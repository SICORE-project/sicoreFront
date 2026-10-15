<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InterfaceAssignmentTest extends TestCase
{
    public function test_new_account_inherits_role_permissions_and_interface_at_login(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*/login' => Http::response([
            'access_token' => 'test', 'message' => 'Bienvenue',
            'user' => ['id' => 3, 'nom' => 'Diop', 'prenom' => 'Amina', 'email' => 'new@example.test',
                'role' => ['slug' => 'nouveau_role', 'nom' => 'Nouveau rôle',
                    'permissions' => [['slug' => 'enseignants.read']]]],
        ])]);
        $this->post('/login', ['email' => 'new@example.test', 'password' => 'password123'])
            ->assertRedirect(route('dashboard'));
        $this->get('/dashboard')->assertOk()->assertSee('Nouveau rôle')
            ->assertSee('Gestion du personnel')->assertDontSee('Gestion de la paie')->assertDontSee('Gestion utilisateur');
        Http::assertSentCount(1);
        $this->post('/parametrage/enseignants')->assertForbidden();
    }

    public function test_missing_scope_opens_empty_teacher_page_without_loading_data(): void
    {
        Http::preventStrayRequests();
        $this->withSession(['access_token' => 'test', 'sicore_user' => [
            'role_slug' => 'agent_drh', 'permissions' => ['enseignants.read', 'enseignants.create'],
        ]])->get('/parametrage/enseignants')->assertOk()
            ->assertSee('Aucun enseignant trouvé.')->assertSee('Aucun périmètre organisationnel');
        $this->post('/parametrage/enseignants')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_role_permission_form_contains_interface_preview(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*/admin/permissions/all' => Http::response(['data' => [
                ['id' => 7, 'nom' => 'Consulter les enseignants', 'slug' => 'enseignants.read', 'module' => 'enseignants'],
            ]]),
            '*/admin/type-roles/all' => Http::response(['data' => []]),
        ]);
        $this->withSession(['access_token' => 'test', 'sicore_user' => ['role_slug' => 'admin']])
            ->get('/roles/create')->assertOk()->assertSee('Interface associée à ce rôle')
            ->assertSee('data-interface-preview', false)->assertSee('enseignants.read');
    }

    public function test_empty_permission_list_does_not_grant_modules(): void
    {
        Http::preventStrayRequests();
        $this->withSession(['sicore_user' => ['role_slug' => 'nouveau_role', 'permissions' => []]])
            ->get('/dashboard')->assertOk()->assertSee('Aucun module')
            ->assertDontSee('Gestion du personnel')->assertDontSee('Gestion utilisateur');
        $this->getJson('/parametrage/enseignants')->assertForbidden();
        Http::assertNothingSent();
    }
}
