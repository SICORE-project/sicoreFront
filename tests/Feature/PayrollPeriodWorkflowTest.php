<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayrollPeriodWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        $this->withSession(['access_token' => 'test', 'sicore_user' => ['role_slug' => 'super_admin', 'permissions' => ['parametrage.periodes_paie.read', 'parametrage.periodes_paie.manage']]]);
    }

    public function test_period_page_displays_cycle_closure_and_existing_reference_opening(): void
    {
        Http::fake(['*/periodes-paie*' => Http::response(['data' => ['data' => [['id' => 1, 'code' => '11', 'libelle' => 'Novembre']], 'total' => 1],
            'payroll_periods' => [['id' => 2,'code' => '2026-10','label' => 'Octobre 2026','status' => 'calculated']]])]);
        $this->get(route('parametres.periodes-paie.index'))->assertOk()->assertSee('Clôturer la période')->assertSee('Ouvrir et calculer')->assertSee('Créer et calculer la paie');
    }

    public function test_close_uses_only_the_period_lifecycle_endpoint(): void
    {
        Http::fake(['*/periodes-paie/cycles/2/close' => Http::response(['message' => 'Période clôturée.'])]);
        $this->post(route('parametres.periodes-paie.close', 2))->assertRedirect()->assertSessionHas('success');
        Http::assertSentCount(1);
        Http::assertSent(fn ($r) => $r->method() === 'POST' && str_ends_with($r->url(), '/periodes-paie/cycles/2/close'));
    }

    public function test_blocked_opening_displays_backend_reason_without_losing_existing_period(): void
    {
        Http::fake(['*/periodes-paie/1/open' => Http::response(['message' => 'Validation', 'errors' => ['code' => ['Clôturez d’abord Octobre.']]],422)]);
        $this->post(route('parametres.periodes-paie.open',1))->assertRedirect()->assertSessionHas('error','Clôturez d’abord Octobre.');
    }
}
