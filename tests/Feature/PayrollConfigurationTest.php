<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayrollConfigurationTest extends TestCase
{
    public function test_menu_etats_de_presence_contient_exactement_les_neuf_entrees_attendues(): void
    {
        $payrollGroup = collect(config('navigation'))->firstWhere('label', 'Gestion de la paie');
        $group = collect($payrollGroup['links'] ?? [])->firstWhere('label', 'États de présence');

        $this->assertNotNull($payrollGroup);
        $this->assertSame('group', $payrollGroup['type']);
        $this->assertNotNull($group);
        $this->assertSame('group', $group['type']);
        $this->assertSame([
            'Avance Tabaski',
            'Retenue Tabaski',
            'Retenue sur rappel',
            'Saisir le nombre de jours travaillés',
            'Exempter un enseignant d’une avance ou retenue',
            'Clôture de la paie',
            'Sommes perçues',
            'Génération du montant des heures supplémentaires',
            'Génération individuelle de la paie',
        ], array_column($group['links'], 'label'));

        $this->assertContains('Travaux périodiques', array_column($payrollGroup['links'], 'label'));
        $this->assertContains('Délégation de crédit', array_column($payrollGroup['links'], 'label'));
    }

    public function test_formulaires_tabaski_utilisent_les_referentiels_et_le_montant_par_defaut(): void
    {
        $forms = config('payroll-forms');
        $advance = collect($forms['apply-tabaski-advance']['fields'])->keyBy('name');
        $deduction = collect($forms['apply-tabaski-deduction']['fields'])->keyBy('name');

        $this->assertSame('teaching_corps', $advance['corps_id']['type']);
        $this->assertSame('academic_inspections', $advance['ia_ids']['source']);
        $this->assertSame('academic_year', $advance['annee_academique_id']['type']);
        $this->assertSame('payroll_month', $advance['month']['type']);
        $this->assertSame(100000, $advance['amount']['default']);

        $this->assertSame('payroll_months', $deduction['months']['source']);
        $this->assertSame(10, $deduction['months']['exact']);
        $this->assertSame(100000, $deduction['amount']['default']);
    }

    public function test_page_avance_tabaski_transmet_les_referentiels_du_backend_au_formulaire(): void
    {
        Http::fake([
            '*/payroll/pages/paie-avance-tabaski*' => Http::response([
                'data' => [
                    'stats' => [],
                    'filters' => [],
                    'actions' => [['code' => 'apply-tabaski-advance', 'label' => 'Appliquer', 'style' => 'primary']],
                    'columns' => [],
                    'rows' => [],
                    'teaching_corps' => [
                        ['id' => 7, 'value' => 7, 'code' => 'VAC', 'label' => 'VAC — Vacataires'],
                        ['id' => 8, 'value' => 8, 'code' => 'PC', 'label' => 'PC — Professeurs contractuels'],
                    ],
                    'academic_inspections' => [
                        ['id' => 3, 'value' => 3, 'code' => 'IA-DKR', 'label' => 'IA Dakar'],
                    ],
                    'academic_years' => [
                        ['id' => 2, 'value' => 2, 'label' => '2025-2026'],
                    ],
                    'payroll_months' => [
                        ['value' => 7, 'label' => 'Juillet'],
                    ],
                ],
            ]),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire paie', 'role' => 'Gestionnaire'],
            'access_token' => 'test-token',
        ])->get('/paie/avance-tabaski')
            ->assertOk()
            ->assertSee('apply-tabaski-advance', false)
            ->assertSee('teaching_corps', false)
            ->assertSee('VAC', false)
            ->assertSee('IA Dakar', false)
            ->assertSee('2025-2026', false);

        Http::assertSent(fn ($request): bool =>
            $request->url() === 'http://127.0.0.1:8000/api/payroll/pages/paie-avance-tabaski'
            && $request->hasHeader('Authorization', 'Bearer test-token')
        );
    }
}
