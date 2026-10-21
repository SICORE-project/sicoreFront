<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MergeIntegrationTest extends TestCase
{
    public function test_ief_page_uses_api_data_and_preserves_crud_controls(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*/iefs*' => Http::response(['data' => [
                ['id' => 42, 'code' => 'IEF-TEST', 'libelle' => 'Inspection de verification', 'ia_id' => 1],
            ], 'meta' => ['total' => 1]]),
            '*/ias*' => Http::response(['data' => []]),
        ]);

        $this->withSession(['sicore_user' => ['role' => 'Administrateur'], 'access_token' => 'test-token'])
            ->get(route('parametres.ief.index'))
            ->assertOk()
            ->assertSee('Inspection de verification')
            ->assertSee('data-modal-open="ief-create-modal"', false)
            ->assertSee('data-modal-open="ief-edit-modal"', false)
            ->assertDontSee('<<<<<<<', false)
            ->assertDontSee('IEF001');
    }

    public function test_login_preserves_payroll_destination(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*/login' => Http::response([
            'access_token' => 'test-token',
            'message' => 'Connexion reussie',
            'user' => ['id' => 1, 'nom' => 'Test', 'prenom' => 'Utilisateur', 'email' => 'test@example.com'],
        ])]);

        $this->post(route('login.submit'), [
            'email' => 'test@example.com',
            'password' => 'test-password',
            'next' => '/paie/etats-presence',
        ])->assertRedirect('/paie/etats-presence');
    }
}
