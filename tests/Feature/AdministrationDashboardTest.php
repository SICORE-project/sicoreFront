<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdministrationDashboardTest extends TestCase
{
    public function test_admin_dashboard_loads_real_administration_metrics(): void
    {
        $this->checkDashboard(['role_slug' => 'super_admin', 'role' => 'Super Administrateur']);
    }

    public function test_legacy_admin_session_displays_the_same_dashboard(): void
    {
        $this->checkDashboard(['role' => 'Super Administrateur']);
    }

    private function checkDashboard(array $user): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*/admin/users/all' => Http::response(['data' => [
                ['id' => 1, 'statut' => 'actif'],
                ['id' => 2, 'statut' => true],
                ['id' => 3, 'statut' => 'inactif'],
            ]]),
            '*/admin/roles/all' => Http::response(['data' => [['id' => 1]]]),
            '*/admin/permissions/all' => Http::response(['data' => [['id' => 1], ['id' => 2]]]),
        ]);

        $this->withSession(['sicore_user' => $user, 'access_token' => 'test-token'])
            ->get('/dashboard')
            ->assertOk()
            ->assertViewHas('isGlobalAdmin', true)
            ->assertViewHas('metrics', ['utilisateurs' => 3, 'utilisateurs_actifs' => 2, 'roles' => 1, 'permissions' => 2])
            ->assertSee('Tableau de bord global')
            ->assertSee('Comptes actifs')
            ->assertSee('data-chart="main-donut"', false)
            ->assertDontSee('Dossiers en cours');

        Http::assertSentCount(3);
    }
}
