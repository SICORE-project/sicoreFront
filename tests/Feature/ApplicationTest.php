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

    public function test_user_status_is_sent_to_the_api_as_a_string(): void
    {
        Http::fake([
            '*/admin/users' => Http::response([
                'success' => true,
                'message' => 'Utilisateur créé avec succès.',
            ], 201),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
            'access_token' => 'test-token',
        ])->post('/utilisateurs', [
            'nom' => 'Diop',
            'prenom' => 'Aminata',
            'email' => 'aminata.diop@example.sn',
            'password' => 'motdepasse',
            'password_confirmation' => 'motdepasse',
            'role_id' => 4,
            'statut' => 'actif',
        ])->assertRedirect(route('utilisateurs.index'));

        Http::assertSent(function ($request): bool {
            return $request->url() === config('services.backend.url').'/admin/users'
                && $request['statut'] === 'actif'
                && is_string($request['statut']);
        });
    }

    public function test_users_are_paginated_ten_per_page(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), '/admin/roles')) {
                return Http::response(['data' => []]);
            }

            $users = array_map(fn (int $id): array => [
                'id' => $id,
                'nom' => 'Utilisateur'.$id,
                'prenom' => 'Test',
                'email' => "user{$id}@example.sn",
                'role' => ['nom' => 'Agent'],
                'statut' => 'actif',
            ], range(11, 20));

            return Http::response([
                'data' => [
                    'current_page' => 2,
                    'data' => $users,
                    'last_page' => 3,
                    'per_page' => 10,
                    'total' => 23,
                ],
            ]);
        });

        $response = $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
        ])->get('/utilisateurs?page=2');

        $response->assertOk()
            ->assertSee('user11@example.sn')
            ->assertSee('user20@example.sn')
            ->assertSee('page=1', false)
            ->assertSee('page=3', false);

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), '/admin/users')
                && $request['page'] === 2
                && $request['per_page'] === 10;
        });
    }

    public function test_email_availability_is_checked_without_submitting_the_form(): void
    {
        Http::fake([
            '*/admin/users/check-email*' => Http::response([
                'available' => false,
                'message' => 'Cette adresse e-mail est déjà utilisée.',
            ]),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
            'access_token' => 'test-token',
        ])->getJson('/utilisateurs/verifier-email?email=existant@example.sn')
            ->assertOk()
            ->assertJson([
                'available' => false,
                'message' => 'Cette adresse e-mail est déjà utilisée.',
            ]);

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), '/admin/users/check-email')
                && $request['email'] === 'existant@example.sn';
        });
    }

    public function test_api_field_error_is_not_duplicated_as_a_general_alert(): void
    {
        Http::fake([
            '*/admin/users' => Http::response([
                'message' => 'The email has already been taken.',
                'errors' => [
                    'email' => ['Cette adresse e-mail est déjà utilisée.'],
                ],
            ], 422),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Admin', 'role' => 'Administrateur'],
            'access_token' => 'test-token',
        ])->from('/utilisateurs')->post('/utilisateurs', [
            'nom' => 'Diop',
            'prenom' => 'Aminata',
            'email' => 'existant@example.sn',
            'password' => 'motdepasse',
            'password_confirmation' => 'motdepasse',
            'role_id' => 4,
            'statut' => 'actif',
        ])->assertRedirect('/utilisateurs')
            ->assertSessionHasErrors([
                'email' => 'Cette adresse e-mail est déjà utilisée.',
            ])
            ->assertSessionMissing('error');
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
            '/paie/edition-enseignants' => 'Édition des enseignants',
            '/paie/prime-scolaire' => 'Prime scolaire',
            '/paie/reliquats' => 'Reliquats',
            '/paie/double-flux' => 'Double flux',
            '/paie/directeurs-interim' => 'Directeurs par intérim',
            '/paie/cumul-enseignants-ief' => 'Cumul des enseignants',
            '/paie/recap-elements-corps' => 'Récapitulatif des éléments',
            '/paie/edition-fonctionnaires' => 'Édition des fonctionnaires',
            '/paie/mutuelles-sante' => 'Édition mutuelle',
            '/paie/situation-affectations' => 'Situation des affectations',
            '/paie/montants-engages-banque' => 'Montants engagés',
            '/paie/heures-supplementaires-interim' => 'Heures supplémentaires',
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

    public function test_travaux_periodiques_affiche_le_catalogue_et_conserve_la_periode(): void
    {
        Http::fake([
            '*/payroll/pages/paie-travaux-periodiques*' => Http::response([
                'data' => [
                    'notice' => 'Données API de test.',
                    'period' => [
                        'id' => 1,
                        'code' => '2026-07',
                        'label' => 'Juillet 2026',
                        'status' => 'open',
                        'status_label' => 'Ouverte',
                    ],
                    'periods' => [[
                        'id' => 1,
                        'code' => '2026-07',
                        'label' => 'Juillet 2026',
                        'status' => 'open',
                        'status_label' => 'Ouverte',
                    ]],
                    'stats' => [],
                    'filters' => [],
                    'actions' => [],
                    'columns' => ['État'],
                    'rows' => [['Disponible']],
                    'report_catalog' => [[
                        'slug' => 'paie-recap-banque',
                        'label' => 'État récapitulatif par banque',
                        'description' => 'Synthèse bancaire de test.',
                        'icon' => 'fa-solid fa-building-columns',
                        'group' => 'Paiements et banques',
                    ]],
                ],
            ]),
        ]);

        $this->withSession([
            'sicore_user' => [
                'name' => 'Administrateur SICORE',
                'email' => 'admin@sicore.sn',
                'role' => 'Administrateur',
            ],
            'access_token' => 'test-token',
        ])->get('/paie/travaux-periodiques?period_id=1')
            ->assertOk()
            ->assertSee('Rapports des travaux périodiques')
            ->assertSee('État récapitulatif par banque')
            ->assertSee('data-periodic-report="paie-recap-banque"', false)
            ->assertSee('/paie/recap-banque?period_id=1', false);
    }

    public function test_etat_des_salaires_affiche_les_rubriques_et_transmet_les_filtres(): void
    {
        Http::fake([
            '*/payroll/pages/paie-etat-salaires*' => Http::response([
                'data' => [
                    'notice' => 'État mensuel détaillé prêt.',
                    'period' => ['id' => 12, 'code' => '2026-09', 'label' => 'Septembre 2026', 'status_label' => 'Ouverte'],
                    'periods' => [['id' => 12, 'label' => 'Septembre 2026', 'status_label' => 'Ouverte']],
                    'academic_years' => [['id' => 1, 'label' => '2025-2026']],
                    'stats' => [],
                    'filters' => [],
                    'actions' => [['label' => 'Exporter CSV', 'code' => 'export', 'style' => 'secondary']],
                    'columns' => [],
                    'rows' => [],
                    'salary_statement' => [
                        'period_label' => 'Septembre 2026',
                        'academic_year' => '2025-2026',
                        'generated_at' => '25/08/2026 à 10:00',
                        'columns' => [
                            ['key' => 'sequence', 'label' => 'N°'],
                            ['key' => 'first_name', 'label' => 'Prénoms'],
                            ['key' => 'gross', 'label' => 'Salaire brut', 'amount' => true],
                            ['key' => 'net', 'label' => 'Net à payer', 'amount' => true],
                            ['key' => 'signature', 'label' => 'Émargement'],
                        ],
                        'rows' => [[
                            'sequence' => 1,
                            'first_name' => 'Moussa',
                            'corps' => 'Professeurs contractuels',
                            'ia' => 'IA Dakar',
                            'ief' => 'IEF Dakar Almadies',
                            'gross_display' => '302 773',
                            'net_display' => '260 670',
                        ]],
                        'totals' => ['gross' => '302 773', 'net' => '260 670'],
                        'service_done' => true,
                        'signatory' => 'Le Directeur de l’Administration générale et de l’Équipement (DAGE)',
                        'filter_options' => [
                            'corps' => [['id' => 2, 'label' => 'Professeurs contractuels']],
                            'ias' => [['id' => 1, 'label' => 'IA Dakar']],
                            'iefs' => [['id' => 2, 'ia_id' => 1, 'label' => 'IEF Dakar Almadies']],
                            'matricules' => [['value' => 'PC-TEST-001', 'label' => 'PC-TEST-001 — Moussa Diop']],
                            'payment_places' => [],
                            'training_centers' => [],
                        ],
                    ],
                ],
            ]),
            '*/payroll/exports/paie-etat-salaires*' => Http::response(
                "N°;Prénoms;Salaire brut;Net à payer\n1;Moussa;302773;260670",
                200,
                [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => 'attachment; filename="etat-salaires-2026-09.csv"',
                ]
            ),
        ]);

        $query = http_build_query([
            'period_id' => 12,
            'academic_year_id' => 1,
            'corps_id' => 2,
            'ia_id' => 1,
            'ief_id' => 2,
            'matricule' => 'PC-TEST-001',
            'with_signature' => 1,
            'dage_signatory' => 1,
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Administrateur SICORE', 'role' => 'Administrateur'],
            'access_token' => 'test-token',
        ])->get('/paie/etat-salaires?'.$query)
            ->assertOk()
            ->assertSee('Préparer l’état mensuel des salaires')
            ->assertSee('Salaire brut')
            ->assertSee('Net à payer')
            ->assertSee('302 773')
            ->assertSee('Émargement')
            ->assertSee('matricule=PC-TEST-001', false)
            ->assertSee('with_signature=1', false)
            ->assertSee('Directeur de l’Administration générale et de l’Équipement');

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), '/payroll/pages/paie-etat-salaires')
                && (int) $request['period_id'] === 12
                && (int) $request['corps_id'] === 2
                && (int) $request['ia_id'] === 1
                && (int) $request['ief_id'] === 2
                && $request['matricule'] === 'PC-TEST-001'
                && (int) $request['with_signature'] === 1;
        });

        $this->withSession([
            'sicore_user' => ['name' => 'Administrateur SICORE', 'role' => 'Administrateur'],
            'access_token' => 'test-token',
        ])->get('/paie/export/paie-etat-salaires?'.$query)
            ->assertOk()
            ->assertDownload('etat-salaires-2026-09.csv');

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), '/payroll/exports/paie-etat-salaires')
                && (int) $request['period_id'] === 12
                && (int) $request['academic_year_id'] === 1
                && (int) $request['corps_id'] === 2
                && (int) $request['with_signature'] === 1
                && (int) $request['dage_signatory'] === 1;
        });
    }

    public function test_credit_edition_buttons_open_reports_and_download_files(): void
    {
        $session = [
            'sicore_user' => [
                'name' => 'Administrateur SICORE',
                'email' => 'admin@sicore.sn',
                'role' => 'Administrateur',
            ],
        ];

        $this->withSession($session)
            ->get('/credits/edition-delegations')
            ->assertOk()
            ->assertSee('href="/credits/edition-delegations/apercu"', false)
            ->assertSee('href="/credits/edition-delegations/export"', false)
            ->assertSee('href="/credits/edition-delegations/DEL-2026-001"', false);

        $this->withSession($session)
            ->get('/credits/edition-delegations/apercu')
            ->assertOk()
            ->assertSee('État des délégations de crédits')
            ->assertSee('DEL-2026-001')
            ->assertSee('Imprimer / Enregistrer en PDF');

        $delegationsExcel = $this->withSession($session)
            ->get('/credits/edition-delegations/export')
            ->assertOk()
            ->assertDownload('delegations-credits-2026.xls');
        $this->assertStringContainsString('<Workbook', $delegationsExcel->streamedContent());

        $this->withSession($session)
            ->get('/credits/edition-engagements')
            ->assertOk()
            ->assertSee('href="/credits/edition-engagements/export/pdf"', false)
            ->assertSee('href="/credits/edition-engagements/export/excel"', false)
            ->assertSee('href="/credits/edition-engagements/0/pdf"', false)
            ->assertSee('href="/credits/edition-engagements/0/excel"', false);

        $engagementsPdf = $this->withSession($session)
            ->get('/credits/edition-engagements/export/pdf')
            ->assertOk()
            ->assertDownload('engagements-credits-2026.pdf');
        $this->assertStringStartsWith('%PDF-1.4', $engagementsPdf->getContent());

        $engagementExcel = $this->withSession($session)
            ->get('/credits/edition-engagements/0/excel')
            ->assertOk()
            ->assertDownload('engagement-credit-1.xls');
        $this->assertStringContainsString('<Workbook', $engagementExcel->streamedContent());
    }
}
