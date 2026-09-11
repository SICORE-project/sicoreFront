<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EtablissementPaginationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['name' => 'Gestionnaire']]);
    }

    private function fakePage(int $page, int $perPage, int $total): void
    {
        $count = max(0, min($perPage, $total - ($page - 1) * $perPage));
        $items = array_map(fn ($index) => ['id' => $index + 1, 'libelle' => 'École '.($index + 1)], range(0, max(0, $count - 1)));
        Http::fake([
            '*/parametrage/lieux-service*' => Http::response([
                'data' => $count ? $items : [],
                'meta' => ['current_page' => $page, 'last_page' => max(1, (int) ceil($total / $perPage)), 'per_page' => $perPage, 'total' => $total],
            ]),
            '*' => Http::response(['data' => []]),
        ]);
    }

    public function test_page_size_and_filters_are_sent_to_api_and_preserved_in_navigation(): void
    {
        $this->fakePage(2, 20, 75);
        $filters = ['ia_id' => 3, 'ief_id' => 7, 'per_page' => 20];
        $response = $this->get(route('parametres.lieux-service.index', $filters + ['page' => 2]))
            ->assertOk()->assertSee('21–40 sur 75')
            ->assertSee('class="pagination-controls"', false)
            ->assertSee('aria-label="Page 2 sur 4"', false)
            ->assertSee('value="20" selected', false);

        foreach ([1, 3, 4] as $targetPage) {
            $response->assertSee('href="'.e(route('parametres.lieux-service.index', $filters + ['page' => $targetPage])).'"', false);
        }
        $response->assertSee('name="page" value="1"', false)
            ->assertSee('name="ia_id" value="3"', false)
            ->assertSee('name="ief_id" value="7"', false)
            ->assertDontSee('data-table-pagination', false);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/parametrage/lieux-service')
            && $request['page'] === 2 && $request['per_page'] === 20
            && (string) $request['ia_id'] === '3' && (string) $request['ief_id'] === '7');
    }

    public static function boundaryPages(): array
    {
        return [[1, ['Première page', 'Page précédente']], [3, ['Page suivante', 'Dernière page']]];
    }

    #[DataProvider('boundaryPages')]
    public function test_navigation_is_disabled_at_first_and_last_page(int $page, array $labels): void
    {
        $this->fakePage($page, 10, 23);
        $response = $this->get(route('parametres.lieux-service.index', ['page' => $page]))->assertOk();
        foreach ($labels as $label) {
            $response->assertSee('disabled aria-label="'.$label.'"', false);
        }
        $response->assertSee($page === 1 ? '1–10 sur 23' : '21–23 sur 23');
    }

    public function test_empty_results_do_not_display_pagination(): void
    {
        $this->fakePage(1, 10, 0);
        $this->get(route('parametres.lieux-service.index'))->assertOk()
            ->assertSee('Aucun établissement trouvé.')
            ->assertDontSee('aria-label="Pagination des établissements"', false);
    }

    public function test_out_of_range_page_redirects_to_last_page_with_filters_and_page_size(): void
    {
        $this->fakePage(9, 50, 60);
        $filters = ['ia_id' => 3, 'ief_id' => 7];
        $this->get(route('parametres.lieux-service.index', $filters + ['page' => 9, 'per_page' => 50]))
            ->assertRedirect(route('parametres.lieux-service.index', $filters + ['page' => 2, 'per_page' => 50]));
    }

    public function test_invalid_pagination_is_rejected_before_api_call(): void
    {
        Http::fake();
        $this->getJson(route('parametres.lieux-service.index', ['page' => 0, 'per_page' => 500]))
            ->assertUnprocessable()->assertJsonValidationErrors(['page', 'per_page']);
        Http::assertNothingSent();
    }
}
