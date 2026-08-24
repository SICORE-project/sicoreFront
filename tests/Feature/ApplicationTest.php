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

    public function test_financial_institutions_page_displays_required_information(): void
    {
        Http::fake([
            '*/parametrage/institutions-financieres*' => Http::response([
                'data' => [[
                    'code' => 'IF001',
                    'nom' => 'Banque Test',
                    'sigle' => 'BT',
                    'type_institution' => 'Banque',
                    'telephone' => '+221 33 000 00 00',
                    'email' => 'contact@banque.test',
                    'adresse' => 'Dakar',
                    'is_active' => true,
                ], [
                    'code' => 'IF002',
                    'nom' => 'Institution inactive',
                    'sigle' => 'II',
                    'type_institution' => 'Microfinance',
                    'telephone' => '+221 33 111 11 11',
                    'email' => 'contact@inactive.test',
                    'adresse' => 'Thiès',
                    'statut' => 'inactif',
                ]],
            ]),
        ]);

        $response = $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
        ])->get('/parametrage/parametres/institutions-financieres');

        $response->assertOk()
            ->assertSee('Institutions financières')
            ->assertSee('Nom ou libellé')
            ->assertSee('Type d’institution')
            ->assertSee('Téléphone')
            ->assertSee('E-mail')
            ->assertSee('Adresse')
            ->assertSee('Actif')
            ->assertSee('Inactif')
            ->assertSee('id="institutionStatusFilter"', false)
            ->assertSee('id="exportInstitutions"', false)
            ->assertSee('aria-label="Pagination"', false)
            ->assertSee('title="Voir"', false)
            ->assertSee('title="Modifier"', false)
            ->assertSee('Objectifs métier')
            ->assertSee('id="newInstitution"', false)
            ->assertSee('id="importInstitutionsFile"', false)
            ->assertSee('Toutes catégories');
    }

    public function test_expired_backend_token_forces_a_new_login(): void
    {
        Http::fake([
            '*/parametrage/institutions-financieres*' => Http::response([
                'message' => 'Non authentifié.',
            ], 401),
        ]);

        $response = $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
            'access_token' => 'expired-token',
        ])->get('/parametrage/parametres/institutions-financieres');

        $response->assertRedirect(route('login'))
            ->assertSessionHas('warning', 'Votre session backend a expiré. Veuillez vous reconnecter.');

        $this->assertNull(session('access_token'));
        $this->assertNull(session('sicore_user'));
    }

    public function test_financial_institution_can_be_created(): void
    {
        Http::fake([
            '*/parametrage/institutions-financieres' => Http::response([
                'message' => 'Institution financière créée avec succès.',
                'data' => ['id' => 10],
            ], 201),
        ]);

        $payload = [
            'code' => 'IF010',
            'nom' => 'Banque Nouvelle',
            'sigle' => 'BN',
            'type_institution' => 'Banque',
            'adresse' => 'Dakar',
            'telephone' => '+221 33 000 00 00',
            'email' => 'contact@banque.sn',
            'statut' => 'actif',
        ];

        $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
            'access_token' => 'valid-token',
        ])->post('/parametrage/parametres/institutions-financieres', $payload)
            ->assertRedirect(route('parametres.institutions-financieres'))
            ->assertSessionHas('success');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/parametrage/institutions-financieres')
            && $request['code'] === 'IF010'
            && $request['statut'] === 'actif');
    }

    public function test_financial_institution_required_fields_are_validated(): void
    {
        $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
        ])->post('/parametrage/parametres/institutions-financieres', [])
            ->assertSessionHasErrors(['code', 'nom', 'sigle', 'type_institution', 'statut']);
    }

    public function test_financial_institution_can_be_updated_without_changing_status(): void
    {
        Http::fake([
            '*/parametrage/institutions-financieres/15' => Http::response([
                'message' => 'Institution financière modifiée avec succès.',
            ]),
        ]);

        $payload = [
            'code' => 'IF015',
            'nom' => 'Banque Mise à Jour',
            'sigle' => 'BMJ',
            'type_institution' => 'Banque',
            'adresse' => 'Dakar',
            'telephone' => '+221 33 222 22 22',
            'email' => 'contact@bmj.sn',
        ];

        $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
            'access_token' => 'valid-token',
        ])->put('/parametrage/parametres/institutions-financieres/15', $payload)
            ->assertRedirect(route('parametres.institutions-financieres'))
            ->assertSessionHas('success');

        Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
            && str_ends_with($request->url(), '/parametrage/institutions-financieres/15')
            && $request['nom'] === 'Banque Mise à Jour'
            && ! isset($request['statut']));
    }

    public function test_financial_institution_can_be_deactivated_without_deletion(): void
    {
        Http::fake([
            '*/parametrage/institutions-financieres/15/statut' => Http::response([
                'message' => 'Institution financière désactivée.',
            ]),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
            'access_token' => 'valid-token',
        ])->patch('/parametrage/parametres/institutions-financieres/15/statut', [
            'est_actif' => false,
        ])->assertRedirect(route('parametres.institutions-financieres'))
            ->assertSessionHas('success');

        Http::assertSent(fn ($request): bool => $request->method() === 'PATCH'
            && str_ends_with($request->url(), '/parametrage/institutions-financieres/15/statut')
            && $request['est_actif'] === false);
    }

    public function test_financial_institution_status_only_accepts_known_values(): void
    {
        $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
        ])->patch('/parametrage/parametres/institutions-financieres/15/statut', [
            'est_actif' => 'invalide',
        ])->assertSessionHasErrors('est_actif');
    }

    public function test_status_api_error_does_not_open_the_edit_form(): void
    {
        Http::fake([
            '*/parametrage/institutions-financieres/15/statut' => Http::response([
                'message' => 'Changement de statut impossible.',
            ], 500),
            '*/parametrage/institutions-financieres*' => Http::response(['data' => []]),
        ]);

        $session = [
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
            'access_token' => 'valid-token',
        ];

        $this->withSession($session)
            ->patch('/parametrage/parametres/institutions-financieres/15/statut', ['est_actif' => false])
            ->assertSessionHas('error');

        $this->withSession($session)
            ->get('/parametrage/parametres/institutions-financieres')
            ->assertOk()
            ->assertSee('id="institution-form-modal"', false)
            ->assertSee('data-modal  hidden', false);
    }

    public function test_bank_account_can_be_associated_to_a_teacher(): void
    {
        Http::fake([
            '*/enseignants/8/comptes-bancaires' => Http::response([
                'message' => 'Compte bancaire associé avec succès.',
                'data' => ['id' => 25],
            ], 201),
        ]);

        $payload = [
            'enseignant_id' => 8,
            'institut_financier_id' => 3,
            'numero_compte' => 'SN00123456789',
            'rib' => 'SN08 0001 0002 1234 5678 9012',
            'est_actif' => '1',
        ];

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire', 'role' => 'Gestionnaire'],
            'access_token' => 'valid-token',
        ])->post('/parametrage/parametres/comptes-bancaires-enseignants', $payload)
            ->assertRedirect(route('parametres.institutions-financieres'))
            ->assertSessionHas('success');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/enseignants/8/comptes-bancaires')
            && $request['institut_financier_id'] === 3
            && ! isset($request['enseignant_id'])
            && $request['est_actif'] === true);
    }

    public function test_teacher_bank_account_required_fields_are_validated(): void
    {
        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire', 'role' => 'Gestionnaire'],
        ])->post('/parametrage/parametres/comptes-bancaires-enseignants', [])
            ->assertSessionHasErrors(['enseignant_id', 'institut_financier_id', 'numero_compte', 'rib', 'est_actif'], null, 'bankAccount');
    }

    public function test_academy_inspections_are_loaded_from_the_api(): void
    {
        Http::fake([
            '*/parametrage/ia*' => Http::response([
                'data' => [[
                    'code' => 'IA-DKR',
                    'libelle' => 'Inspection d’académie de Dakar',
                    'region' => ['nom' => 'Dakar'],
                    'responsable' => ['nom' => 'Aminata Diop'],
                    'telephone' => '33 800 00 00',
                    'email' => 'ia.dakar@sicore.sn',
                    'statut' => 'actif',
                ]],
            ]),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire', 'role' => 'Gestionnaire'],
            'access_token' => 'valid-token',
        ])->get('/parametrage/parametres/ia')
            ->assertOk()
            ->assertSee('Inspection d’académie de Dakar')
            ->assertSee('Aminata Diop')
            ->assertSee('Actif')
            ->assertSee('data-modal-open="ia-create-modal"', false)
            ->assertSee('id="ia-create-modal"', false)
            ->assertSee('id="iaCreateStatut" name="statut" required', false)
            ->assertSee('Sélectionner un statut')
            ->assertSee('data-modal-open="ia-edit-modal"', false)
            ->assertSee('Enregistrer les modifications')
            ->assertSee('data-ia-toggle', false)
            ->assertSee('Désactiver')
            ->assertSee('Son historique sera conservé.');

        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && str_contains($request->url(), '/api/parametrage/ia'));
    }

    public function test_ief_creation_form_is_displayed_in_a_frontend_modal(): void
    {
        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire', 'role' => 'Gestionnaire'],
        ])->get('/parametrage/parametres/ief')
            ->assertOk()
            ->assertSee('Créer une IEF')
            ->assertSee('data-modal-open="ief-create-modal"', false)
            ->assertSee('id="ief-create-modal"', false)
            ->assertSee('class="filter-panel"', false)
            ->assertDontSee('id="ief-filter-modal"', false)
            ->assertSee('id="iefFilterIa"', false)
            ->assertSee('id="iefFilterStatus"', false)
            ->assertSee('Réinitialiser')
            ->assertSee('>Voir</button>', false)
            ->assertSee('>Modifier</button>', false)
            ->assertSee('>Supprimer</button>', false)
            ->assertDontSee('class="icon-action"', false)
            ->assertSee('data-modal-open="ief-edit-modal"', false)
            ->assertSee('data-ief-edit', false)
            ->assertSee('id="ief-edit-modal"', false)
            ->assertSee('id="iefEditCode"', false)
            ->assertSee('id="iefEditLibelle"', false)
            ->assertSee('id="iefEditIa"', false)
            ->assertSee('Enregistrer les modifications')
            ->assertSee('data-ief-transfer', false)
            ->assertSee('>Transférer</button>', false)
            ->assertSee('id="ief-transfer-modal"', false)
            ->assertSee('id="iefCurrentIa"', false)
            ->assertSee('id="iefDestinationIa"', false)
            ->assertSee('Nouvelle IA active')
            ->assertSee('Confirmer le transfert')
            ->assertSee('Seules les inspections d’académie actives sont proposées.')
            ->assertSee('name="code"', false)
            ->assertSee('name="libelle"', false)
            ->assertSee('name="inspection_academie_id"', false)
            ->assertSee('Données temporaires utilisées en attendant la connexion au backend.')
            ->assertSee('Inspection d’académie de Dakar');
    }

    public function test_academy_page_displays_its_attached_iefs(): void
    {
        Http::fake([
            '*/ias/12/iefs' => Http::response([
                'data' => [
                    ['code' => 'IEF-DKR-N', 'libelle' => 'IEF de Dakar Nord'],
                    ['code' => 'IEF-DKR-S', 'libelle' => 'IEF de Dakar Sud'],
                ],
            ]),
            '*/parametrage/ia*' => Http::response([
                'data' => [[
                    'id' => 12,
                    'code' => 'IA-DKR',
                    'libelle' => 'Inspection d’académie de Dakar',
                ]],
            ]),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire', 'role' => 'Gestionnaire'],
            'access_token' => 'valid-token',
        ])->get('/parametrage/parametres/ia/12')
            ->assertOk()
            ->assertSee('IEF rattachées')
            ->assertSee('IEF-DKR-N')
            ->assertSee('IEF de Dakar Nord')
            ->assertSee('IEF-DKR-S')
            ->assertSee('IEF de Dakar Sud');

        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && str_ends_with($request->url(), '/api/ias/12/iefs'));
    }

    public function test_service_locations_are_loaded_with_their_ia_and_ief(): void
    {
        Http::fake([
            '*/parametrage/lieux-service*' => Http::response([
                'data' => [[
                    'id' => 9,
                    'code' => 'LS-001',
                    'libelle' => 'École élémentaire Liberté',
                    'type' => 'École',
                    'ia' => ['id' => 2, 'libelle' => 'IA de Dakar'],
                    'ief' => ['libelle' => 'IEF Grand Dakar', 'inspection_academie_id' => 2],
                    'adresse' => 'Dakar',
                    'est_actif' => true,
                ]],
            ]),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire', 'role' => 'Gestionnaire'],
            'access_token' => 'valid-token',
        ])->get('/parametrage/parametres/lieux-service')
            ->assertOk()
            ->assertSee('Lieux de service')
            ->assertSee('École élémentaire Liberté')
            ->assertSee('IA de Dakar')
            ->assertSee('IEF Grand Dakar')
            ->assertSee('Conforme')
            ->assertSee('data-lieu-edit', false)
            ->assertSee('data-lieu-status', false)
            ->assertSee('Désactiver')
            ->assertSee('data-lieu-affectation', false)
            ->assertSee('Affecter')
            ->assertSee('Modifier un lieu de service');

        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && str_contains($request->url(), '/api/parametrage/lieux-service')
            && $request['page'] === 1
            && $request['per_page'] === 10);
    }

    public function test_service_location_page_flags_an_ia_ief_mismatch(): void
    {
        Http::fake(['*/parametrage/lieux-service*' => Http::response(['data' => [[
            'code' => 'LS-002', 'libelle' => 'Lycée Test', 'ia' => ['id' => 2, 'libelle' => 'IA Dakar'],
            'ief' => ['libelle' => 'IEF Thiès', 'inspection_academie_id' => 5], 'statut' => 'actif',
        ]]])]);

        $this->withSession(['sicore_user' => ['name' => 'Gestionnaire']])
            ->get('/parametrage/parametres/lieux-service')
            ->assertOk()
            ->assertSee('À vérifier')
            ->assertSee('une incohérence entre l’IA et l’IEF');
    }

    public function test_service_location_can_be_created(): void
    {
        Http::fake([
            '*/parametrage/lieux-service' => Http::response([
                'message' => 'Lieu de service créé avec succès.',
                'data' => ['id' => 21],
            ], 201),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire'],
            'access_token' => 'valid-token',
        ])->post('/parametrage/parametres/lieux-service', [
            'code' => 'LS-021',
            'libelle' => 'École Liberté',
            'ia_id' => 2,
            'ief_id' => 7,
        ])->assertRedirect(route('parametres.lieux-service.index'))
            ->assertSessionHas('success', 'Lieu de service créé avec succès.');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/parametrage/lieux-service')
            && $request['code'] === 'LS-021'
            && $request['libelle'] === 'École Liberté'
            && $request['ia_id'] === 2
            && $request['ief_id'] === 7);
    }

    public function test_service_location_required_fields_are_validated(): void
    {
        $this->withSession(['sicore_user' => ['name' => 'Gestionnaire']])
            ->post('/parametrage/parametres/lieux-service', [])
            ->assertSessionHasErrors(['code', 'libelle', 'ia_id', 'ief_id']);
    }

    public function test_service_location_can_be_updated(): void
    {
        Http::fake([
            '*/parametrage/lieux-service/9' => Http::response([
                'message' => 'Lieu de service modifié avec succès.',
            ]),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire'],
            'access_token' => 'valid-token',
        ])->put('/parametrage/parametres/lieux-service/9', [
            'code' => 'LS-009',
            'libelle' => 'Lycée de Dakar',
            'ia_id' => 2,
            'ief_id' => 7,
        ])->assertRedirect(route('parametres.lieux-service.index'))
            ->assertSessionHas('success', 'Lieu de service modifié avec succès.');

        Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
            && str_ends_with($request->url(), '/api/parametrage/lieux-service/9')
            && $request['code'] === 'LS-009'
            && $request['libelle'] === 'Lycée de Dakar'
            && $request['ia_id'] === 2
            && $request['ief_id'] === 7);
    }

    public function test_service_location_update_preserves_backend_uniqueness_errors(): void
    {
        Http::fake([
            '*/parametrage/lieux-service/9' => Http::response([
                'message' => 'Les données sont invalides.',
                'errors' => ['code' => ['Ce code est déjà utilisé.']],
            ], 422),
        ]);

        $this->withSession(['sicore_user' => ['name' => 'Gestionnaire']])
            ->put('/parametrage/parametres/lieux-service/9', [
                'code' => 'LS-001', 'libelle' => 'Lycée', 'ia_id' => 2, 'ief_id' => 7,
            ])->assertRedirect()
            ->assertSessionHasErrors(['code' => 'Ce code est déjà utilisé.'], null, 'updateLieu')
            ->assertSessionHas('lieu_edit_form_open', true)
            ->assertSessionHas('lieu_edit_id', '9');
    }

    public function test_service_location_update_required_fields_are_validated(): void
    {
        $this->withSession(['sicore_user' => ['name' => 'Gestionnaire']])
            ->put('/parametrage/parametres/lieux-service/9', [])
            ->assertSessionHasErrors(['code', 'libelle', 'ia_id', 'ief_id'], null, 'updateLieu')
            ->assertSessionHas('lieu_edit_form_open', true);
    }

    public function test_service_location_can_be_deactivated_without_deletion(): void
    {
        Http::fake([
            '*/parametrage/lieux-service/9/statut' => Http::response([
                'message' => 'Lieu de service désactivé.',
            ]),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire'],
            'access_token' => 'valid-token',
        ])->patch('/parametrage/parametres/lieux-service/9/statut', ['actif' => '0'])
            ->assertRedirect(route('parametres.lieux-service.index'))
            ->assertSessionHas('success', 'Lieu de service désactivé.');

        Http::assertSent(fn ($request): bool => $request->method() === 'PATCH'
            && str_ends_with($request->url(), '/api/parametrage/lieux-service/9/statut')
            && $request['actif'] === false);
    }

    public function test_service_location_can_be_activated(): void
    {
        Http::fake([
            '*/parametrage/lieux-service/9/statut' => Http::response([], 200),
        ]);

        $this->withSession(['sicore_user' => ['name' => 'Gestionnaire']])
            ->patch('/parametrage/parametres/lieux-service/9/statut', ['actif' => '1'])
            ->assertRedirect(route('parametres.lieux-service.index'))
            ->assertSessionHas('success', 'Lieu de service activé.');

        Http::assertSent(fn ($request): bool => $request['actif'] === true);
    }

    public function test_service_location_status_only_accepts_a_boolean(): void
    {
        Http::fake();

        $this->withSession(['sicore_user' => ['name' => 'Gestionnaire']])
            ->patch('/parametrage/parametres/lieux-service/9/statut', ['actif' => 'inconnu'])
            ->assertSessionHasErrors(['actif'], null, 'statusLieu');

        Http::assertNothingSent();
    }

    public function test_teacher_can_be_assigned_to_an_active_service_location(): void
    {
        Http::fake([
            '*/enseignants/8/affectations' => Http::response([
                'message' => 'Enseignant affecté au lieu de service avec succès.',
                'data' => ['id' => 31, 'actif' => true],
            ], 201),
        ]);

        $this->withSession([
            'sicore_user' => ['id' => 4, 'name' => 'Gestionnaire'],
            'access_token' => 'valid-token',
        ])->post('/parametrage/parametres/lieux-service/9/affectations', [
            'enseignant_id' => 8,
            'date_debut' => '2026-08-22',
        ])->assertRedirect(route('parametres.lieux-service.index'))
            ->assertSessionHas('success', 'Enseignant affecté au lieu de service avec succès.');

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && str_ends_with($request->url(), '/api/enseignants/8/affectations')
            && $request['lieu_service_id'] === '9'
            && $request['date_debut'] === '2026-08-22'
            && $request['actif'] === true
            && ! isset($request['date_fin'])
            && ! isset($request['created_by']));
    }

    public function test_service_location_assignment_required_fields_are_validated(): void
    {
        Http::fake();

        $this->withSession(['sicore_user' => ['name' => 'Gestionnaire']])
            ->post('/parametrage/parametres/lieux-service/9/affectations', [])
            ->assertSessionHasErrors(['enseignant_id', 'date_debut'], null, 'affectationLieu')
            ->assertSessionHas('affectation_form_open', true)
            ->assertSessionHas('affectation_lieu_id', '9');

        Http::assertNothingSent();
    }

    public function test_assignment_backend_errors_are_displayed_in_the_assignment_form(): void
    {
        Http::fake([
            '*/enseignants/8/affectations' => Http::response([
                'message' => 'Affectation impossible.',
                'errors' => ['lieu_service_id' => ['Ce lieu de service est inactif.']],
            ], 422),
        ]);

        $this->withSession(['sicore_user' => ['name' => 'Gestionnaire']])
            ->post('/parametrage/parametres/lieux-service/9/affectations', [
                'enseignant_id' => 8, 'date_debut' => '2026-08-22',
            ])->assertSessionHasErrors(['lieu_service_id' => 'Ce lieu de service est inactif.'], null, 'affectationLieu')
            ->assertSessionHas('affectation_form_open', true);
    }

    public function test_service_locations_can_be_filtered_and_sorted_through_the_api(): void
    {
        Http::fake([
            '*/parametrage/lieux-service*' => Http::response(['data' => ['data' => [[
                'id' => 4, 'code' => 'LS-DKR', 'libelle' => 'École Dakar', 'ia_id' => 1,
                'ia' => ['id' => 1, 'libelle' => 'IA Dakar'],
                'ief' => ['id' => 2, 'libelle' => 'IEF Dakar', 'ia_id' => 1], 'est_actif' => true,
            ]], 'current_page' => 1, 'last_page' => 1, 'total' => 1]], 200),
            '*/parametrage/ia*' => Http::response(['data' => [['id' => 1, 'code' => 'IA-DKR', 'libelle' => 'IA Dakar']]]),
            '*/ias/1/iefs' => Http::response(['data' => [['id' => 2, 'code' => 'IEF-DKR', 'libelle' => 'IEF Dakar', 'ia_id' => 1]]]),
            '*/enseignants*' => Http::response(['data' => []]),
        ]);

        $this->withSession(['sicore_user' => ['name' => 'Gestionnaire'], 'access_token' => 'token'])
            ->get('/parametrage/parametres/lieux-service?search=Dakar&ia_id=1&ief_id=2&statut=actif&sort=code&direction=desc')
            ->assertOk()->assertSee('École Dakar')->assertSee('Réinitialiser')
            ->assertSee('id="lieuxLoading"', false)->assertSee('data-lieu-detail', false)
            ->assertSee('Fiche du lieu de service');

        Http::assertSent(fn ($request): bool => str_contains($request->url(), '/api/parametrage/lieux-service')
            && $request['search'] === 'Dakar' && $request['ia_id'] === '1' && $request['ief_id'] === '2'
            && $request['statut'] === 'actif' && $request['sort'] === 'code' && $request['direction'] === 'desc');
    }

    public function test_central_service_location_coherence_is_not_applicable(): void
    {
        Http::fake([
            '*/parametrage/lieux-service*' => Http::response(['data' => [[
                'id' => 1, 'code' => 'DAGE', 'libelle' => 'DAGE', 'est_actif' => true,
            ]]]),
        ]);

        $this->withSession(['sicore_user' => ['name' => 'Gestionnaire']])
            ->get('/parametrage/parametres/lieux-service')
            ->assertOk()->assertSee('Non applicable')->assertDontSee('À vérifier');
    }
}
