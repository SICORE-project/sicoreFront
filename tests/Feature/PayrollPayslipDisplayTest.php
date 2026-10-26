<?php

namespace Tests\Feature;

use Tests\TestCase;

class PayrollPayslipDisplayTest extends TestCase
{
    public function test_generation_uses_one_form_for_whole_ia_and_individual_without_salary_resubmission(): void
    {
        $definition = config('payroll-forms.generate-payslips');
        $fields = collect($definition['fields'])->keyBy('name');
        $this->assertEquals(['ia', 'teacher'], array_column($fields['generation_scope']['options'], 'value'));
        foreach (['enseignant_id', 'ia_id', 'ief_id', 'statut_enseignant_id', 'annee_academique_id', 'month'] as $field) {
            $this->assertTrue($fields->has($field));
        }
        foreach (['payroll_diploma_level', 'payroll_category_level', 'impr_monthly_amount', 'trimf_monthly_amount'] as $field) {
            $this->assertFalse($fields->has($field));
        }
    }

    public function test_profile_form_does_not_request_deductions_owned_by_the_rubric_crud(): void
    {
        $definition = config('payroll-forms.configure-teacher-payroll');
        $fields = array_column($definition['fields'], 'name');
        foreach (['impr_monthly_amount', 'trimf_monthly_amount', 'ipm_monthly_amount', 'union_checkoff_monthly_amount'] as $field) {
            $this->assertNotContains($field, $fields);
        }
        $this->assertContains('payroll_diploma_level', $fields);
        $this->assertStringContainsString('Rubriques de paie', $definition['confirmation']);
    }

    public function test_bulletin_renders_api_labels_escaped_and_explains_hidden_lines(): void
    {
        $data = $this->data();
        $data['lines'] = [[
            'code' => 'PRIME_TEST', 'label' => 'Prime <script>alert(1)</script>',
            'amount' => 12000, 'category' => 'earning',
        ]];
        $data['display_settings'] = [
            'origin' => 'calculation_snapshot', 'matched_rubric_count' => 2, 'hidden_line_count' => 1,
        ];
        $this->view('pages.paie.payslip', compact('data'))
            ->assertSee('Prime &lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Réglages conservés à la date du calcul.')
            ->assertSee('leurs montants restent inclus dans les totaux.')
            ->assertSee('NET A PERCEVOIR')
            ->assertSee('BULLETIN DE SOLDE')
            ->assertSee('VALIDITE : MOIS DE OCTOBRE')
            ->assertSee('Origine')->assertSee('Echéances')
            ->assertSee('N° Emission')->assertSee('Imputation')
            ->assertSee('Talon du bulletin');
    }

    public function test_bulletin_handles_all_lines_hidden_and_old_api_without_display_metadata(): void
    {
        $data = $this->data();
        $this->view('pages.paie.payslip', compact('data'))
            ->assertSee('Aucune rubrique à afficher pour ce bulletin.')
            ->assertSee('NET A PERCEVOIR');
    }

    private function data(): array
    {
        return [
            'reference' => 'BS-TEST',
            'period' => ['id' => 1, 'label' => 'Octobre 2026'],
            'teacher' => [
                'matricule' => 'TEST-001', 'name' => 'Agent Test', 'bank' => null,
                'account_last_four' => null, 'academic_inspection' => null,
                'education_inspection' => null, 'establishment' => null, 'corps' => null,
            ],
            'lines' => [], 'gross_amount' => 12000, 'deduction_amount' => 0,
            'employer_contribution_amount' => 0, 'net_amount' => 12000,
            'payment_reference' => null, 'payment_status' => 'pending', 'edited_on' => '25/10/2026',
        ];
    }
}
