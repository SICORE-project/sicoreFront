<?php

namespace Tests\Feature;

use App\Services\Organisation\InterfaceAccess;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IaWorkspaceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->withSession(['access_token' => 'test', 'sicore_user' => [
            'name' => 'Fatou Fall', 'role_slug' => 'gestionnaire_ia', 'role' => 'Gestionnaire IA',
            'ia_id' => 1, 'ia' => ['id' => 1, 'libelle' => 'Dakar'],
            'permissions' => ['enseignants.read', 'paie.bulletins.read', 'paie.bulletins.export', 'parametrage.ia.manage', 'administration.users.read'],
        ]]);
    }

    public function test_teacher_page_uses_scoped_endpoints_without_ia_selector(): void
    {
        Http::fake([
            '*/ia/enseignants*' => Http::response(['data' => [['id' => 3, 'matricule' => 'MAT3', 'prenom' => 'Awa', 'nom' => 'Diop', 'type_engagement' => 'contractuel', 'statut' => 'en_activite']], 'total' => 1, 'current_page' => 1, 'last_page' => 1]),
            '*/ia/referentiels*' => Http::response(['data' => ['iefs' => [['id' => 1, 'libelle' => 'IEF de Dakar']], 'etablissements' => []]]),
        ]);
        $this->get('/parametrage/enseignants')->assertOk()->assertSee('Awa Diop')->assertSee('Périmètre : IA de Dakar')
            ->assertDontSee('name="ia_id"', false)->assertDontSee('Paramétrage')->assertDontSee('Gestion utilisateur')
            ->assertSee(route('enseignants.import.store'), false)
            ->assertSee('Importer et soumettre à la DAGE');
        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => str_contains($r->url(), '/ia/enseignants') && $r['ia_id'] == 1);
    }

    public function test_frontend_scope_tampering_and_foreign_teacher_are_forbidden(): void
    {
        $this->get('/parametrage/enseignants?ia_id=2')->assertForbidden();
        Http::assertNothingSent();
        Http::fake(['*/ia/enseignants/9*' => Http::response([], 403)]);
        $this->get('/ia/enseignants/9')->assertForbidden();
    }

    public function test_payroll_page_and_export_use_ia_endpoints(): void
    {
        Http::fake([
            '*/ia/paie/export*' => Http::response('MAT1;Local', 200, ['Content-Type' => 'text/csv']),
            '*/ia/payroll/pages/*' => Http::response(['data' => ['notice' => 'Périmètre : IA de Dakar', 'stats' => [], 'actions' => [], 'columns' => [], 'rows' => [], 'filters' => [['name' => 'period_id', 'label' => 'Période', 'options' => [['value' => 4, 'label' => '2026-09']]]]]]),
        ]);
        $this->get(route('paie.bulletins'))->assertOk()->assertSee('2026-09')->assertSee('Périmètre : IA de Dakar')->assertDontSee('name="ia_id"', false);
        $this->get('/ia/paie/export?period_id=4')->assertOk()->assertSee('MAT1;Local');
        Http::assertSentCount(2);
    }

    public function test_ia_never_gets_settings_or_user_management_even_with_legacy_permissions(): void
    {
        request()->setLaravelSession(app('session.store'));
        $access = app(InterfaceAccess::class);
        $this->assertFalse($access->allowsRoute('parametres.ia.index'));
        $this->assertFalse($access->allowsRoute('utilisateurs.index'));
        $this->assertFalse($access->allowsRoute('indemnites.convocations'));
        $this->assertTrue($access->allowsRoute('enseignants.index'));
        $this->assertTrue($access->allowsRoute('paie.bulletins'));
        $this->assertFalse($access->allowsRoute('paie.sommes-percues'));
        session()->push('sicore_user.permissions', 'paie.sommes_percues.read');
        $this->assertTrue($access->allowsRoute('paie.sommes-percues'));
    }

    public function test_login_redirects_ia_to_dashboard_even_with_payroll_return_url(): void
    {
        $this->flushSession();
        Http::fake(['*/login' => Http::response(['message' => 'Bienvenue', 'access_token' => 'new-token', 'user' => [
            'id' => 1, 'nom' => 'Fall', 'prenom' => 'Fatou', 'email' => 'ia@example.test',
            'role' => ['slug' => 'gestionnaire_ia', 'nom' => 'Gestionnaire IA', 'permissions' => ['enseignants.read']],
            'ia' => ['id' => 1, 'libelle' => 'Dakar'], 'ia_id' => 1,
        ]])]);
        $this->post('/login', ['email' => 'ia@example.test', 'password' => 'secret123', 'next' => '/paie/bulletins'])
            ->assertRedirect(route('dashboard'))->assertSessionHas('sicore_user.ia_id', 1);
    }
    public function test_teacher_detail_uses_shared_profile(): void
    {
        Http::fake(['*/ia/enseignants/3*' => Http::response(['data' => [
            'id' => 3, 'prenom' => 'Awa', 'nom' => 'Diop',
            'cni' => '1234567890123', 'ief' => ['libelle' => 'IEF Dakar'],
        ]])]);
        $this->get('/ia/enseignants/3')->assertOk()
            ->assertViewIs('pages.ia.teacher-show')
            ->assertSee('teacher-profile-hero', false)
            ->assertSee('data-teacher-detail="cni"', false)
            ->assertSee('teacher-profile.js', false);
    }
    public function test_ia_can_open_creation_form_and_submit_for_validation(): void
    {
        $options = array_fill_keys(['statutOptions', 'corpsOptions', 'categorieOptions', 'diplomeOptions',
            'disciplineOptions', 'institutionOptions', 'syndicatOptions', 'regionOptions', 'iefOptions'], []);
        $options['academies'] = [['id' => 1, 'libelle' => 'Dakar']];
        Http::fake([
            '*/ia/teacher-options*' => Http::response(['data' => $options]),
            '*/ia/teacher-submissions' => Http::response(['message' => 'Dossier soumis.'], 201),
        ]);
        $this->get(route('ia.teachers.create'))->assertOk()
            ->assertSee(route('ia.teachers.submit'), false)->assertSee('teacher-create-modal', false);
        $this->post(route('ia.teachers.submit'), ['ia_id' => 1, 'nom' => 'Diop'])
            ->assertRedirect(route('ia.teachers.submissions'));
        Http::assertSent(fn ($request) => $request->method() === 'POST' && str_ends_with($request->url(), '/ia/teacher-submissions'));
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/admin/personnel/enseignants'));
    }

    public function test_ia_submission_page_does_not_offer_approval(): void
    {
        Http::fake(['*/ia/teacher-submissions*' => Http::response(['data' => ['data' => [
            ['id' => 1, 'matricule' => '123456789/A', 'status' => 'pending', 'payload' => ['nom' => 'Diop', 'prenom' => 'Awa']],
        ]]])]);
        $this->get(route('ia.teachers.submissions'))->assertOk()
            ->assertSee('En attente de validation')->assertDontSee('>Valider</button>', false);
    }
}
