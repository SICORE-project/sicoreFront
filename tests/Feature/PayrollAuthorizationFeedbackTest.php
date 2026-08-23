<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayrollAuthorizationFeedbackTest extends TestCase
{
    public function test_la_page_de_connexion_ne_contient_aucun_marqueur_de_conflit_git(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('<<<<<<< HEAD')
            ->assertDontSee('=======')
            ->assertDontSee('>>>>>>> origin/module-paie');
    }

    public function test_un_refus_de_consultation_explique_clairement_le_role_concerne(): void
    {
        Http::fake([
            '*/payroll/pages/paie-avance-tabaski*' => Http::response([
                'message' => 'Accès interdit.',
            ], 403),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Compte enseignant', 'role' => 'Enseignant'],
            'access_token' => 'test-token',
        ])->get('/paie/avance-tabaski')
            ->assertOk()
            ->assertSee('Accès refusé pour votre rôle')
            ->assertSee('Votre compte est bien connecté avec le rôle « Enseignant »')
            ->assertSee('Contactez un administrateur pour vérifier les droits associés à votre rôle.')
            ->assertDontSee('Connexion au backend indisponible');
    }

    public function test_une_session_api_invalide_demande_explicitement_une_reconnexion(): void
    {
        Http::fake([
            '*/payroll/pages/paie-avance-tabaski*' => Http::response([
                'message' => 'Non authentifié.',
            ], 401),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire paie', 'role' => 'Gestionnaire Paie'],
            'access_token' => 'expired-token',
        ])->get('/paie/avance-tabaski')
            ->assertRedirect('/')
            ->assertSessionMissing('sicore_user')
            ->assertSessionMissing('access_token')
            ->assertSessionHas(
                'warning',
                'Votre session est absente, expirée ou révoquée. Reconnectez-vous pour continuer.'
            );
    }

    public function test_un_compte_sans_autorisation_recoit_une_explication_a_la_connexion(): void
    {
        Http::fake([
            '*/login' => Http::response(['message' => 'Accès interdit.'], 403),
        ]);

        $this->post('/login', [
            'email' => 'sans.role@sicore.sn',
            'password' => 'password',
        ])->assertRedirect('/')
            ->assertSessionHasErrors([
                'email' => 'Votre compte est reconnu, mais il n’est pas autorisé à se connecter à SICORE. Vérifiez que le compte est actif et qu’un rôle lui est attribué, puis contactez un administrateur.',
            ]);
    }
}
