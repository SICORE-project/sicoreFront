<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TeacherContractuelTest extends TestCase
{
    private function teacherSession(): array
    {
        return ['access_token' => 'token', 'sicore_user' => ['name' => 'Gestionnaire']];
    }

    public function test_form_receives_the_salary_for_each_diploma_category_pair(): void
    {
        Http::fake([
            '*/corps?*' => Http::response(['data' => ['data' => [
                ['id' => 1, 'code' => 'CTR', 'libelle' => 'Contractuel'],
            ]]]),
            '*/categories?*' => Http::response(['data' => ['data' => [
                ['id' => 1, 'corps_id' => 1, 'libelle' => 'A1'],
                ['id' => 2, 'corps_id' => 1, 'libelle' => 'A2'],
            ]]]),
            '*/diplomes?*' => Http::response(['data' => [
                ['id' => 1, 'libelle' => 'LICENCE', 'categorie_id' => 1, 'salaire_brut' => 200000],
                ['id' => 2, 'libelle' => 'LICENCE', 'categorie_id' => 2, 'salaire_brut' => 250000],
            ]]),
            '*' => Http::response(['data' => []]),
        ]);

        $this->withSession($this->teacherSession())->get(route('enseignants.index'))
            ->assertOk()
            ->assertSee('data-corps-code="CTR"', false)
            ->assertSee('data-salaire-brut="200000" data-categorie-id="1"', false)
            ->assertSee('data-salaire-brut="250000" data-categorie-id="2"', false)
            ->assertSee('id="teacher-categorie" name="categorie_id"', false)
            ->assertSee('id="edit-teacher-categorie" name="categorie_id"', false)
            ->assertSee('id="teacher-date-fin-contrat" name="date_fin_contrat"', false)
            ->assertSee('id="edit-teacher-date-fin-contrat" name="date_fin_contrat"', false);
    }

    public static function operations(): array
    {
        return ['création' => ['POST', null], 'modification' => ['PUT', 8]];
    }

    #[DataProvider('operations')]
    public function test_contract_fields_are_forwarded_to_the_backend(string $method, ?int $id): void
    {
        $endpoint = '/admin/personnel/enseignants'.($id ? '/'.$id : '');
        Http::fake(['*'.$endpoint => Http::response(['message' => 'Enseignant enregistré.'], 200)]);
        $payload = [
            'matricule' => 'ENS008', 'nom' => 'Diop', 'prenom' => 'Awa',
            'date_naissance' => '1990-01-01', 'ia_id' => 1, 'ief_id' => 1,
            'corps_id' => 1, 'diplome_id' => 2, 'categorie_id' => 2,
            'salaire_brut' => 250000, 'date_recrutement' => '2026-09-01',
            'date_fin_contrat' => '2027-08-31', 'est_en_couple' => '0',
            'nombre_parts_fiscales' => 1, 'statut' => 'en_activite', 'est_actif' => '1',
        ];

        $this->withSession($this->teacherSession())
            ->call($method, $id ? route('enseignants.update', $id) : route('enseignants.store'), $payload)
            ->assertRedirect(route('enseignants.index'))->assertSessionHasNoErrors();

        Http::assertSent(fn ($request) => $request->method() === $method
            && str_ends_with($request->url(), $endpoint)
            && $request['categorie_id'] === 2 && $request['diplome_id'] === 2
            && $request['salaire_brut'] === 250000 && $request['date_fin_contrat'] === '2027-08-31');
    }
}
