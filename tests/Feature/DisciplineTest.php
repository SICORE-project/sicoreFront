<?php

namespace Tests\Feature;

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
}
