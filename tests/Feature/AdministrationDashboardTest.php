<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdministrationDashboardTest extends TestCase
{
    private function dashboard(array $overrides = []): array
    {
        return array_replace([
            'title' => 'Mon espace enseignant', 'role' => 'Enseignant', 'name' => 'Awa Ndiaye',
            'scope' => 'Votre dossier personnel', 'updated_at' => '2026-09-24T14:00:00Z',
            'cards' => [['label' => 'Mes bulletins validés', 'value' => 2, 'icon' => 'fa-file-invoice', 'unit' => null]],
            'actions' => [], 'notices' => [],
            'sections' => [['title' => 'Mon dossier', 'columns' => ['Matricule'], 'rows' => [['001234/F']]]],
        ], $overrides);
    }

    public function test_super_admin_dashboard_renders_charts_and_empty_states(): void
    {
        Http::fake(['*/dashboard*' => Http::response(['data' => $this->dashboard([
            'layout' => 'super-admin', 'title' => 'Pilotage de la plateforme',
            'analytics' => ['history' => [['label' => '09/2026', 'teachers' => 3, 'users' => 1]],
                'distributions' => [
                    ['title' => 'État des comptes', 'subtitle' => 'Activation', 'items' => [['label' => 'Actifs', 'value' => 1]]],
                    ['title' => 'Paiements', 'subtitle' => 'Aucune période', 'items' => []],
                ]],
        ])])]);
        $this->get('/dashboard')->assertOk()->assertSee('Évolution des inscriptions')
            ->assertSee('État des comptes')->assertSee('Aucune donnée disponible')
            ->assertSee('super-admin-dashboard.js')->assertSee('super-admin-dashboard.css')
            ->assertSee('6 mois')->assertSee('12 mois');
    }

    protected function setUp(): void
    {
        parent::setUp();
        // The API, not this intentionally stale session role, decides the dashboard.
        $this->withSession(['sicore_user' => ['role_slug' => 'super_admin', 'role' => 'Super Administrateur'], 'access_token' => 'token']);
        Http::preventStrayRequests();
    }

    public function test_dashboard_uses_api_profile_and_never_fetches_global_collections(): void
    {
        Http::fake(['*/dashboard*' => Http::response(['data' => $this->dashboard()])]);
        $this->get('/dashboard')->assertOk()->assertSee('Mon espace enseignant')->assertSee('001234/F')
            ->assertDontSee('Gestion utilisateur')->assertDontSee('Comptes utilisateurs')->assertDontSee('Gestion de la paie');
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer token'));
    }

    public function test_admin_shortcuts_and_metrics_come_from_authorized_api_response(): void
    {
        Http::fake(['*/dashboard*' => Http::response(['data' => $this->dashboard([
            'title' => 'Administration des accès', 'role' => 'Administrateur', 'scope' => 'Administration centrale',
            'cards' => [['label' => 'Comptes actifs', 'value' => 12, 'icon' => 'fa-user-check', 'unit' => null]],
            'actions' => [['label' => 'Gérer les utilisateurs', 'route' => 'utilisateurs.index', 'icon' => 'fa-users']],
            'sections' => [],
        ])])]);
        $this->get('/dashboard')->assertOk()->assertSee('Administration des accès')->assertSee('Comptes actifs')->assertSee('Gérer les utilisateurs')
            ->assertDontSee('Gestion du personnel')->assertDontSee('Gestion des indemnités');
    }

    public function test_empty_scope_displays_message_instead_of_fabricated_metrics(): void
    {
        Http::fake(['*/dashboard*' => Http::response(['data' => $this->dashboard([
            'cards' => [], 'sections' => [], 'notices' => ['Votre rattachement territorial doit être complété.'],
        ])])]);
        $this->get('/dashboard')->assertOk()->assertSee('Votre rattachement territorial doit être complété.')
            ->assertDontSee('dashboard-metric-icon');
    }

    public function test_backend_failure_does_not_appear_as_zero_metrics(): void
    {
        Http::fake(['*/dashboard*' => Http::response([], 500)]);
        $this->get('/dashboard')->assertOk()->assertSee('Tableau de bord indisponible')->assertDontSee('Comptes actifs')
            ->assertDontSee('Gestion utilisateur')->assertDontSee('dashboard-metric-icon');
    }

    public function test_connection_failure_displays_unavailable_state(): void
    {
        Http::fake(['*' => Http::failedConnection()]);
        $this->get('/dashboard')->assertOk()->assertSee('Aucun indicateur');
    }

    public function test_forbidden_profile_shows_access_explanation(): void
    {
        Http::fake(['*/dashboard*' => Http::response([], 403)]);
        $this->get('/administration/dashboard')->assertOk()->assertSee('Contactez votre administrateur.');
    }

    public function test_expired_session_redirects_to_login(): void
    {
        Http::fake(['*/dashboard*' => Http::response([], 401)]);
        $this->get('/dashboard')->assertRedirect(route('login'))->assertSessionMissing('access_token')->assertSessionMissing('sicore_user');
    }

    public function test_teacher_layout_renders_compact_cards_and_personal_charts(): void
    {
        Http::fake(['*/dashboard*' => Http::response(['data' => $this->dashboard([
            'layout' => 'teacher',
            'charts' => [
                'net_history' => [['period' => '2026-09', 'amount' => 150000]],
                'payments' => [
                    ['label' => 'Payés', 'value' => 1, 'color' => '#14866d'],
                    ['label' => 'En attente', 'value' => 0, 'color' => '#e8aa42'],
                    ['label' => 'Rejetés', 'value' => 0, 'color' => '#dd6475'],
                ],
            ],
        ])])]);
        $this->get('/dashboard')->assertOk()->assertSee('teacher-kpi')->assertSee('teacher-dashboard.js')
            ->assertSee('Évolution du montant net')->assertSee('Situation des paiements')
            ->assertSee('6 périodes')->assertSee('12 périodes')->assertSee('150 000')
            ->assertDontSee('Points de suivi');
    }

    public function test_empty_teacher_charts_show_no_fabricated_statistics(): void
    {
        Http::fake(['*/dashboard*' => Http::response(['data' => $this->dashboard([
            'layout' => 'teacher', 'cards' => [], 'charts' => ['net_history' => [], 'payments' => []],
        ])])]);
        $this->get('/dashboard')->assertOk()->assertSee('Votre historique apparaîtra ici')
            ->assertSee('Aucun paiement à afficher')->assertDontSee('teacher-donut');
    }

    public function test_backend_text_is_escaped(): void
    {
        Http::fake(['*/dashboard*' => Http::response(['data' => $this->dashboard(['name' => '<script>alert(1)</script>'])])]);
        $this->get('/dashboard')->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }
}
