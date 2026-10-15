<?php
namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IaPayrollReportsTest extends TestCase
{
    public function test_all_requested_modules_are_visible_and_use_their_own_report(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*/ia/dashboard*' => Http::response(['data' => ['indicateurs' => ['bulletins_payes' => 4, 'sommes_percues' => 900]]]),
            '*/ia/payroll/pages/*' => Http::response(['data' => [
                'stats' => [['label' => 'Masse brute', 'value' => '900 FCFA', 'note' => 'Total des gains', 'color' => 'green', 'icon' => 'BR']],
                'columns' => ['Rubrique', 'Montant'], 'rows' => [['Rapport IA', '900 FCFA']], 'filters' => [], 'actions' => [],
            ]])]);
        $this->withSession(['access_token' => 'test', 'sicore_user' => ['role_slug' => 'gestionnaire_ia',
            'ia_id' => 1, 'ia' => ['id' => 1, 'libelle' => 'Dakar'],
            'permissions' => ['paie.bulletins.read', 'paie.sommes_percues.read', 'paie.etat_salaires.read', 'paie.cotisations.read', 'paie.effectifs_ief.read', 'paie.recap_banque.read']]]);
        $this->get('/dashboard')->assertOk()->assertSee('Sommes perçues')->assertSee('État des salaires')
            ->assertSee('Cotisations sociales')->assertSee('Paie générée par IEF')->assertSee('Récapitulatif par banque')->assertDontSee('Bulletins payés');
        foreach (['sommes-percues' => 'paid', 'etat-salaires' => 'salaries', 'cotisations-sociales' => 'contributions', 'generee-ief' => 'workforce', 'recap-banque' => 'banks'] as $route => $view) {
            $this->get('/paie/'.$route)->assertOk()->assertViewIs('pages.paie.'.$route)->assertSee('data-payroll-module="paie-'.$route.'"', false)->assertSee('Rapport IA')->assertSee('900 FCFA')->assertSee('Masse brute');
            Http::assertSent(fn ($r) => str_contains($r->url(), '/ia/payroll/pages/paie-'.$route) && $r['ia_id'] == 1);
        }
    }
    public function test_salary_statement_preserves_full_parameters_and_export_filters(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*/ia/payroll/pages/paie-etat-salaires/export*' => Http::response('Etat;Salaire', 200),
            '*/ia/payroll/pages/paie-etat-salaires*' => Http::response(['data' => [
                'scope_ia_id' => 1, 'scope_label' => 'IA de Dakar', 'stats' => [], 'columns' => [], 'rows' => [], 'filters' => [],
                'actions' => [['code' => 'export', 'label' => 'Exporter CSV']],
                'salary_statement' => ['title' => 'État des salaires', 'rows' => [], 'columns' => [], 'with_signature' => true],
            ]]),
        ]);
        $this->withSession(['access_token' => 'test', 'sicore_user' => ['role_slug' => 'gestionnaire_ia',
            'ia_id' => 1, 'permissions' => ['paie.etat_salaires.read', 'paie.bulletins.export']]]);
        $this->get('/paie/etat-salaires?period_id=4&corps_id=3&with_signature=1')->assertOk()
            ->assertSee('Préparer l’état mensuel des salaires')->assertSee('salary-statement-workspace')
            ->assertSee('name="corps_id"', false)->assertSee('name="with_signature"', false)
            ->assertSee('IA de Dakar')->assertDontSee('name="ia_id"', false)->assertSee('Exporter CSV');
        $this->get('/ia/paie/export?slug=paie-etat-salaires&period_id=4&corps_id=3&with_signature=1')
            ->assertOk()->assertSee('Etat;Salaire');
        Http::assertSent(fn ($r) => str_contains($r->url(), '/export') && $r['corps_id'] == 3 && $r['with_signature'] == 1 && $r['ia_id'] == 1);
    }
    public function test_additional_periodic_cards_open_the_original_report_views(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*/ia/payroll/pages/*' => Http::response(['data' => [
            'stats' => [], 'columns' => ['Rapport'], 'rows' => [['Rapport IA']], 'filters' => [], 'actions' => [],
        ]])]);
        $this->withSession(['access_token' => 'test', 'sicore_user' => ['role_slug' => 'gestionnaire_ia',
            'ia_id' => 1, 'permissions' => ['paie.bulletins.read']]]);
        foreach (['montants-engages-banque', 'edition-salaires-banque', 'elements-saisie-dashboard', 'recap-elements-corps', 'cumul-enseignants-ief', 'effectifs-corps', 'non-generee', 'edition-enseignants', 'edition-fonctionnaires', 'mutuelles-sante', 'situation-affectations', 'prime-scolaire', 'reliquats', 'double-flux', 'directeurs-interim', 'heures-supplementaires-interim'] as $slug) {
            $this->get('/paie/'.$slug.'?period_id=4')->assertOk()->assertViewIs('pages.paie.'.$slug)->assertSee('Rapport IA');
            Http::assertSent(fn ($r) => str_contains($r->url(), '/ia/payroll/pages/paie-'.$slug) && $r['period_id'] == 4 && $r['ia_id'] == 1);
        }
        $this->getJson('/paie/fermeture-periode')->assertForbidden();
    }
}
