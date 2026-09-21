<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DrhInterfaceTest extends TestCase
{
    private function drh(array $permissions = ['personnel.consulter', 'recruitment.read'], array $scope = ['ief_id' => 4, 'ief' => ['id' => 4, 'libelle' => 'IEF Dakar']]): static
    {
        Http::preventStrayRequests();

        return $this->withSession(['access_token' => 'test-token', 'sicore_user' => [
            'prenom' => 'Amina', 'nom' => 'Diop', 'role_slug' => 'agent_drh',
            'permissions' => $permissions, 'acces_organisationnel' => $scope,
        ]]);
    }

    public function test_dashboard_displays_scoped_metrics_and_filters_navigation(): void
    {
        Http::fake(['*/pages.dashboard.index*' => Http::response(['data' => ['agents_total' => 42]])]);
        $this->drh()->get('/dashboard')->assertOk()
            ->assertSee('Tableau de bord DRH')->assertSee('IEF Dakar')->assertSee('42')
            ->assertSee('Gestion du personnel')->assertSee('Indicateur indisponible')
            ->assertDontSee('Gestion de la paie')->assertDontSee('Gestion des indemnités')
            ->assertDontSee('Gestion des utilisateurs')->assertDontSee('Paramétrage');
        Http::assertSent(fn ($request) => $request['ief_id'] === 4 && $request->hasHeader('Authorization', 'Bearer test-token'));
        Http::assertSentCount(1);
    }

    public function test_drh_alias_only_sees_relevant_modules_and_old_links_return_to_dashboard(): void
    {
        Http::fake(['*/pages.dashboard.index*' => Http::response(['data' => []])]);
        $this->drh()->withSession(['sicore_user.role_slug' => 'drh'])
            ->get('/dashboard')->assertOk()
            ->assertSee('Gestion du personnel')->assertDontSee('Gestion de la paie')
            ->assertDontSee('Gestion des indemnités')->assertDontSee('Paramétrage')
            ->assertDontSee('Gestion des utilisateurs');
        $this->get('/paie/bulletins')->assertRedirect(route('dashboard'));
        $this->getJson('/paie/bulletins')->assertForbidden();
    }

    public function test_missing_scope_or_permission_does_not_query_backend(): void
    {
        $this->drh(scope: [])->get('/dashboard')->assertOk()->assertSee('Votre périmètre organisationnel doit être défini');
        $this->drh(permissions: [])->get('/dashboard')->assertOk()->assertSee('ne vous est pas autorisée');
        $this->get('/enseignants')->assertRedirect(route('dashboard'));
        Http::assertNothingSent();
    }

    public function test_direct_urls_and_writes_are_forbidden_without_permissions(): void
    {
        $this->drh();
        foreach (['/paie/bulletins', '/parametrage/parametres/corps', '/enseignants/nouveau', '/parametrage/enseignants/1/modifier'] as $url) {
            $this->get($url)->assertRedirect(route('dashboard'));
        }
        $this->post('/parametrage/enseignants', [])->assertForbidden();
        $this->put('/parametrage/enseignants/1', [])->assertForbidden();
        $this->delete('/parametrage/enseignants/1')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_personnel_list_is_scoped_and_hides_unauthorized_actions(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        $response = $this->drh()->get('/parametrage/enseignants?ief_id=999')->assertOk();
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/s', '', $response->getContent());
        $this->assertStringNotContainsString('data-modal-open="teacher-create-modal"', $html);
        $this->assertStringNotContainsString('data-referentiel-open="ia"', $html);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'admin/personnel/enseignants') && (string) $request['ief_id'] === '4');
    }

    public function test_complementary_permission_only_opens_its_route(): void
    {
        $this->drh(['personnel.consulter', 'personnel.reclassement'])
            ->get('/personnel/reclassement')->assertOk();
        $this->get('/personnel/heures-supplementaires')->assertRedirect(route('dashboard'));
    }

    public function test_edit_permission_displays_edit_action_but_does_not_allow_deletion(): void
    {
        Http::fake([
            '*/admin/personnel/enseignants*' => Http::response(['data' => [['id' => 7, 'nom' => 'Diop']]]),
            '*' => Http::response(['data' => []]),
        ]);
        $response = $this->drh(['personnel.consulter', 'personnel.modifier'])
            ->get('/parametrage/enseignants')->assertOk();
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/s', '', $response->getContent());
        $this->assertStringContainsString('data-edit-teacher=', $html);
        $this->assertStringNotContainsString('title="Supprimer"', $html);
        $this->put('/parametrage/enseignants/7', [])->assertSessionHasErrors('nom');
        $this->delete('/parametrage/enseignants/7')->assertForbidden();
    }

    public function test_api_failure_does_not_display_zero_metrics(): void
    {
        Http::fake(['*' => Http::response([], 403)]);
        $this->drh()->get('/dashboard')->assertOk()->assertSee('indisponibles')->assertSee('Indicateur indisponible');
    }

    public function test_login_preserves_permissions_and_redirects_drh_to_dashboard(): void
    {
        Http::fake(['*/login' => Http::response([
            'access_token' => 'token', 'message' => 'Bienvenue',
            'user' => ['id' => 4, 'prenom' => 'Amina', 'nom' => 'Diop', 'email' => 'drh@example.test',
                'role' => ['slug' => 'agent_drh', 'nom' => 'Agent DRH'],
                'permissions' => ['personnel.consulter'], 'acces_organisationnel' => ['ief_id' => 4]],
        ])]);
        $this->post('/login', ['email' => 'drh@example.test', 'password' => 'test-password', 'next' => '/paie/bulletins'])
            ->assertRedirect(route('dashboard'))->assertSessionHas('sicore_user.permissions', ['personnel.consulter']);
    }
}
