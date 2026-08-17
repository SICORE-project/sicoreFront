<?php

namespace Tests\Feature;

use App\Contracts\SicoreApiClientInterface;
use App\Services\SicoreApi;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Tests fonctionnels du frontend : connexion, sécurité, pages, navigation et Paie.
 *
 * Http::fake remplace temporairement le backend par des réponses contrôlées.
 * Les tests restent ainsi rapides et indiquent précisément si une régression
 * vient du frontend. Le parcours HTTP réel est contrôlé séparément en recette.
 */
class ApplicationTest extends TestCase
{
    /** Prépare les réponses API communes avant chaque méthode de test. */
    protected function setUp(): void
    {
        parent::setUp();

        // Répondre selon l'endpoint que SicoreApi essaie d'appeler.
        Http::fake(function (Request $request) {
            if (str_ends_with($request->url(), '/api/login')) {
                if ($request['password'] === 'incorrect') {
                    return Http::response(['message' => 'Identifiants invalides'], 401);
                }

                return Http::response([
                    'token' => '1|front-test-token',
                    'user' => [
                        'id' => 1,
                        'email' => 'admin@sicore.sn',
                        'nom' => 'SICORE',
                        'prenom' => 'Administrateur',
                        'role' => ['id' => 1, 'libelle' => 'Administrateur'],
                    ],
                ]);
            }

            if (str_contains($request->url(), '/api/payroll/pages/')) {
                return Http::response([
                    'data' => [
                        'notice' => 'Données API de test.',
                        'period' => [
                            'id' => 1,
                            'code' => '2026-07',
                            'label' => 'Juillet 2026',
                            'status' => 'open',
                            'status_label' => 'Ouverte',
                            'version' => 1,
                        ],
                        'periods' => [[
                            'id' => 1,
                            'code' => '2026-07',
                            'label' => 'Juillet 2026',
                            'status' => 'open',
                            'status_label' => 'Ouverte',
                            'version' => 1,
                        ]],
                        'academic_inspections' => [[
                            'id' => 1,
                            'value' => 1,
                            'label' => 'IA Test',
                        ]],
                        'education_inspections' => [[
                            'id' => 2,
                            'value' => 2,
                            'label' => 'IEF Test',
                            'ia_id' => 1,
                        ]],
                        'teachers' => [[
                            'id' => 3,
                            'value' => 3,
                            'label' => 'TEST-001 — Agent Test',
                            'matricule' => 'TEST-001',
                            'name' => 'Agent Test',
                            'ia_id' => 1,
                            'ief_id' => 2,
                        ]],
                        'supports_hierarchy_filter' => true,
                        'row_filters' => [[
                            'ia_id' => 1,
                            'ief_id' => 2,
                            'matricule' => 'TEST-001',
                        ]],
                        'stats' => [],
                        'filters' => [[
                            'name' => 'period_id',
                            'label' => 'Période',
                            'value' => 1,
                            'options' => [['value' => 1, 'label' => 'Juillet 2026']],
                        ]],
                        'actions' => [],
                        'columns' => ['État'],
                        'rows' => [['Connecté']],
                    ],
                ]);
            }

            if (str_contains($request->url(), '/api/payroll/payslips/')) {
                return Http::response([
                    'data' => [
                        'id' => 1,
                        'reference' => 'BS-202603-VAC-2026-001',
                        'period' => ['id' => 1, 'code' => '2026-03', 'label' => 'Mars 2026'],
                        'teacher' => [
                            'matricule' => 'VAC-2026-001',
                            'name' => 'Oumou DIOP',
                            'corps' => 'Vacataires',
                            'bank' => 'CBAO',
                            'account_last_four' => '0001',
                            'academic_inspection' => 'IA Saint-Louis',
                            'education_inspection' => 'IEF Saint-Louis Commune',
                            'establishment' => 'Centre de formation professionnelle de Saint-Louis',
                        ],
                        'profile' => [
                            'engagement_type' => 'vacataire',
                            'engagement_label' => 'Vacataire',
                            'diploma' => null,
                            'category' => null,
                        ],
                        'gross_amount' => '150000.00',
                        'deduction_amount' => '20900.00',
                        'employer_contribution_amount' => '0.00',
                        'net_amount' => '129100.00',
                        'payment_status' => 'pending',
                        'payment_reference' => null,
                        'edited_on' => '08/08/2026',
                        'lines' => [
                            ['code' => 'SALAIRE_BASE', 'label' => 'Salaire de base', 'category' => 'earning', 'amount' => '150000.00'],
                            ['code' => 'IMPR', 'label' => 'IMPR', 'category' => 'deduction', 'amount' => '10500.00'],
                            ['code' => 'TRIMF', 'label' => 'TRIMF', 'category' => 'deduction', 'amount' => '400.00'],
                            ['code' => 'TABASKI_RETENUE', 'label' => 'Retenue Tabaski', 'category' => 'deduction', 'amount' => '10000.00'],
                        ],
                    ],
                ]);
            }

            return Http::response(['message' => 'OK']);
        });
    }

    /** Vérifie que le provider injecte un client API unique et conforme au contrat. */
    public function test_contrat_api_est_lie_au_client_sicore_en_singleton(): void
    {
        $first = app(SicoreApiClientInterface::class);
        $second = app(SicoreApiClientInterface::class);

        $this->assertInstanceOf(SicoreApi::class, $first);
        $this->assertSame($first, $second);
    }

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
        $this->assertSame('1|front-test-token', session('sicore_token'));
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->post('/login', [
            'email' => 'admin@sicore.sn',
            'password' => 'incorrect',
        ])->assertSessionHasErrors('email');
    }

    public function test_sidebar_pages_are_rendered_by_laravel(): void
    {
        $session = [
            'sicore_user' => [
                'name' => 'Administrateur SICORE',
                'email' => 'admin@sicore.sn',
                'role' => 'Administrateur',
            ],
            'sicore_token' => '1|front-test-token',
            'sicore_token_expires_at' => now()->addHour()->timestamp,
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

    public function test_configuration_paie_commence_par_ia_ief_et_matricule_saisi(): void
    {
        $fields = config('payroll-forms.configure-teacher-payroll.fields');

        $this->assertSame(
            ['ia_id', 'ief_id', 'matricule'],
            array_column(array_slice($fields, 0, 3), 'name')
        );
        $this->assertSame('matricule', $fields[2]['type']);
        $this->assertTrue($fields[2]['required']);
        $this->assertTrue($fields[2]['full_width']);
        $this->assertSame(['contractuel'], $fields[5]['show_for'] ?? null);
        $this->assertSame(['contractuel'], $fields[6]['show_for'] ?? null);
    }

    public function test_formulaires_tabaski_sont_collectifs_sans_matricule_ni_categorie(): void
    {
        foreach (['apply-tabaski-advance', 'apply-tabaski-deduction'] as $action) {
            $fields = config('payroll-forms.'.$action.'.fields');
            $names = array_column($fields, 'name');

            $this->assertSame([
                'type_engagement',
                'ia_id',
                'ief_id',
                'academic_year',
                'payroll_period_id',
                'amount',
            ], $names);
            $this->assertNotContains('matricule', $names);
            $this->assertNotContains('category', $names);
            $this->assertSame('Corps d’enseignement', $fields[0]['label']);
            $this->assertSame('Mois d’application', $fields[4]['label']);
        }
    }

    public function test_pages_paie_proposent_la_recherche_hierarchique_sans_rechargement(): void
    {
        $this->withSession($this->sicoreSession())
            ->get('/paie/etats-presence')
            ->assertOk()
            ->assertSee('Recherche administrative instantanée')
            ->assertSee('Inspection académique (IA)')
            ->assertSee('Inspection de l’Éducation et de la Formation (IEF)')
            ->assertSee('data-payroll-live-matricule', false)
            ->assertSee('data-payroll-row', false)
            ->assertSee('data-table-pagination', false)
            ->assertSee('data-page-action="first"', false)
            ->assertSee('data-page-action="last"', false)
            ->assertSee('data-page-size', false);
    }

    public function test_lien_bulletins_reste_marque_et_le_script_conserve_sa_position(): void
    {
        $response = $this->withSession($this->sicoreSession())
            ->get('/paie/bulletins')
            ->assertOk();

        $this->assertMatchesRegularExpression(
            '/class="active"\s+href="[^"]*\/paie\/bulletins"/s',
            $response->getContent()
        );
        $this->assertStringContainsString(
            'sicore_sidebar_scroll',
            file_get_contents(public_path('assets/js/app.js'))
        );
    }

    public function test_bulletin_moderne_contient_entete_officiel_totaux_et_pied_mefpt(): void
    {
        $this->withSession($this->sicoreSession())
            ->get('/paie/bulletins/1')
            ->assertOk()
            ->assertSee('République du Sénégal')
            ->assertSee('Direction des Ressources Humaines')
            ->assertSee('Bulletin de solde')
            ->assertSee('Net à percevoir')
            ->assertSee('129 100')
            ->assertSee('SICORE - MEFPT')
            ->assertSee('Édité le 08/08/2026');
    }

    /** @return array<string, mixed> */
    private function sicoreSession(): array
    {
        return [
            'sicore_user' => [
                'name' => 'Administrateur SICORE',
                'email' => 'admin@sicore.sn',
                'role' => 'Administrateur',
            ],
            'sicore_token' => '1|front-test-token',
            'sicore_token_expires_at' => now()->addHour()->timestamp,
        ];
    }
}
