<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayrollPayslipPresentationTest extends TestCase
{
    public function test_le_bulletin_affiche_clairement_le_mois_et_regroupe_le_dossier_du_beneficiaire(): void
    {
        Http::fake([
            '*/payroll/payslips/42' => Http::response([
                'data' => [
                    'id' => 42,
                    'reference' => 'BS-TEST-202609-002',
                    'period' => [
                        'id' => 9,
                        'code' => '2026-09',
                        'label' => 'Septembre 2026',
                        'month_label' => 'Septembre 2026',
                        'month_number' => 9,
                        'year' => 2026,
                    ],
                    'teacher' => [
                        'matricule' => 'PC-TEST-001',
                        'name' => 'Moussa Diop',
                        'corps' => 'Professeurs contractuels',
                        'bank' => null,
                        'account_last_four' => null,
                        'academic_inspection' => 'IA Dakar',
                        'education_inspection' => 'IEF Dakar Almadies',
                        'establishment' => null,
                    ],
                    'profile' => [
                        'engagement_label' => 'Professeur contractuel',
                        'diploma' => 'BAC / BT',
                        'category' => 1,
                    ],
                    'gross_amount' => '302773.00',
                    'deduction_amount' => '42103.00',
                    'employer_contribution_amount' => '21504.00',
                    'net_amount' => '260670.00',
                    'payment_status' => 'paid',
                    'payment_reference' => 'VIR-TEST-202609-002',
                    'paid_at' => '2026-09-30T00:00:00+00:00',
                    'edited_on' => '24/08/2026',
                    'lines' => [[
                        'code' => 'SALAIRE_BASE',
                        'label' => 'Salaire de base',
                        'category' => 'earning',
                        'amount' => '152773.00',
                        'source' => 'salary_scale',
                    ]],
                ],
            ]),
        ]);

        $this->withSession([
            'sicore_user' => ['name' => 'Gestionnaire paie', 'role' => 'Gestionnaire'],
            'access_token' => 'test-token',
        ])->get('/paie/bulletins/42')
            ->assertOk()
            ->assertSee('Bulletin de salaire — Septembre 2026')
            ->assertSee('Paie du mois de Septembre 2026')
            ->assertSee('Mois de paie : SEPTEMBRE 2026')
            ->assertSee('Agent bénéficiaire')
            ->assertSee('Moussa Diop')
            ->assertSee('Professeur contractuel')
            ->assertSee('Banque / compte')
            ->assertSee('Banque non renseignée')
            ->assertSee('IA Dakar')
            ->assertSee('IEF Dakar Almadies')
            ->assertSee('BAC / BT')
            ->assertSee('1re catégorie');
    }

    public function test_la_feuille_d_impression_repete_l_entete_et_numerote_les_pages(): void
    {
        $css = file_get_contents(public_path('assets/css/app.css'));

        $this->assertIsString($css);
        $this->assertStringContainsString('counter(page)', $css);
        $this->assertStringContainsString('counter(pages)', $css);
        $this->assertStringContainsString('display: table-header-group', $css);
        $this->assertStringContainsString('page-break-inside: avoid', $css);
        $this->assertStringContainsString('@media screen and (max-width: 760px)', $css);
        $this->assertStringContainsString(
            'grid-template-columns: minmax(145px, 0.9fr) minmax(230px, 1.2fr) minmax(150px, 0.85fr)',
            $css
        );
        $this->assertStringContainsString('grid-template-columns: 1.05fr 0.8fr 1.15fr auto', $css);
    }
}
