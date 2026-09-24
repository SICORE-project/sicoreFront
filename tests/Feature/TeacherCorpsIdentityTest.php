<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TeacherCorpsIdentityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['name' => 'Gestionnaire']]);
        Http::preventStrayRequests();
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'matricule' => '543678/F', 'indice' => '0012',
            'nom' => 'Ndiaye', 'prenom' => 'Awa', 'date_naissance' => '1990-01-01',
            'ia_id' => 1, 'ief_id' => 2, 'corps_id' => 5,
            'est_en_couple' => '0', 'nombre_parts_fiscales' => '1', 'statut' => 'en_activite', 'est_actif' => '1',
        ], $overrides);
    }

    private function fakeBackend(array $response = ['message' => 'Enseignant créé.'], int $status = 201): void
    {
        Http::fake(function ($request) use ($response, $status) {
            if ($request->method() === 'GET' && str_contains($request->url(), '/corps/')) {
                $fonctionnaire = str_contains($request->url(), '/corps/5');
                return Http::response(['data' => ['libelle' => $fonctionnaire ? 'Fonctionnaire' : 'Vacataire', 'code' => $fonctionnaire ? 'FONC' : 'VAC']]);
            }
            return Http::response($response, $status);
        });
    }

    public function test_forms_use_corps_instead_of_administrative_status(): void
    {
        Http::fake(['*' => Http::response(['data' => []])]);
        $this->get(route('enseignants.index'))->assertOk()
            ->assertDontSee('Statut administratif')->assertDontSee('name="statut_administratif"', false)
            ->assertSee('id="teacher-indice"', false)->assertSee('id="edit-teacher-indice"', false)
            ->assertSee('pattern="[0-9]{4,6}"', false);
    }

    public function test_civil_servant_identity_reaches_backend_without_losing_leading_zeroes(): void
    {
        $this->fakeBackend();
        $this->post(route('enseignants.store'), $this->payload())->assertSessionHasNoErrors()->assertRedirect(route('enseignants.index'));
        Http::assertSent(fn ($request) => $request->method() === 'POST' && $request['matricule'] === '543678/F'
            && $request['indice'] === '0012' && ! isset($request['statut_administratif']));
    }

    public function test_non_civil_servant_index_is_ignored_based_on_corps_even_with_forged_status(): void
    {
        $this->fakeBackend();
        $this->post(route('enseignants.store'), $this->payload([
            'corps_id' => 3, 'statut_administratif' => 'fonctionnaire', 'matricule' => '202409675/H', 'indice' => 'invalid',
        ]))->assertSessionHasNoErrors();
        Http::assertSent(fn ($request) => $request->method() === 'POST' && $request['indice'] === null && $request['matricule'] === '202409675/H');
    }

    public static function invalidIdentity(): array
    {
        return [
            [['matricule' => '123456789/H'], 'matricule'],
            [['corps_id' => 3], 'matricule'],
            [['matricule' => 'A12345/F'], 'matricule'],
            [['matricule' => ' 543678/F '], 'matricule'],
            [['matricule' => '543678'], 'matricule'],
            [['matricule' => '543678/f'], 'matricule'],
            [['indice' => null], 'indice'],
            [['indice' => '123'], 'indice'],
            [['indice' => '1234567'], 'indice'],
            [['indice' => '12.34'], 'indice'],
        ];
    }

    #[DataProvider('invalidIdentity')]
    public function test_invalid_identity_is_not_sent_to_teacher_creation_api(array $data, string $field): void
    {
        $this->fakeBackend();
        $this->from(route('enseignants.index'))->post(route('enseignants.store'), $this->payload($data))
            ->assertRedirect(route('enseignants.index'))->assertSessionHasErrors($field);
        Http::assertNotSent(fn ($request) => $request->method() === 'POST');
    }

    public function test_backend_duplicate_index_error_is_displayed(): void
    {
        $this->fakeBackend(['errors' => ['indice' => ['Cet indice est déjà attribué à un autre enseignant.']]], 422);
        $this->from(route('enseignants.index'))->post(route('enseignants.store'), $this->payload())
            ->assertSessionHasErrors(['indice' => 'Cet indice est déjà attribué à un autre enseignant.'])
            ->assertSessionHasInput('indice', '0012');
    }

    public function test_backend_duplicate_error_is_shown_and_form_values_are_preserved(): void
    {
        $this->fakeBackend(['errors' => ['matricule' => ['Ce matricule existe déjà.']]], 422);
        $this->from(route('enseignants.index'))->post(route('enseignants.store'), $this->payload())
            ->assertSessionHasErrors(['matricule' => 'Ce matricule existe déjà.'])
            ->assertSessionHasInput('matricule', '543678/F')->assertSessionHasInput('indice', '0012');
    }
}
