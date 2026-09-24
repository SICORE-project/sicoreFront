<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DecpcDashboardTest extends TestCase
{
    private function loginAsDecpc(array $overrides = []): static
    {
        return $this->withSession(['access_token' => 'test-token', 'sicore_user' => array_replace([
            'role_slug' => 'agent_decpc',
            'permissions' => ['enseignants.read', 'indemnites.read'],
            'decpc' => ['perimetre' => ['type' => 'lieu_service_id', 'id' => 7]],
        ], $overrides)]);
    }

    public function test_dashboard_uses_scoped_api_and_filters_navigation(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*/decpc/dashboard*' => Http::response(['data' => [
            'total_agents' => 42, 'dossiers_en_attente' => 3, 'montant_en_cours' => 125000,
        ]])]);
        $this->loginAsDecpc()->get('/dashboard?lieu_service_id=99')->assertOk()
            ->assertViewIs('pages.dashboard.decpc')->assertSee('Gestion du personnel')
            ->assertSee('Gestion des indemnités')->assertSee('125 000 FCFA')
            ->assertDontSee('Gestion de la paie')->assertDontSee('Gestion utilisateur')->assertDontSee('Paramétrage');
        Http::assertSent(fn ($request) => $request['lieu_service_id'] === 7 && $request->hasHeader('Authorization', 'Bearer test-token'));
    }

    public function test_missing_scope_blocks_data_access(): void
    {
        Http::preventStrayRequests();
        $this->loginAsDecpc(['decpc' => []])->get('/dashboard')->assertOk()->assertSee('Votre périmètre DECPC doit être défini');
        $this->getJson('/parametrage/enseignants')->assertForbidden();
        $this->getJson('/indemnites/convocations')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_read_permission_does_not_allow_mutations_or_other_modules(): void
    {
        Http::preventStrayRequests();
        $this->loginAsDecpc()->postJson('/indemnites/pieces-justificatives/1/valider')->assertForbidden();
        $this->postJson('/indemnites/etats-paie', [])->assertForbidden();
        $this->getJson('/utilisateurs')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_legacy_session_without_permissions_fails_closed(): void
    {
        Http::preventStrayRequests();
        $this->withSession(['access_token' => 'test', 'sicore_user' => ['role' => 'Agent DECPC']])
            ->get('/dashboard')->assertOk()->assertDontSee('Gestion de la paie')->assertDontSee('Gestion utilisateur');
        Http::assertNothingSent();
    }

    public function test_api_failure_displays_unavailable_metrics(): void
    {
        Http::fake(['*' => Http::response([], 403)]);
        $this->loginAsDecpc()->get('/dashboard')->assertOk()->assertSee('Les indicateurs sont indisponibles');
    }

    public function test_personnel_permission_does_not_reveal_indemnity_metrics(): void
    {
        Http::fake(['*' => Http::response(['data' => ['montant_en_cours' => 987654]])]);
        $this->loginAsDecpc(['permissions' => ['enseignants.read']])->get('/dashboard')->assertOk()
            ->assertDontSee('987 654')->assertDontSee('Derniers dossiers reçus ou traités');
    }

    public function test_complementary_permissions_are_required_for_administration(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        $this->loginAsDecpc(['permissions' => ['enseignants.read', 'administration.users.read']])
            ->get('/dashboard')->assertOk()->assertSee('Gestion utilisateur')->assertDontSee('Gestion de la paie');
        $access = app(\App\Services\Organisation\InterfaceAccess::class);
        $this->assertTrue($access->allowsRoute('utilisateurs.index'));
        $this->assertFalse($access->allowsRoute('utilisateurs.store'));
    }

    public function test_read_access_includes_details_but_not_validation(): void
    {
        $this->loginAsDecpc();
        request()->setLaravelSession(session()->driver());
        $access = app(\App\Services\Organisation\InterfaceAccess::class);
        $this->assertTrue($access->allowsRoute('indemnites.convocations.show'));
        $this->assertTrue($access->allowsRoute('indemnites.pieces-justificatives.telecharger'));
        $this->assertFalse($access->allowsRoute('indemnites.pieces-justificatives.valider'));
    }

    public function test_national_scope_loads_backend_indicators(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*/decpc/dashboard*' => Http::response(['data' => [
            'indicateurs' => ['total_agents' => 42, 'indemnites_en_attente' => 3,
                'montant_indemnites_en_cours' => 125000],
        ]])]);
        $this->loginAsDecpc(['decpc' => ['perimetre' => ['type' => 'national', 'id' => null]]])
            ->get('/dashboard')->assertOk()->assertSee('125 000 FCFA')->assertSee('Gestion du personnel');
        Http::assertSent(fn ($request) => str_contains($request->url(), 'decpc/dashboard')
            && ! isset($request['lieu_service_id']));
    }
}
