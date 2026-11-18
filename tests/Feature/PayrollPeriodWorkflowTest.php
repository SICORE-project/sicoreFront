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

    public function test_main_catalogue_design_only_exposes_code_label_and_standard_actions(): void
    {
        Http::fake(['*/periodes-paie*' => Http::response(['data' => ['data' => [['id' => 1, 'code' => 'NOV', 'libelle' => 'Novembre']], 'total' => 1], 'stats' => ['total' => 1, 'ouvertes' => 1, 'cloturees' => 0, 'derniere' => '2026-11']])]);
        $this->get(route('parametres.periodes-paie.index'))->assertOk()
            ->assertSee('Ajouter une période')->assertSee('Novembre')->assertSee('name="code"', false)->assertSee('name="libelle"', false)
            ->assertDontSee('name="date_debut"', false)->assertDontSee('name="date_fin"', false)
            ->assertDontSee('Clôturer la période')->assertDontSee('Ouvrir et calculer')->assertDontSee('Cycles de paie');
    }

    public function test_creation_posts_only_code_and_label(): void
    {
        Http::fake(['*/periodes-paie' => Http::response(['message' => 'Période de paie ajoutée avec succès.'], 201)]);
        $this->post(route('parametres.periodes-paie.store'), ['code' => 'JAN', 'libelle' => 'JANVIER'])
            ->assertRedirect(route('parametres.periodes-paie.index'))->assertSessionHas('success');
        Http::assertSent(fn ($r) => $r->method() === 'POST' && $r['code'] === 'JAN' && $r['libelle'] === 'JANVIER' && ! isset($r['date_debut']));
        Http::assertSentCount(1);
    }

    public function test_processing_error_preserves_the_simple_form_and_entered_values(): void
    {
        Http::fake(['*/periodes-paie' => Http::response(['message' => 'Validation', 'errors' => ['code' => ['Diplôme manquant pour un enseignant.']]], 422)]);
        $this->post(route('parametres.periodes-paie.store'), ['code' => 'JAN', 'libelle' => 'JANVIER'])
            ->assertRedirect()->assertSessionHas('periode_create_form_open', true)->assertSessionHasErrors('code');
    }
}