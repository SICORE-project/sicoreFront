<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayrollTabaskiIntegrationTest extends TestCase
{
    public function test_les_pages_tabaski_affichent_les_formateurs_sans_filtre_de_periode(): void
    {
        foreach (['avance-tabaski', 'retenue-tabaski'] as $page) {
            Http::fake(['*/payroll/pages/paie-'.$page.'*' => Http::response([
                'data' => [
                    'period' => null,
                    'periods' => [],
                    'notice' => 'Données issues des référentiels enregistrés.',
                    'stats' => [[
                        'label' => 'Formateurs actifs',
                        'value' => 1,
                        'note' => 'Données réelles',
                        'icon' => 'EN',
                        'color' => 'green',
                    ]],
                    'filters' => [],
                    'actions' => [[
                        'code' => $page === 'avance-tabaski'
                            ? 'apply-tabaski-advance'
                            : 'apply-tabaski-deduction',
                        'label' => 'Appliquer collectivement',
                        'style' => 'primary',
                    ]],
                    'columns' => ['Enseignant', 'Matricule'],
                    'rows' => [['Awa Ndiaye', 'TEST-001']],
                    'row_filters' => [[
                        'ia_id' => 1,
                        'ief_id' => 2,
                        'matricule' => 'TEST-001',
                    ]],
                    'supports_hierarchy_filter' => true,
                    'academic_inspections' => [['id' => 1, 'label' => 'IA Dakar']],
                    'education_inspections' => [['id' => 2, 'ia_id' => 1, 'label' => 'IEF Dakar']],
                    'teachers' => [['id' => 3, 'ia_id' => 1, 'ief_id' => 2, 'matricule' => 'TEST-001', 'name' => 'Awa Ndiaye']],
                ],
            ])]);

            $this->withSession([
                'sicore_user' => ['name' => 'Gestionnaire paie', 'role' => 'Gestionnaire Paie'],
                'access_token' => 'test-token',
            ])->get('/paie/'.$page)
                ->assertOk()
                ->assertSee('Awa Ndiaye')
                ->assertSee('Appliquer collectivement')
                ->assertSee('data-payroll-live-ia', false)
                ->assertSee('data-payroll-live-ief', false)
                ->assertSee('data-payroll-live-matricule', false)
                ->assertDontSee('class="filter-panel" method="GET"', false);
        }
    }

    public function test_une_api_indisponible_ne_montre_pas_les_anciens_chiffres_de_demo(): void
    {
        Http::fake(['*/payroll/pages/paie-avance-tabaski*' => Http::response([
            'message' => 'Service indisponible.',
        ], 503)]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire paie', 'role' => 'Gestionnaire Paie'],
            'access_token' => 'test-token',
        ])->get('/paie/avance-tabaski')
            ->assertOk()
            ->assertDontSee('Demandes eligibles')
            ->assertDontSee('Ajouter une avance');
    }

    public function test_la_retenue_sur_rappel_est_expliquee_sans_la_confondre_avec_tabaski(): void
    {
        Http::fake(['*/payroll/pages/paie-retenues-rappel*' => Http::response([
            'message' => 'Service indisponible.',
        ], 503)]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire paie', 'role' => 'Gestionnaire Paie'],
            'access_token' => 'test-token',
        ])->get('/paie/retenues-rappel')
            ->assertOk()
            ->assertSee('Que signifie « retenue sur rappel » ?')
            ->assertSee('distincte de la retenue Tabaski de 10 000 FCFA par mois')
            ->assertDontSee('Rappels traites');
    }
}
