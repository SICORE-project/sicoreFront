<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    public function test_login_page_is_accessible(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Bienvenue sur SICORE')
            ->assertSee('admin@sicore.sn')
            ->assertSee('Sicore@2026');
    }

    public function test_dashboard_requires_a_sicore_session(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_test_user_can_login_and_reach_dashboard(): void
    {
        $this->post('/login', [
            'email' => 'admin@sicore.sn',
            'password' => 'Sicore@2026',
        ])->assertRedirect(route('dashboard'));

        $this->assertSame('Administrateur SICORE', session('sicore_user.name'));
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->post('/login', [
            'email' => 'admin@sicore.sn',
            'password' => 'incorrect',
        ])->assertSessionHasErrors('email');
    }

    public function test_users_page_renders_backend_users(): void
    {
        Http::fake([
            '*/admin/users*' => Http::response([
                'data' => [
                    [
                        'id' => 1,
                        'nom' => 'Diallo',
                        'prenom' => 'Amina',
                        'email' => 'amina.diallo@sicore.sn',
                        'role' => ['nom' => 'Administrateur'],
                        'statut' => true,
                    ],
                    [
                        'id' => 2,
                        'nom' => 'Ndiaye',
                        'prenom' => 'Moussa',
                        'email' => 'moussa.ndiaye@sicore.sn',
                        'role' => ['nom' => 'Gestionnaire paie'],
                        'statut' => true,
                    ],
                ],
            ]),
        ]);

        $this->withSession([
            'sicore_user' => [
                'name' => 'Administrateur SICORE',
                'email' => 'admin@sicore.sn',
                'role' => 'Administrateur',
            ],
        ])->get('/utilisateurs')
            ->assertOk()
            ->assertSee('Amina Diallo')
            ->assertSee('amina.diallo@sicore.sn')
            ->assertSee('Administrateur')
            ->assertSee('Actif');
    }

    public function test_user_creation_form_is_displayed_in_a_modal(): void
    {
        Http::fake([
            '*/admin/users*' => Http::response(['data' => ['data' => []]]),
            '*/admin/roles*' => Http::response([
                'data' => [['id' => 4, 'nom' => 'Gestionnaire']],
            ]),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
        ])->get('/utilisateurs')
            ->assertOk()
            ->assertSee('data-modal-open="create-user-modal"', false)
            ->assertSee('id="create-user-modal"', false)
            ->assertSee('Créer un utilisateur')
            ->assertSee('Gestionnaire');
    }

    public function test_user_creation_form_displays_valid_national_structures(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/national-organisation-options')) {
                return Http::response([
                    'data' => [
                        'data' => [
                            ['id' => 1, 'code' => 'DRH', 'libelle' => 'Direction des ressources humaines'],
                            ['id' => 2, 'code' => 'DAGE', 'libelle' => 'Direction de l administration generale'],
                            ['id' => 3, 'code' => 'DECPC', 'libelle' => 'Direction de la certification'],
                        ],
                    ],
                ]);
            }

            if (str_contains($request->url(), '/organisation-options')) {
                return Http::response(['data' => []]);
            }

            if (str_contains($request->url(), '/admin/roles')) {
                return Http::response(['data' => [['id' => 4, 'nom' => 'DRH', 'slug' => 'drh']]]);
            }

            return Http::response(['data' => ['data' => []]]);
        });

        $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
        ])->get('/utilisateurs')
            ->assertOk()
            ->assertSee('DRH — Direction des ressources humaines')
            ->assertSee('DAGE — Direction de l administration generale')
            ->assertSee('DECPC — Direction de la certification');
    }

    public function test_user_status_is_sent_to_the_api_as_a_string(): void
    {
        Http::fake([
            '*/admin/users' => Http::response([
                'success' => true,
                'message' => 'Utilisateur créé avec succès.',
            ], 201),
        ]);

        $this->withSession([
            'sicore_user' => [
                'name' => 'Administrateur SICORE',
                'email' => 'admin@sicore.sn',
                'role' => 'Administrateur',
            ],
        ])->post('/utilisateurs', [
            'nom' => 'Diallo',
            'prenom' => 'Amina',
            'email' => 'amina.diallo@sicore.sn',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 1,
            'statut' => 'actif',
        ])->assertRedirect(route('utilisateurs.index'));

        Http::assertSent(fn ($request) =>
            $request->url() === config('services.backend.url').'/admin/users'
            && $request['statut'] === 'actif'
        );
    }

    public function test_sidebar_pages_are_rendered_by_laravel(): void
    {
        $session = [
            'sicore_user' => [
                'name' => 'Administrateur SICORE',
                'email' => 'admin@sicore.sn',
                'role' => 'Administrateur',
            ],
        ];

        $pages = [
            '/dashboard' => 'Tableau de bord',
            '/enseignants' => 'Dashboard Enseignant',
            '/enseignants/nouveau' => 'Nouvel enseignant',
            '/paie/etats-presence' => 'États de présence',
            '/paie/avance-tabaski' => 'Avance Tabaski',
            '/paie/retenue-tabaski' => 'Retenue Tabaski',
            '/paie/retenues-rappel' => 'Retenues rappel',
            '/paie/exemptions' => 'Exemption',
            '/paie/travaux-periodiques' => 'Travaux périodiques',
            '/paie/recap-banque' => 'Récapitulatif',
            '/paie/cotisations-sociales' => 'Cotisations sociales',
            '/paie/etat-salaires' => 'État des salaires',
            '/paie/elements-saisie-dashboard' => 'Éléments de saisie',
            '/paie/generee-ief' => 'Paie générée',
            '/paie/fermeture-periode' => 'Fermeture',
            '/paie/edition-salaires-banque' => 'Salaires par banque',
            '/paie/bulletins' => 'Bulletins',
            '/paie/effectifs-corps' => 'Effectifs',
            '/paie/non-generee' => 'Paie non générée',
            '/paie/sommes-percues' => 'Sommes perçues',
            '/credits/delegation' => 'Délégation',
            '/credits/edition-delegations' => 'Édition des délégations',
            '/credits/edition-engagements' => 'Édition des engagements',
            '/indemnites/convocations' => 'Convocations',
            '/indemnites/services-faits' => 'Services faits',
            '/indemnites/pieces-justificatives' => 'Pièces justificatives',
            '/indemnites/accuses-reception' => 'Accusés',
            '/indemnites/calcul' => 'Calcul',
            '/indemnites/frais-deplacement' => 'Frais de déplacement',
            '/indemnites/etats-paie' => 'États de paie',
            '/bourses/enregistrer-demande' => 'Enregistrer',
            '/bourses/valider-dossier' => 'Valider',
            '/bourses/attribuer-aide' => 'Attribuer',
            '/parametres' => 'Paramétrage',
            '/parametres/ief' => 'Inspection',
            '/utilisateurs' => 'Utilisateurs',
            '/utilisateurs/profils-roles' => 'Profils',
            '/utilisateurs/permissions' => 'Permissions',
        ];

        foreach ($pages as $uri => $expectedText) {
            $this->withSession($session)
                ->get($uri)
                ->assertOk()
                ->assertSee($expectedText, false)
                ->assertSee('sidebar', false);
        }
    }
}
