<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OrganisationScopeTest extends TestCase
{
    public function test_ia_manager_dashboard_is_scoped_to_own_ia(): void
    {
        Http::fake(['*/dashboard*' => Http::response(['data' => ['enseignants' => 12]])]);

        $response = $this->withSession([
            'access_token' => 'token',
            'sicore_user' => [
                'id' => 7,
                'role_slug' => 'gestionnaire_ia',
                'acces_organisationnel' => [
                    'ia_id' => 4,
                    'ia' => ['id' => 4, 'code' => 'IA-DKR', 'libelle' => 'Dakar'],
                ],
            ],
        ])->get('/dashboard');

        $response->assertOk()->assertSee('IA-DKR')->assertSee('12');
        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/dashboard') && (string) $request['ia_id'] === '4');
    }

    public function test_global_user_does_not_receive_an_organisation_filter(): void
    {
        Http::fake(['*/dashboard*' => Http::response(['data' => []])]);
        $this->withSession(['sicore_user' => ['id' => 1], 'access_token' => 'token'])->get('/dashboard')->assertOk();
        Http::assertSent(fn ($request): bool => ! isset($request['ia_id']) && ! isset($request['ief_id']) && ! isset($request['structure_organisationnelle_id']));
    }
}