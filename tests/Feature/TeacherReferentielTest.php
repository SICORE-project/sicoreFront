<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TeacherReferentielTest extends TestCase
{
    public static function referentiels(): array
    {
        return [
            'corps' => ['corps', 'corps', ['code' => 'VAC', 'libelle' => 'Vacataire']],
            'categorie' => ['categorie', 'categories', ['libelle' => 'A1', 'corps_id' => 1, 'ordre' => 2, 'description' => 'Description', 'est_actif' => '0']],
            'discipline' => ['discipline', 'parametrage/disciplines', ['libelle' => 'Mathématiques', 'description' => 'Description']],
            'diplome' => ['diplome', 'diplomes', ['libelle' => 'MASTER', 'categorie_id' => 1, 'salaire_brut' => '123456.50']],
            'lieu' => ['lieu_service', 'parametrage/lieux-service', ['libelle' => 'École Liberté', 'ia_id' => 1, 'ief_id' => 2, 'telephone' => '771234567']],
            'lieu sans téléphone' => ['lieu_service', 'parametrage/lieux-service', ['libelle' => 'École Liberté', 'ia_id' => 1, 'ief_id' => 2]],
            'banque' => ['banque', 'parametrage/institutions-financieres', ['libelle' => 'Banque', 'sigle' => null, 'type_institution' => 'Banque', 'adresse' => 'Dakar', 'telephone' => '771234567', 'email' => 'banque@example.com', 'code_banque' => '00123', 'code_guichet' => '00045']],
            'ia' => ['ia', 'ias', ['code' => 'DKR', 'libelle' => 'Dakar', 'region_id' => 1]],
            'ief' => ['ief', 'iefs', ['code' => 'IEF01', 'libelle' => 'IEF Dakar', 'ia_id' => 1, 'adresse' => 'Dakar', 'telephone' => '771234567', 'email' => 'ief@example.com', 'responsable' => 'Awa Diop', 'est_actif' => '0']],
        ];
    }

    #[DataProvider('referentiels')]
    public function test_all_fields_are_forwarded_to_the_backend(string $type, string $endpoint, array $fields): void
    {
        Http::fake(['*/'.$endpoint => Http::response(['data' => ['id' => 42] + $fields], 201)]);

        $this->withSession(['access_token' => 'token', 'sicore_user' => ['name' => 'Gestionnaire']])
            ->postJson(route('enseignants.referentiels.store'), ['type' => $type] + $fields)
            ->assertOk()->assertJsonPath('data.id', 42);

        $expected = $fields;
        Http::assertSent(fn ($request) => str_ends_with($request->url(), '/'.$endpoint)
            && $request->data() === $expected);
    }

    public function test_ief_location_requires_both_parent_structures(): void
    {
        Http::fake();
        $this->withSession(['access_token' => 'token', 'sicore_user' => ['name' => 'Gestionnaire']])
            ->postJson(route('enseignants.referentiels.store'), [
                'type' => 'lieu_service', 'libelle' => 'École Liberté',
            ])->assertUnprocessable()->assertJsonValidationErrors(['ia_id', 'ief_id']);
        Http::assertNothingSent();
    }
}
