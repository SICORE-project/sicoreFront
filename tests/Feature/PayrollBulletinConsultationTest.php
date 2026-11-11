<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayrollBulletinConsultationTest extends TestCase
{
    private array $criteria = ['period_id' => 1, 'academic_year_id' => 2, 'statut_enseignant_id' => 3, 'ia_id' => 4, 'ief_id' => 5];

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->withSession(['access_token' => 'test', 'sicore_user' => ['role_slug' => 'super_admin', 'permissions' => ['paie.bulletins.read']]]);
    }

    private function fakeApi(array $records): void
    {
        Http::fake([
            '*/payroll/pages/paie-etat-salaires*' => Http::response(['data' => [
                'period' => ['id' => 1], 'salary_statement' => ['rows' => $records, 'filter_options' => []],
            ]]),
            '*/payroll/pages/paie-bulletins*' => Http::response(['data' => ['rows' => [
                ['BS-1', '', 'PC-1', '', '', '', '', '', ['actions' => [['code' => 'view-payslip', 'payload' => ['payroll_payslip_id' => 10]]]]],
                ['BS-2', '', 'PC-2', '', '', '', '', '', ['actions' => [['code' => 'view-payslip', 'payload' => ['payroll_payslip_id' => 20]]]]],
            ]]]),
            '*/payroll/payslips/*' => Http::response(['data' => $this->slip()]),
        ]);
    }

    private function slip(): array
    {
        return ['reference' => 'BS-TEST', 'period' => ['id' => 1, 'label' => 'Octobre 2026', 'year' => 2026],
            'teacher' => ['name' => 'Mame Dieye', 'matricule' => 'PC-1'], 'lines' => [],
            'gross_amount' => 100, 'deduction_amount' => 10, 'net_amount' => 90, 'edited_on' => '11/11/2026'];
    }

    public function test_entry_displays_only_filters_and_no_generation_or_table(): void
    {
        $this->fakeApi([]);
        $this->get('/paie/bulletins')->assertOk()->assertSee('Édition bulletin avec le groupe')
            ->assertDontSee('data-payroll-action', false)->assertDontSee('<table', false);
        Http::assertSentCount(1);
    }

    public function test_suggestions_use_salary_statement_filters_and_only_existing_slips(): void
    {
        $this->fakeApi([['reference' => 'BS-1', 'matricule' => 'PC-1', 'first_name' => 'Mame', 'last_name' => 'Dieye']]);
        $this->getJson('/paie/bulletins?'.http_build_query($this->criteria + ['suggest' => 1, 'matricule' => 'PC']))
            ->assertOk()->assertJsonPath('teachers.0.matricule', 'PC-1');
        Http::assertSent(fn ($r) => $r['ia_id'] == 4 && $r['ief_id'] == 5 && $r['matricule'] === 'PC');
        Http::assertSentCount(1);
    }

    public function test_individual_consultation_requires_an_exact_matricule(): void
    {
        $this->fakeApi([['reference' => 'BS-1', 'matricule' => 'PC-1'], ['reference' => 'BS-2', 'matricule' => 'PC-2']]);
        $this->get('/paie/bulletins?'.http_build_query($this->criteria + ['consult' => 1, 'matricule' => 'PC-1']))
            ->assertOk()->assertViewHas('bulletins', fn ($items) => count($items) === 1)->assertSee('BULLETIN DE SOLDE');
        Http::assertNotSent(fn ($r) => str_ends_with($r->url(), '/payslips/20'));
    }

    public function test_group_opens_all_matching_slips_without_writing(): void
    {
        $this->fakeApi([['reference' => 'BS-1', 'matricule' => 'PC-1'], ['reference' => 'BS-2', 'matricule' => 'PC-2']]);
        $this->get('/paie/bulletins?'.http_build_query($this->criteria + ['consult' => 1, 'group' => 1]))
            ->assertOk()->assertViewHas('bulletins', fn ($items) => count($items) === 2);
        Http::assertSentCount(4);
        Http::assertNotSent(fn ($r) => $r->method() !== 'GET');
    }

    public function test_no_monthly_statement_means_no_bulletin(): void
    {
        $this->fakeApi([]);
        $this->get('/paie/bulletins?'.http_build_query($this->criteria + ['consult' => 1, 'group' => 1]))
            ->assertRedirect()->assertSessionHasErrors('period_id');
        Http::assertSentCount(1);
    }

    public function test_no_matricule_requires_explicit_group_selection(): void
    {
        $this->fakeApi([]);
        $this->get('/paie/bulletins?'.http_build_query($this->criteria + ['consult' => 1]))
            ->assertRedirect()->assertSessionHasErrors('matricule');
    }

    public function test_partial_matricule_does_not_open_another_teacher(): void
    {
        $this->fakeApi([['reference' => 'BS-1', 'matricule' => 'PC-1']]);
        $this->get('/paie/bulletins?'.http_build_query($this->criteria + ['consult' => 1, 'matricule' => 'PC']))
            ->assertRedirect()->assertSessionHasErrors('period_id');
        Http::assertSentCount(1);
    }

    public function test_group_and_matricule_cannot_be_combined(): void
    {
        $this->fakeApi([]);
        $this->get('/paie/bulletins?'.http_build_query($this->criteria + ['consult' => 1, 'group' => 1, 'matricule' => 'PC-1']))
            ->assertRedirect()->assertSessionHasErrors('matricule');
    }

    public function test_failed_salary_statement_never_displays_partial_group(): void
    {
        Http::fake(['*/payroll/pages/paie-etat-salaires*' => Http::response(['message' => 'Forbidden'], 403)]);
        $this->get('/paie/bulletins?'.http_build_query($this->criteria + ['consult' => 1, 'group' => 1]))
            ->assertForbidden()->assertDontSee('solde-document', false);
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/payroll/payslips/'));
    }

    public function test_session_scope_overrides_browser_ia_for_suggestions(): void
    {
        $this->withSession(['sicore_user.drh.perimetre' => ['type' => 'ia_id', 'id' => 4]]);
        $this->fakeApi([]);
        $this->getJson('/paie/bulletins?'.http_build_query(array_replace($this->criteria, ['ia_id' => 99, 'suggest' => 1, 'matricule' => 'PC'])))
            ->assertOk();
        Http::assertSent(fn ($r) => $r['ia_id'] == 4);
    }
}
