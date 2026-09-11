<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TeacherEtablissementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['name' => 'Gestionnaire']]);
    }

    public function test_options_are_filtered_by_ief_across_all_pages(): void
    {
        Http::fakeSequence()->push([
            'data' => [['id' => 5, 'libelle' => 'École A', 'ief_id' => 2]],
            'meta' => ['last_page' => 2],
        ])->push([
            'data' => [['id' => 6, 'libelle' => 'École B', 'ief_id' => 2]],
            'meta' => ['last_page' => 2],
        ]);

        $this->getJson(route('enseignants.etablissements', ['ief_id' => 2]))
            ->assertOk()->assertJsonCount(2, 'items')
            ->assertJsonPath('items.0.id', 5)->assertJsonPath('items.1.id', 6);

        Http::assertSentCount(2);
        foreach ([1, 2] as $page) {
            Http::assertSent(fn ($request) => str_contains($request->url(), '/parametrage/lieux-service?')
                && $request['ief_id'] == 2 && $request['page'] == $page && $request['per_page'] == 100);
        }
    }

    public function test_ief_is_required_before_requesting_options(): void
    {
        Http::fake();
        $this->getJson(route('enseignants.etablissements'))->assertUnprocessable()->assertJsonValidationErrors('ief_id');
        $this->getJson(route('enseignants.etablissements', ['ief_id' => 0]))->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_backend_failure_does_not_return_a_partial_list(): void
    {
        Http::fakeSequence()->push(['data' => [['id' => 5]], 'meta' => ['last_page' => 2]])
            ->push(['message' => 'Indisponible'], 500);
        $this->getJson(route('enseignants.etablissements', ['ief_id' => 2]))
            ->assertStatus(502)->assertJsonPath('items', [])->assertJsonPath('error', 'Indisponible');
    }

    public function test_an_ief_without_establishments_returns_an_empty_list(): void
    {
        Http::fake(['*' => Http::response(['data' => [], 'meta' => ['last_page' => 1]])]);
        $this->getJson(route('enseignants.etablissements', ['ief_id' => 2]))
            ->assertOk()->assertJsonPath('items', [])->assertJsonPath('error', null);
    }
}
