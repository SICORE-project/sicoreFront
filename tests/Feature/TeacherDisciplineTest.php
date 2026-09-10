<?php

namespace Tests\Feature;

use App\Services\Parametrage\EnseignantDisciplineService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TeacherDisciplineTest extends TestCase
{
    private function userSession(array $permissions = ['enseignants.disciplines.associer']): array
    {
        return ['access_token' => 'token', 'sicore_user' => ['name' => 'Gestionnaire', 'permissions' => $permissions]];
    }

    private function fakeDossier(array $associated = [], array $active = []): void
    {
        Http::fake(function ($request) use ($associated, $active) {
            if (str_ends_with($request->url(), '/enseignants/8')) {
                return Http::response(['data' => ['id' => 8, 'matricule' => 'ENS008', 'nom' => 'Diop', 'prenom' => 'Awa', 'disciplines' => $associated]]);
            }

            return Http::response(['data' => ['data' => $active]]);
        });
    }

    public function test_teacher_dossier_displays_associated_disciplines_and_primary_marker(): void
    {
        $this->fakeDossier([
            ['id' => 1, 'code' => 'MAT', 'libelle' => 'Mathématiques', 'statut' => 'actif', 'pivot' => ['est_principale' => true]],
            ['id' => 2, 'code' => 'PHY', 'libelle' => 'Physique', 'statut' => 'inactif', 'pivot' => ['est_principale' => false]],
        ], [
            ['id' => 1, 'code' => 'MAT', 'libelle' => 'Mathématiques', 'statut' => 'actif'],
            ['id' => 3, 'code' => 'CHI', 'libelle' => 'Chimie', 'statut' => 'actif'],
        ]);

        $this->withSession($this->userSession())->get('/parametrage/enseignants/8')->assertOk()
            ->assertSee('Mathématiques')->assertSee('Physique')->assertSee('Principale')
            ->assertSee('CHI')->assertDontSee('<option value="1"', false)->assertDontSee('<option value="2"', false)
            ->assertSee('id="associateDisciplineForm"', false)
            ->assertSee('data-associate-submit', false);
    }

    public function test_valid_discipline_association_updates_teacher_dossier(): void
    {
        Http::fake(['*/enseignants/8/disciplines' => Http::response([
            'message' => 'Discipline associée.',
            'data' => ['enseignant_id' => 8, 'discipline_id' => 3, 'est_principale' => false],
            'audit' => ['id' => 110, 'action' => 'association'],
        ], 201)]);

        $this->withSession($this->userSession())->post('/parametrage/enseignants/8/disciplines', [
            'discipline_id' => 3,
        ])->assertRedirect(route('enseignants.show', 8))->assertSessionHas('success', 'Discipline associée.');

        Http::assertSent(fn ($request) => $request['discipline_id'] === 3 && $request['est_principale'] === false);
    }

    public function test_multiple_disciplines_can_be_associated(): void
    {
        Http::fake(['*/enseignants/8/disciplines' => Http::response(['data' => ['id' => 1]], 201)]);
        foreach ([3, 4] as $disciplineId) {
            $this->withSession($this->userSession())->post('/parametrage/enseignants/8/disciplines', ['discipline_id' => $disciplineId])
                ->assertRedirect(route('enseignants.show', 8));
        }
        Http::assertSentCount(2);
    }

    public function test_discipline_can_be_defined_as_primary(): void
    {
        Http::fake(['*/enseignants/8/disciplines' => Http::response(['data' => ['est_principale' => true]], 201)]);
        $this->withSession($this->userSession())->post('/parametrage/enseignants/8/disciplines', [
            'discipline_id' => 3, 'est_principale' => '1',
        ])->assertRedirect(route('enseignants.show', 8));
        Http::assertSent(fn ($request) => $request['est_principale'] === true);
    }

    public function test_unknown_teacher_is_handled(): void
    {
        Http::fake(['*/enseignants/999' => Http::response(['message' => 'Enseignant introuvable.'], 404)]);
        $this->withSession($this->userSession())->get('/parametrage/enseignants/999')
            ->assertRedirect(route('enseignants.index'))->assertSessionHas('error', 'Enseignant introuvable.');
    }

    #[DataProvider('associationErrorsProvider')]
    public function test_api_association_errors_are_displayed(int $status, string $message): void
    {
        Http::fake(['*/enseignants/8/disciplines' => Http::response(['message' => $message, 'errors' => ['discipline_id' => [$message]]], $status)]);
        $this->withSession($this->userSession())->post('/parametrage/enseignants/8/disciplines', ['discipline_id' => 3])
            ->assertSessionHasErrors(['discipline_id' => $message], null, 'associateDiscipline')
            ->assertSessionHas('discipline_association_form_open', true);
    }

    public static function associationErrorsProvider(): array
    {
        return [
            'discipline inexistante' => [404, 'Discipline introuvable.'],
            'discipline inactive' => [422, 'Cette discipline est inactive.'],
            'association en doublon' => [422, 'Cette discipline est déjà associée.'],
        ];
    }

    public function test_association_is_refused_without_permission(): void
    {
        Http::fake();
        $this->withSession($this->userSession([]))->post('/parametrage/enseignants/8/disciplines', ['discipline_id' => 3])
            ->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_association_form_is_hidden_without_permission(): void
    {
        $this->fakeDossier([], [['id' => 3, 'code' => 'CHI', 'libelle' => 'Chimie', 'statut' => 'actif']]);
        $this->withSession($this->userSession([]))->get('/parametrage/enseignants/8')->assertOk()
            ->assertDontSee('<form class="teacher-form" id="associateDisciplineForm"', false)
            ->assertDontSee('+ Associer une discipline');
    }

    public function test_required_discipline_is_validated_before_api_call(): void
    {
        Http::fake();
        $this->withSession($this->userSession())->post('/parametrage/enseignants/8/disciplines', [])
            ->assertSessionHasErrors('discipline_id', null, 'associateDiscipline');
        Http::assertNothingSent();
    }

    public function test_service_preserves_association_response_and_audit_trace(): void
    {
        Http::fake(['*/enseignants/8/disciplines' => Http::response([
            'data' => ['enseignant_id' => 8, 'discipline_id' => 3, 'disciplines_count' => 2],
            'audit' => ['id' => 110, 'action' => 'association_discipline', 'subject_id' => 8],
        ], 201)]);

        $result = $this->app->make(EnseignantDisciplineService::class)->associate(8, ['discipline_id' => 3, 'est_principale' => false]);

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['data']['disciplines_count']);
        $this->assertSame('association_discipline', $result['audit']['action']);
        $this->assertSame(8, $result['audit']['subject_id']);
    }
}
