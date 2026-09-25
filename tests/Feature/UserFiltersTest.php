<?php
namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UserFiltersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['role' => 'Administrateur']]);
        Http::preventStrayRequests();
    }

    public function test_list_sends_filters_and_preserves_them_in_pagination(): void
    {
        Http::fake([
            '*/admin/users?*' => Http::response(['data' => [], 'meta' => ['current_page' => 1, 'last_page' => 2, 'total' => 12, 'per_page' => 10]]),
            '*/admin/users/filter-options*' => Http::response(['data' => ['ias' => [['id' => 1, 'libelle' => 'Dakar']], 'iefs' => [['id' => 2, 'libelle' => 'Plateau']], 'etablissements' => [['id' => 3, 'libelle' => 'Lycée']]]]),
            '*' => Http::response(['data' => []]),
        ]);
        $filters = ['nom' => 'Awa Ndiaye', 'matricule' => '1234', 'ia_id' => 1, 'ief_id' => 2, 'etablissement_id' => 3];
        $this->get(route('utilisateurs.index', $filters))->assertOk()->assertSee('Nom ou prénom')->assertSee('Lycée')->assertSee('12 utilisateur(s)')
            ->assertSee(route('utilisateurs.index', $filters + ['page' => 2]))->assertDontSee('data-table-pagination')->assertDontSee('id="moduleSearch"', false);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/admin/users?') && $request['nom'] === 'Awa Ndiaye' && $request['etablissement_id'] == 3 && $request['page'] == 1);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/admin/users/all'));
    }

    public function test_dependent_options_are_loaded_through_authenticated_proxy(): void
    {
        Http::fake(['*/admin/users/filter-options*' => Http::response(['data' => ['iefs' => [['id' => 2, 'libelle' => 'Plateau']]]])]);
        $this->getJson(route('utilisateurs.filter-options', ['ia_id' => 1]))->assertOk()->assertJsonPath('data.iefs.0.id', 2);
        Http::assertSent(fn ($request) => $request['ia_id'] == 1 && $request->hasHeader('Authorization', 'Bearer token'));
    }

    public function test_option_failure_is_not_reported_as_empty_success(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Accès interdit'], 403)]);
        $this->getJson(route('utilisateurs.filter-options', ['ia_id' => 1]))->assertForbidden()->assertJsonPath('message', 'Accès interdit');
    }
}
