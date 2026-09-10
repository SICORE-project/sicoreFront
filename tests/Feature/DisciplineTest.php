<?php

namespace Tests\Feature;

use App\Services\Parametrage\DisciplineService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DisciplineTest extends TestCase
{
    private function userSession(array $permissions = ['parametrage.disciplines.consulter']): array
    {
        return ['access_token' => 'token', 'sicore_user' => ['name' => 'Gestionnaire', 'permissions' => $permissions]];
    }

    private function fakeList(array $items = [], array $meta = []): void
    {
        Http::fake(['*/parametrage/disciplines*' => Http::response(['data' => array_merge([
            'data' => $items, 'current_page' => 1, 'last_page' => 1, 'total' => count($items),
        ], $meta)])]);
    }

    public function test_consultation_with_required_permission(): void
    {
        $this->fakeList([['code' => 'MAT', 'libelle' => 'Mathématiques', 'description' => 'Sciences exactes', 'statut' => 'actif']]);
        $this->withSession($this->userSession())->get('/parametrage/parametres/disciplines')->assertOk()->assertSee('MAT')->assertSee('Mathématiques')->assertSee('Sciences exactes')->assertSee('Actif');
    }

    public function test_access_is_refused_without_permission(): void
    {
        $this->withSession($this->userSession([]))->get('/parametrage/parametres/disciplines')->assertForbidden();
    }

    #[DataProvider('filtersProvider')]
    public function test_filters_are_forwarded_to_api(array $query, string $key, string $value): void
    {
        $this->fakeList();
        $this->withSession($this->userSession())->get('/parametrage/parametres/disciplines?'.http_build_query($query))->assertOk();
        Http::assertSent(fn ($request) => (string) $request[$key] === $value);
    }

    public static function filtersProvider(): array
    {
        return [
            'recherche par code' => [['search' => 'MAT'], 'search', 'MAT'],
            'recherche par libellé' => [['search' => 'Mathématiques'], 'search', 'Mathématiques'],
            'disciplines actives' => [['statut' => 'actif'], 'statut', 'actif'],
            'disciplines inactives' => [['statut' => 'inactif'], 'statut', 'inactif'],
            'pagination' => [['page' => 2], 'page', '2'],
            'tri' => [['sort' => 'libelle', 'direction' => 'desc'], 'direction', 'desc'],
        ];
    }

    public function test_empty_list_is_displayed(): void
    {
        $this->fakeList();
        $this->withSession($this->userSession())->get('/parametrage/parametres/disciplines')->assertOk()->assertSee('Aucune discipline trouvée.');
    }

    public function test_api_errors_are_displayed(): void
    {
        Http::fake(['*/parametrage/disciplines*' => Http::response(['message' => 'Erreur métier.'], 500)]);
        $this->withSession($this->userSession())->get('/parametrage/parametres/disciplines')->assertOk()->assertSee('Erreur métier.');
    }

    public function test_a_discipline_can_be_added(): void
    {
        Http::fake(['*/parametrage/disciplines' => Http::response([
            'message' => 'Discipline créée.',
            'data' => ['id' => 12, 'code' => 'PHY', 'libelle' => 'Physique', 'statut' => 'actif'],
            'audit' => ['id' => 91, 'action' => 'creation'],
        ], 201)]);

        $this->withSession($this->userSession(['parametrage.disciplines.creer']))->post('/parametrage/parametres/disciplines', [
            'code' => 'PHY',
            'libelle' => 'Physique',
            'description' => 'Sciences physiques',
            'statut' => 'actif',
        ])->assertRedirect(route('parametres.disciplines.index'))->assertSessionHas('success', 'Discipline créée.');

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request['code'] === 'PHY'
            && $request['libelle'] === 'Physique'
            && $request['statut'] === 'actif'
            && $request->hasHeader('Authorization', 'Bearer token'));
    }

    public function test_creation_form_is_only_displayed_with_creation_permission(): void
    {
        $this->fakeList();
        $this->withSession($this->userSession())->get('/parametrage/parametres/disciplines')
            ->assertDontSee('id="disciplineCreateForm"', false)
            ->assertDontSee('Ajouter une discipline');

        $this->withSession($this->userSession([
            'parametrage.disciplines.consulter', 'parametrage.disciplines.creer',
        ]))->get('/parametrage/parametres/disciplines')
            ->assertSee('disciplineCreateForm')
            ->assertSee('data-discipline-submit', false)
            ->assertSee('pattern="[A-Z0-9]+(?:[-_][A-Z0-9]+)*"', false)
            ->assertSee('Champs obligatoires');
    }

    public function test_missing_required_creation_fields_are_rejected(): void
    {
        Http::fake();
        $this->withSession($this->userSession(['parametrage.disciplines.creer']))
            ->post('/parametrage/parametres/disciplines', [])
            ->assertSessionHasErrors(['code', 'libelle']);
        Http::assertNothingSent();
    }

    public function test_invalid_code_format_is_rejected(): void
    {
        Http::fake();
        $this->withSession($this->userSession(['parametrage.disciplines.creer']))
            ->post('/parametrage/parametres/disciplines', ['code' => 'physique générale', 'libelle' => 'Physique'])
            ->assertSessionHasErrors('code');
        Http::assertNothingSent();
    }

    #[DataProvider('uniquenessErrorsProvider')]
    public function test_api_uniqueness_errors_are_displayed(string $field, string $message): void
    {
        Http::fake(['*/parametrage/disciplines' => Http::response([
            'message' => 'Données invalides.', 'errors' => [$field => [$message]],
        ], 422)]);

        $this->withSession($this->userSession(['parametrage.disciplines.creer']))
            ->post('/parametrage/parametres/disciplines', ['code' => 'PHY', 'libelle' => 'Physique'])
            ->assertSessionHasErrors([$field => $message])
            ->assertSessionHas('discipline_create_form_open', true);
    }

    public static function uniquenessErrorsProvider(): array
    {
        return [
            'code déjà utilisé' => ['code', 'Ce code est déjà utilisé.'],
            'libellé déjà utilisé' => ['libelle', 'Ce libellé est déjà utilisé.'],
        ];
    }

    public function test_creation_is_refused_without_creation_permission(): void
    {
        Http::fake();
        $this->withSession($this->userSession())->post('/parametrage/parametres/disciplines', [
            'code' => 'PHY', 'libelle' => 'Physique',
        ])->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_discipline_is_active_by_default(): void
    {
        Http::fake(['*/parametrage/disciplines' => Http::response(['data' => ['id' => 12]], 201)]);
        $this->withSession($this->userSession(['parametrage.disciplines.creer']))
            ->post('/parametrage/parametres/disciplines', ['code' => 'PHY', 'libelle' => 'Physique'])
            ->assertRedirect(route('parametres.disciplines.index'));
        Http::assertSent(fn ($request) => $request['statut'] === 'actif');
    }

    public function test_update_action_and_prefilled_data_require_update_permission(): void
    {
        $discipline = ['id' => 7, 'code' => 'MAT', 'libelle' => 'Mathématiques', 'description' => 'Algèbre', 'statut' => 'actif'];
        $this->fakeList([$discipline]);
        $this->withSession($this->userSession())->get('/parametrage/parametres/disciplines')
            ->assertDontSee('<button class="table-action"', false)
            ->assertDontSee('<form class="teacher-form" id="disciplineUpdateForm"', false);

        $this->withSession($this->userSession([
            'parametrage.disciplines.consulter', 'parametrage.disciplines.modifier',
        ]))->get('/parametrage/parametres/disciplines')
            ->assertSee('data-discipline-edit', false)
            ->assertSee('&quot;code&quot;:&quot;MAT&quot;', false)
            ->assertSee('id="disciplineUpdateForm"', false)
            ->assertSee('data-update-submit', false);
    }

    public function test_a_discipline_can_be_updated_and_list_is_refreshed(): void
    {
        Http::fake([
            '*/parametrage/disciplines/7' => Http::response([
                'message' => 'Discipline modifiée.',
                'data' => ['id' => 7, 'code' => 'MATH', 'libelle' => 'Mathématiques générales', 'description' => 'Nouveau', 'statut' => 'actif'],
                'audit' => ['id' => 92, 'action' => 'modification', 'subject_id' => 7],
            ]),
        ]);

        $this->withSession($this->userSession(['parametrage.disciplines.modifier']))
            ->put('/parametrage/parametres/disciplines/7', [
                'code' => 'MATH', 'libelle' => 'Mathématiques générales', 'description' => 'Nouveau', 'statut' => 'actif',
            ])->assertRedirect(route('parametres.disciplines.index'))
            ->assertSessionHas('success', 'Discipline modifiée.');

        Http::assertSent(fn ($request) => $request->method() === 'PUT'
            && str_ends_with($request->url(), '/parametrage/disciplines/7')
            && $request['code'] === 'MATH'
            && $request['libelle'] === 'Mathématiques générales');
    }

    public function test_updating_an_unknown_discipline_displays_api_error(): void
    {
        Http::fake(['*/parametrage/disciplines/999' => Http::response(['message' => 'Discipline introuvable.'], 404)]);
        $this->withSession($this->userSession(['parametrage.disciplines.modifier']))
            ->put('/parametrage/parametres/disciplines/999', ['code' => 'MAT', 'libelle' => 'Mathématiques', 'statut' => 'actif'])
            ->assertSessionHasErrors(['api' => 'Discipline introuvable.'], null, 'updateDiscipline')
            ->assertSessionHas('discipline_update_form_open', true);
    }

    public function test_required_update_fields_are_validated(): void
    {
        Http::fake();
        $this->withSession($this->userSession(['parametrage.disciplines.modifier']))
            ->put('/parametrage/parametres/disciplines/7', [])
            ->assertSessionHasErrors(['code', 'libelle', 'statut'], null, 'updateDiscipline');
        Http::assertNothingSent();
    }

    #[DataProvider('updateUniquenessProvider')]
    public function test_update_uniqueness_errors_are_displayed(string $field, string $message): void
    {
        Http::fake(['*/parametrage/disciplines/7' => Http::response(['errors' => [$field => [$message]]], 422)]);
        $this->withSession($this->userSession(['parametrage.disciplines.modifier']))
            ->put('/parametrage/parametres/disciplines/7', ['code' => 'MAT', 'libelle' => 'Mathématiques', 'statut' => 'actif'])
            ->assertSessionHasErrors([$field => $message], null, 'updateDiscipline');
    }

    public static function updateUniquenessProvider(): array
    {
        return [
            'code affecté à une autre discipline' => ['code', 'Ce code est attribué à une autre discipline.'],
            'libellé déjà utilisé' => ['libelle', 'Ce libellé est déjà utilisé.'],
        ];
    }

    public function test_current_discipline_code_is_preserved_and_accepted(): void
    {
        Http::fake(['*/parametrage/disciplines/7' => Http::response(['data' => ['id' => 7, 'code' => 'MAT']], 200)]);
        $this->withSession($this->userSession(['parametrage.disciplines.modifier']))
            ->put('/parametrage/parametres/disciplines/7', ['code' => 'MAT', 'libelle' => 'Mathématiques avancées', 'statut' => 'actif'])
            ->assertRedirect(route('parametres.disciplines.index'));
        Http::assertSent(fn ($request) => $request['code'] === 'MAT' && str_ends_with($request->url(), '/7'));
    }

    public function test_update_is_refused_without_permission(): void
    {
        Http::fake();
        $this->withSession($this->userSession())->put('/parametrage/parametres/disciplines/7', [
            'code' => 'MAT', 'libelle' => 'Mathématiques', 'statut' => 'actif',
        ])->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_update_service_preserves_api_response_and_audit_structure(): void
    {
        Http::fake(['*/parametrage/disciplines/7' => Http::response([
            'message' => 'Discipline modifiée.',
            'data' => ['id' => 7, 'code' => 'MAT', 'libelle' => 'Mathématiques', 'statut' => 'actif'],
            'audit' => ['id' => 92, 'action' => 'modification', 'subject_id' => 7],
        ])]);

        $result = $this->withSession($this->userSession(['parametrage.disciplines.modifier']))
            ->app->make(DisciplineService::class)
            ->update(7, ['code' => 'MAT', 'libelle' => 'Mathématiques', 'statut' => 'actif']);

        $this->assertTrue($result['success']);
        $this->assertSame(7, $result['data']['id']);
        $this->assertSame('modification', $result['audit']['action']);
        $this->assertSame(7, $result['audit']['subject_id']);
    }

    public function test_active_discipline_can_be_deactivated_without_removing_associations(): void
    {
        Http::fake(['*/parametrage/disciplines/7/statut' => Http::response([
            'message' => 'Discipline désactivée.',
            'data' => ['id' => 7, 'statut' => 'inactif', 'associations_count' => 4],
            'audit' => ['id' => 101, 'action' => 'desactivation', 'subject_id' => 7],
        ])]);

        $this->withSession($this->userSession(['parametrage.disciplines.changer-statut']))
            ->patch('/parametrage/parametres/disciplines/7/statut', ['statut' => 'inactif'])
            ->assertRedirect(route('parametres.disciplines.index'))
            ->assertSessionHas('success', 'Discipline désactivée.');

        Http::assertSent(fn ($request) => $request->method() === 'PATCH'
            && $request->data() === ['statut' => 'inactif']);
    }

    public function test_inactive_discipline_can_be_reactivated(): void
    {
        Http::fake(['*/parametrage/disciplines/7/statut' => Http::response(['message' => 'Discipline réactivée.'])]);
        $this->withSession($this->userSession(['parametrage.disciplines.changer-statut']))
            ->patch('/parametrage/parametres/disciplines/7/statut', ['statut' => 'actif'])
            ->assertSessionHas('success', 'Discipline réactivée.');
        Http::assertSent(fn ($request) => $request['statut'] === 'actif');
    }

    public function test_invalid_status_value_is_rejected(): void
    {
        Http::fake();
        $this->withSession($this->userSession(['parametrage.disciplines.changer-statut']))
            ->patch('/parametrage/parametres/disciplines/7/statut', ['statut' => 'supprime'])
            ->assertSessionHasErrors('statut');
        Http::assertNothingSent();
    }

    public function test_status_change_for_unknown_discipline_displays_api_error(): void
    {
        Http::fake(['*/parametrage/disciplines/999/statut' => Http::response(['message' => 'Discipline introuvable.'], 404)]);
        $this->withSession($this->userSession(['parametrage.disciplines.changer-statut']))
            ->patch('/parametrage/parametres/disciplines/999/statut', ['statut' => 'inactif'])
            ->assertSessionHas('error', 'Discipline introuvable.');
    }

    public function test_status_change_is_refused_without_permission(): void
    {
        Http::fake();
        $this->withSession($this->userSession())->patch('/parametrage/parametres/disciplines/7/statut', [
            'statut' => 'inactif',
        ])->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_status_action_is_permission_protected_and_adapts_to_current_status(): void
    {
        $this->fakeList([
            ['id' => 7, 'code' => 'MAT', 'libelle' => 'Mathématiques', 'statut' => 'actif'],
            ['id' => 8, 'code' => 'LAT', 'libelle' => 'Latin', 'statut' => 'inactif'],
        ]);
        $this->withSession($this->userSession())->get('/parametrage/parametres/disciplines')
            ->assertDontSee('class="inline-form"', false)
            ->assertSee('Inactif');

        $this->withSession($this->userSession([
            'parametrage.disciplines.consulter', 'parametrage.disciplines.changer-statut',
        ]))->get('/parametrage/parametres/disciplines')
            ->assertSee('data-status-form', false)
            ->assertSee('Désactiver cette discipline ?')
            ->assertSee('Réactiver cette discipline ?')
            ->assertSee('data-status-submit', false);
    }

    public function test_inactive_discipline_remains_consultable(): void
    {
        $this->fakeList([['id' => 8, 'code' => 'LAT', 'libelle' => 'Latin', 'statut' => 'inactif']]);
        $this->withSession($this->userSession())->get('/parametrage/parametres/disciplines')
            ->assertOk()->assertSee('LAT')->assertSee('Latin')->assertSee('Inactif');
    }

    public function test_only_active_disciplines_are_loaded_for_selection(): void
    {
        Http::fake(['*/parametrage/disciplines*' => Http::response(['data' => ['data' => [
            ['id' => 7, 'code' => 'MAT', 'libelle' => 'Mathématiques', 'statut' => 'actif'],
        ]]])]);

        $items = $this->app->make(DisciplineService::class)->getActiveForSelection();

        $this->assertCount(1, $items);
        $this->assertSame('MAT', $items[0]['code']);
        Http::assertSent(fn ($request) => $request['statut'] === 'actif' && $request['per_page'] === 100);
    }

    public function test_status_service_preserves_api_response_and_audit_trace(): void
    {
        Http::fake(['*/parametrage/disciplines/7/statut' => Http::response([
            'data' => ['id' => 7, 'statut' => 'inactif', 'associations_count' => 4],
            'audit' => ['id' => 101, 'action' => 'desactivation', 'subject_id' => 7],
        ])]);

        $result = $this->app->make(DisciplineService::class)->updateStatus(7, 'inactif');

        $this->assertTrue($result['success']);
        $this->assertSame(4, $result['data']['associations_count']);
        $this->assertSame('desactivation', $result['audit']['action']);
        $this->assertSame(7, $result['audit']['subject_id']);
    }
}
