<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IaReferenceViewsTest extends TestCase
{
    public function test_ia_and_ief_use_existing_parameter_views_with_scoped_data(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*/ia/referentiels*' => Http::response(['data' => [
            'ia' => ['id' => 1, 'code' => 'IA-DKR', 'libelle' => 'IA de Dakar', 'region_id' => 1, 'region' => ['id' => 1, 'nom' => 'Dakar']],
            'iefs' => [['id' => 2, 'code' => 'IEF-DKR', 'libelle' => 'IEF Dakar', 'ia_id' => 1]],
        ]])]);
        $this->withSession(['access_token' => 'test', 'sicore_user' => [
            'role_slug' => 'gestionnaire_ia', 'permissions' => ['enseignants.read'], 'ia_id' => 1,
            'ia' => ['id' => 1, 'libelle' => 'Dakar'],
        ]]);
        $this->get('/ia/structure')->assertOk()->assertViewIs('pages.parametres.ia-index')
            ->assertSee('IA-DKR')->assertSee('Dakar')->assertDontSee('+ Nouvelle IA')->assertDontSee('id="ia-edit-modal"', false);
        $this->get('/ia/iefs')->assertOk()->assertViewIs('pages.parametres.ief')
            ->assertSee('IEF-DKR')->assertSee('IA de Dakar')->assertDontSee('name="ia_id"', false)->assertDontSee('+ Nouvelle IEF');
        $this->get('/ia/iefs?search=absent')->assertOk()->assertViewHas('items', []);
        $this->get('/ia/iefs?ia_id=2')->assertForbidden();
        Http::assertSentCount(3);
    }

    public function test_administrator_retains_existing_management_forms(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['data' => []])]);
        $this->withSession(['access_token' => 'test', 'sicore_user' => ['role_slug' => 'super_admin']]);
        $this->get(route('parametres.ia.index'))->assertOk()->assertSee('+ Nouvelle IA')->assertSee('ia-create-modal');
        $this->get(route('parametres.ief.index'))->assertOk()->assertSee('+ Nouvelle IEF')->assertSee('ief-create-modal');
    }
}
