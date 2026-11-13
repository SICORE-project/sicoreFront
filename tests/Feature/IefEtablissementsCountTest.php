<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IefEtablissementsCountTest extends TestCase
{
    public function test_ief_page_shows_establishments_per_ief(): void
    {
        Http::fake([
            '*/iefs*' => Http::response([
                'data' => [
                    ['id' => 1, 'code' => 'IEF1', 'libelle' => 'IEF de Pikine', 'ia' => ['code' => 'IA1', 'libelle' => 'IA de Dakar'], 'etablissements_count' => 7],
                    ['id' => 2, 'code' => 'IEF2', 'libelle' => 'IEF de Guédiawaye', 'ia' => ['code' => 'IA1', 'libelle' => 'IA de Dakar'], 'etablissements_count' => 0],
                ],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 2],
                'stats' => ['etablissements' => 7, 'iefs_sans_etablissement' => 1],
            ]),
            '*' => Http::response(['data' => []]),
        ]);

        $response = $this->withSession(['access_token' => 'token', 'sicore_user' => ['name' => 'Admin', 'role_slug' => 'super_admin', 'permissions' => ['parametrage.ief.read']]])
            ->get('/parametrage/parametres/ief')->assertOk()
            ->assertSeeInOrder(['Total IEF', 'IA disponibles', 'Établissements', '7', 'IEF sans établissement', '1'])
            ->assertSee('<th>Établissements</th>', false)
            ->assertSeeInOrder(['IEF de Pikine', 'IA de Dakar', '7', 'IEF de Guédiawaye', 'IA de Dakar', '0']);

        // 4 cartes : une ligne complète.
        $this->assertSame(4, substr_count($response->getContent(), '<article class="stat-card">'));
    }
}
