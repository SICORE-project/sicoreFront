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
        $returnUrl = '/paie/avance-tabaski?period_id=12&ia_id=3';

        Http::fake([
            '*/payroll/pages/paie-avance-tabaski*' => Http::response([
                'message' => 'Non authentifié.',
            ], 401),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire paie', 'role' => 'Gestionnaire Paie'],
            'access_token' => 'expired-token',
        ])->get($returnUrl)
            ->assertRedirect(route('login', ['next' => $returnUrl]))
            ->assertSessionMissing('sicore_user')
            ->assertSessionMissing('access_token')
            ->assertSessionHas(
                'warning',
                'Votre session est absente, expirée ou révoquée. Reconnectez-vous pour continuer.'
            );
    }

    public function test_un_acces_sans_session_conserve_la_page_de_paie_et_ses_filtres(): void
    {
        $returnUrl = '/paie/etat-salaires?period_id=12&ia_id=3&with_signature=1';

        $this->get($returnUrl)
            ->assertRedirect(route('login', ['next' => $returnUrl]))
            ->assertSessionHas(
                'warning',
                'Veuillez vous connecter pour accéder à SICORE.'
            );

        $this->get(route('login', ['next' => $returnUrl]))
            ->assertOk()
            ->assertSee('name="next"', false)
            ->assertSee('value="/paie/etat-salaires?period_id=12&amp;ia_id=3&amp;with_signature=1"', false);
    }

    public function test_la_reconnexion_revient_exactement_sur_la_page_de_paie_demandee(): void
    {
        $returnUrl = '/paie/etat-salaires?period_id=12&ia_id=3&with_signature=1';

        Http::fake([
            '*/login' => Http::response([
                'access_token' => 'new-token',
                'message' => 'Connexion réussie.',
                'user' => [
                    'id' => 1,
                    'nom' => 'Diop',
                    'prenom' => 'Mamadou',
                    'email' => 'mamadou.diop@sicore.sn',
                    'role' => [
                        'nom' => 'Gestionnaire Paie',
                        'slug' => 'gestionnaire-paie',
                    ],
                ],
            ]),
        ]);

        $this->post('/login', [
            'email' => 'mamadou.diop@sicore.sn',
            'password' => 'password',
            'next' => $returnUrl,
        ])->assertRedirect($returnUrl)
            ->assertSessionHas('access_token', 'new-token');

        Http::assertSent(fn ($request): bool =>
            str_ends_with($request->url(), '/login')
            && $request['email'] === 'mamadou.diop@sicore.sn'
            && ! isset($request['next'])
        );
    }

    public function test_une_destination_externe_est_refusee_apres_connexion(): void
    {
        Http::fake([
            '*/login' => Http::response([
                'access_token' => 'new-token',
                'message' => 'Connexion réussie.',
                'user' => [
                    'id' => 1,
                    'nom' => 'Diop',
                    'prenom' => 'Mamadou',
                    'email' => 'mamadou.diop@sicore.sn',
                    'role' => [
                        'nom' => 'Gestionnaire Paie',
                        'slug' => 'gestionnaire-paie',
                    ],
                ],
            ]),
        ]);

        $this->post('/login', [
            'email' => 'mamadou.diop@sicore.sn',
            'password' => 'password',
            'next' => 'https://example.net/vol-de-session',
        ])->assertRedirect(route('dashboard'));
    }

    public function test_la_deconnexion_depuis_la_paie_prepare_le_retour_sur_la_meme_page(): void
    {
        $returnUrl = '/paie/bulletins?period_id=12&matricule=PC-TEST-001';

        Http::fake([
            '*/logout' => Http::response(['message' => 'Déconnecté.']),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire paie', 'role' => 'Gestionnaire Paie'],
            'access_token' => 'test-token',
        ])->post('/logout', ['next' => $returnUrl])
            ->assertRedirect(route('login', ['next' => $returnUrl]))
            ->assertSessionMissing('sicore_user')
            ->assertSessionMissing('access_token');
    }

    public function test_une_action_ajax_expiree_fournit_l_url_de_reconnexion_et_de_retour(): void
    {
        $returnUrl = '/paie/elements-saisie-dashboard?period_id=12&ia_id=3';

        Http::fake([
            '*/payroll/actions/add-element*' => Http::response([
                'message' => 'Non authentifié.',
            ], 401),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire paie', 'role' => 'Gestionnaire Paie'],
            'access_token' => 'expired-token',
        ])->withHeader('X-SICORE-NEXT', $returnUrl)
            ->postJson('/paie/actions/add-element', [])
            ->assertUnauthorized()
            ->assertJsonPath('next', $returnUrl)
            ->assertJsonPath('login_url', route('login', ['next' => $returnUrl]))
            ->assertSessionMissing('sicore_user')
            ->assertSessionMissing('access_token');
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
