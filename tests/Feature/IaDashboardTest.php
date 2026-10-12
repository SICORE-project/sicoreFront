<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IaDashboardTest extends TestCase
{
    private function manager(array $permissions = ['enseignants.read', 'paie.bulletins.read']): static
    {
        Http::preventStrayRequests();

        return $this->withSession(['access_token' => 'test-token', 'sicore_user' => [
            'name' => 'Fatou Fall',
            'role_slug' => 'gestionnaire_ia',
            'role' => 'Gestionnaire IA',
            'permissions' => $permissions,
        ]]);
    }

    public function test_manager_sees_real_metrics_and_api_scope(): void
    {
        $this->manager();
        Http::fake(['*/ia/dashboard' => Http::response(['data' => [
            'ia' => ['id' => 4, 'libelle' => 'IA de Dakar'],
            'indicateurs' => ['agents' => 1250, 'fonctionnaires' => 1000, 'non_fonctionnaires' => 250, 'bulletins_generes' => 1100, 'bulletins_restants' => 150],
            'periode' => ['code' => '2026-09'],
            'iefs' => [['libelle' => 'IEF de Rufisque', 'total' => 300]],
            'lieu_de_services' => [['libelle' => 'Lycée de Rufisque', 'total' => 40]],
            'dernieres_operations' => [['prenom' => 'Awa', 'nom' => 'Diop', 'payment_status' => 'paid', 'updated_at' => '2026-09-21 09:30:00']],
        ]])]);

        $this->get('/dashboard')->assertOk()->assertViewIs('pages.dashboard.ia')
            ->assertSee('Fatou Fall')->assertSee('IA de Dakar')->assertSee('1 250')
            ->assertDontSee('Bulletins g'.chr(233).'n'.chr(233).'r'.chr(233).'s')->assertDontSee('Bulletins restants')
            ->assertSee('IEF de Rufisque')->assertSee('Lycée de Rufisque')
            ->assertSee('Awa Diop')->assertSee('Payé')->assertSee('21/09/2026 à 09:30')
            ->assertDontSee('Périmètre global');
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/ia/dashboard')
            && $request->hasHeader('Authorization', 'Bearer test-token'));
        Http::assertSentCount(1);
    }

    public function test_salary_mass_is_hidden_without_specific_permission(): void
    {
        $this->manager();
        Http::fake(['*/ia/dashboard' => Http::response(['data' => [
            'indicateurs' => ['masse_salariale' => 987654321],
        ]])]);
        $this->get('/dashboard')->assertOk()->assertDontSee('Masse salariale brute')->assertDontSee('987 654 321');
    }

    public function test_authorized_salary_mass_is_displayed(): void
    {
        $this->manager(['paie.bulletins.read', 'paie.masse_salariale.read']);
        Http::fake(['*/ia/dashboard' => Http::response(['data' => [
            'indicateurs' => ['masse_salariale' => 987654321],
        ]])]);
        $this->get('/dashboard')->assertOk()->assertSee('Masse salariale brute')->assertSee('987 654 321');
    }

    public function test_personnel_only_manager_does_not_see_payroll(): void
    {
        $this->manager(['enseignants.read']);
        Http::fake(['*/ia/dashboard' => Http::response(['data' => ['indicateurs' => ['agents' => 0]]])]);
        $this->get('/dashboard')->assertOk()->assertSee('Enseignants par IEF')
            ->assertDontSee('Suivi de la paie')->assertDontSee('Dernières opérations de paie');
    }

    public function test_forbidden_scope_shows_error_without_global_fallback(): void
    {
        $this->manager();
        Http::fake(['*/ia/dashboard' => Http::response([], 403)]);
        $this->get('/dashboard')->assertOk()->assertSee('Votre accès ou votre rattachement')
            ->assertSee('Indicateur indisponible')->assertSee('IA non disponible')
            ->assertDontSee('Périmètre global');
    }

    public function test_connection_failure_keeps_dashboard_available(): void
    {
        $this->manager();
        Http::fake(['*/ia/dashboard' => Http::failedConnection()]);
        $this->get('/dashboard')->assertOk()->assertSee('Le service est momentanément inaccessible.')
            ->assertSee('Réessayer')->assertSee('Indicateur indisponible');
    }

    public function test_missing_period_has_no_invented_payroll_values(): void
    {
        $this->manager();
        Http::fake(['*/ia/dashboard' => Http::response(['data' => ['periode' => null, 'indicateurs' => []]])]);
        $this->get('/dashboard')->assertOk()->assertDontSee('Suivi de la paie')
            ->assertSee('Aucune opération disponible.')->assertSee('—');
    }
}
