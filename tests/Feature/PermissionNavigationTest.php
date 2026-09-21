<?php

namespace Tests\Feature;

use App\Services\Organisation\DrhAccess;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PermissionNavigationTest extends TestCase
{
    public function test_director_can_open_empty_teacher_list_with_backend_permission(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['data' => []])]);
        $this->withSession(['access_token' => 'test', 'sicore_user' => [
            'role_slug' => 'drh', 'permissions' => [['slug' => 'enseignants.read']],
            'drh' => ['perimetre' => ['type' => 'national', 'id' => null]],
        ]])->get('/parametrage/enseignants')->assertOk()->assertSee('Aucun enseignant trouvé.');
    }

    public function test_new_role_uses_permissions_without_role_specific_code(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        $this->withSession(['access_token' => 'test', 'sicore_user' => [
            'role_slug' => 'nouveau_profil', 'permissions' => ['enseignants.read'],
        ]])->get('/parametrage/enseignants')->assertOk()
            ->assertSee('Gestion du personnel')->assertDontSee('Gestion de la paie')
            ->assertDontSee('Gestion utilisateur');
        $this->assertFalse(app(DrhAccess::class)->allowsRoute('enseignants.store'));
        session(['sicore_user.permissions' => ['administration.users.read']]);
        $access = app(DrhAccess::class);
        $this->assertFalse($access->allowsRoute('enseignants.index'));
        $this->assertTrue($access->allowsRoute('utilisateurs.index'));
        $this->assertFalse($access->allowsRoute('utilisateurs.store'));
    }

    public function test_no_scope_still_displays_empty_list_and_backend_enforces_scope(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        $this->withSession(['access_token' => 'test', 'sicore_user' => [
            'role_slug' => 'agent_drh', 'permissions' => ['enseignants.read'],
        ]])->get('/parametrage/enseignants')->assertOk()->assertSee('Aucun enseignant trouvé.');
    }
}
