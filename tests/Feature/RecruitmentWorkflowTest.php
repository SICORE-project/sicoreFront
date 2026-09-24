<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecruitmentWorkflowTest extends TestCase
{
    private function account(array $permissions, string $role = 'agent_drh'): static
    {
        Http::preventStrayRequests();
        return $this->withSession(['access_token' => 'test-token', 'sicore_user' => [
            'id' => 9, 'role_slug' => $role, 'permissions' => $permissions,
            'acces_organisationnel' => ['ia_id' => 4],
        ]]);
    }

    private function batch(array $member = []): array
    {
        return ['batch' => ['id' => 12, 'reference' => 'LOT-2024', 'recruited_at' => '2024-01-01',
            'transmitted_at' => '2024-01-02', 'has_os' => true],
            'members' => [array_replace(['id' => 5, 'enseignant_id' => 20, 'prenom' => 'Amina', 'nom' => 'Diop',
                'matricule' => 'TMP000001', 'engagement' => 'vacataire', 'est_actif' => false,
                'service_date' => null, 'ia_id' => 4, 'ief_id' => 8, 'lieu_service_id' => 6], $member)],
            'history' => [['id' => 30, 'member_id' => 5, 'action' => 'recrutement', 'previous_status' => null,
                'new_status' => 'vacataire', 'effective_date' => '2024-01-01', 'created_at' => '2024-01-01', 'user_id' => 9, 'has_document' => false]],
        ];
    }

    public function test_drh_navigation_points_to_batches_and_empty_list_is_visible(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        $this->account(['recruitment.read', 'recruitment.import'])
            ->get('/personnel/recrutements')->assertOk()->assertSee('Nouveaux enseignants vacataires')
            ->assertSee('Importer une liste')->assertSee('Aucun lot de recrutement')
            ->assertDontSee('Dashboard Enseignant');
    }

    public function test_unauthorized_import_never_calls_backend(): void
    {
        $this->account(['recruitment.read'])->post('/personnel/recrutements/apercu')->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_preview_creates_no_records_and_confirmation_uses_verified_file(): void
    {
        Storage::fake('local');
        Http::fake(['*/recruitment/batches' => function ($request) {
            return data_get(collect($request->data())->firstWhere('name', 'preview'), 'contents') === '1'
                ? Http::response(['data' => ['total' => 1, 'rows' => [['matricule' => null, 'prenom' => 'Amina', 'nom' => 'Diop', 'type_engagement' => 'vacataire']]]])
                : Http::response(['data' => ['id' => 12, 'total' => 1]], 201);
        }]);
        $this->account(['recruitment.read', 'recruitment.import'])->post('/personnel/recrutements/apercu', [
            'reference' => 'LOT-2024', 'recruited_at' => '2024-01-01',
            'file' => UploadedFile::fake()->createWithContent('liste.csv', "prenom;nom\nAmina;Diop\n"),
        ])->assertOk()->assertSee('Confirmer l’import de 1 recruté(s)');
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => data_get(collect($request->data())->firstWhere('name', 'preview'), 'contents') === '1');
        $pending = session('recruitment_import');
        Storage::disk('local')->assertExists($pending['path']);
        $this->post('/personnel/recrutements', ['token' => $pending['token'], 'reference' => 'TAMPERED'])
            ->assertRedirect(route('recruitment.show', 12));
        Http::assertSent(fn ($request) => data_get(collect($request->data())->firstWhere('name', 'preview'), 'contents') === '0' && data_get(collect($request->data())->firstWhere('name', 'reference'), 'contents') === 'LOT-2024');
        Storage::disk('local')->assertMissing($pending['path']);
        $this->post('/personnel/recrutements', ['token' => $pending['token']])->assertRedirect(route('recruitment.create'));
        Http::assertSentCount(2);
    }

    public function test_csv_errors_are_shown_without_creating_pending_import(): void
    {
        Http::fake(['*' => Http::response(['errors' => ['ligne_3' => ['Matricule répété.']]], 422)]);
        $this->account(['recruitment.import'])->from(route('recruitment.create'))->post('/personnel/recrutements/apercu', [
            'reference' => 'LOT', 'recruited_at' => '2024-01-01',
            'file' => UploadedFile::fake()->createWithContent('liste.csv', "a;b\nx;y\n"),
        ])->assertRedirect(route('recruitment.create'))->assertSessionHasErrors('ligne_3');
        $this->assertNull(session('recruitment_import'));
    }

    public function test_unverified_and_expired_imports_are_rejected(): void
    {
        $this->account(['recruitment.import'])->post('/personnel/recrutements', [
            'token' => '1aaa0000-0000-4000-8000-000000000001',
        ])->assertRedirect(route('recruitment.create'));
        $this->withSession(['recruitment_import' => ['token' => '1aaa0000-0000-4000-8000-000000000001',
            'expires_at' => now()->subMinute()->timestamp, 'user_id' => 9, 'path' => 'unused']])
            ->post('/personnel/recrutements', ['token' => '1aaa0000-0000-4000-8000-000000000001'])
            ->assertRedirect(route('recruitment.create'));
        Http::assertNothingSent();
    }

    public function test_two_year_alert_uses_service_date_without_changing_status(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 21));
        Http::fake(['*' => Http::response(['data' => $this->batch(['service_date' => '2024-09-21', 'est_actif' => true])])]);
        $this->account(['recruitment.read', 'recruitment.transition'])->get('/personnel/recrutements/12')
            ->assertOk()->assertSee('2026-09-21')->assertSee('À examiner')->assertSee('Vacataire')
            ->assertSee('Valider le passage à contractuel')->assertSee('Historique de carrière');
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request->method() === 'GET');
    }

    public function test_no_alert_before_service_anniversary_and_no_alert_without_service(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 20));
        Http::fake(['*' => Http::response(['data' => $this->batch(['service_date' => '2024-09-21'])])]);
        $this->account(['recruitment.read', 'recruitment.transition'])->get('/personnel/recrutements/12')
            ->assertOk()->assertDontSee('À examiner')->assertDontSee('Valider le passage à contractuel');
    }

    public function test_only_service_permission_exposes_service_action(): void
    {
        Http::fake(['*' => Http::response(['data' => $this->batch()])]);
        $this->account(['recruitment.read'])->get('/personnel/recrutements/12')
            ->assertOk()->assertSee('Inactif')->assertDontSee('Enregistrer la prise de service');
        $this->post('/personnel/recrutements/12/agents/5/prise-service')->assertForbidden();
        $this->account(['recruitment.read', 'recruitment.service'], 'agent_ia')->get('/personnel/recrutements/12')
            ->assertOk()->assertSee('Enregistrer la prise de service');
    }

    public function test_ia_service_form_uses_dedicated_establishment_catalogue(): void
    {
        Http::fake([
            '*/recruitment/batches/12*' => Http::response(['data' => $this->batch()]),
            '*/recruitment/establishments*' => Http::response(['data' => [['id' => 6, 'libelle' => 'École de Dakar']]]),
        ]);
        $this->account(['recruitment.read', 'recruitment.service'], 'agent_ia')
            ->get('/personnel/recrutements/12/agents/5/prise-service')->assertOk()
            ->assertSee('École de Dakar')->assertSee('Certificat de prise de service');
        Http::assertSentCount(2);
    }

    public function test_expired_preview_cleanup_preserves_recent_files(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('recruitment-previews/old.csv', 'old');
        Storage::disk('local')->put('recruitment-previews/new.csv', 'new');
        touch(Storage::disk('local')->path('recruitment-previews/old.csv'), now()->subDays(2)->timestamp);
        $this->artisan('recruitment:prune-previews')->assertSuccessful();
        Storage::disk('local')->assertMissing('recruitment-previews/old.csv');
        Storage::disk('local')->assertExists('recruitment-previews/new.csv');
    }

    public function test_service_requires_certificate_and_forwards_it_to_backend(): void
    {
        Http::fake([
            '*/recruitment/batches/12*' => Http::response(['data' => $this->batch()]),
            '*/recruitment/members/5/service' => Http::response(['message' => 'Prise de service enregistrée.']),
        ]);
        $this->account(['recruitment.read', 'recruitment.service'], 'agent_ia')
            ->post('/personnel/recrutements/12/agents/5/prise-service', ['service_date' => '2024-02-01', 'lieu_service_id' => 6])
            ->assertSessionHasErrors('document');
        Http::assertNothingSent();
        $this->post('/personnel/recrutements/12/agents/5/prise-service', [
            'service_date' => '2024-02-01', 'lieu_service_id' => 6,
            'document' => UploadedFile::fake()->createWithContent('certificat.pdf', '%PDF-1.4 certificate'),
        ])->assertRedirect(route('recruitment.show', 12))->assertSessionHas('success');
        Http::assertSent(fn ($request) => str_contains($request->url(), '/members/5/service') && data_get(collect($request->data())->firstWhere('name', 'service_date'), 'contents') === '2024-02-01');
    }

    public function test_backend_refusal_of_transition_is_not_displayed_as_success(): void
    {
        Http::fake([
            '*/recruitment/batches/12*' => Http::response(['data' => $this->batch()]),
            '*/recruitment/members/5/transition' => Http::response(['message' => 'Échéance non atteinte.'], 422),
        ]);
        $this->account(['recruitment.read', 'recruitment.transition'])->from(route('recruitment.show', 12))
            ->post('/personnel/recrutements/12/agents/5/carriere', [
                'effective_date' => '2024-02-01', 'document' => UploadedFile::fake()->createWithContent('decision.pdf', '%PDF-1.4 decision'),
            ])->assertRedirect(route('recruitment.show', 12))->assertSessionHasErrors('operation')->assertSessionMissing('success');
    }

    public function test_document_download_uses_authenticated_backend_and_private_cache(): void
    {
        Http::fake(['*' => Http::response('%PDF-1.4 document', 200, ['Content-Type' => 'application/pdf'])]);
        $this->account(['recruitment.read'])->get('/personnel/recrutements/documents/30')
            ->assertOk()->assertHeader('Content-Type', 'application/pdf')->assertDownload('justificatif.pdf');
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer test-token'));
    }
}
