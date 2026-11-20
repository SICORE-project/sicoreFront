<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IefEtablissementsCountTest extends TestCase
{
    public function test_ief_page_shows_the_number_of_establishments(): void
    {
        Http::fake([
            '*/iefs*' => Http::response([
                'data' => [
                    ['id' => 1, 'code' => 'IEF1', 'libelle' => 'IEF de Pikine', 'ia' => ['code' => 'IA1', 'libelle' => 'IA de Dakar']],
                ],
                'meta' => ['current_page' => 1, 'last_page' => 1, 'total' => 1],
                'stats' => ['etablissements' => 7],
            ]),
            '*' => Http::response(['data' => []]),
        ]);

        $response = $this->withSession(['access_token' => 'token', 'sicore_user' => ['name' => 'Admin', 'role_slug' => 'super_admin', 'permissions' => ['parametrage.ief.read']]])
            ->get('/parametrage/parametres/ief')->assertOk()
            ->assertSeeInOrder(['Total IEF', 'IA disponibles', 'Établissements', '7'])
            ->assertDontSee('<th>Établissements</th>', false);

        // 3 cartes sur 3 colonnes : une ligne complète.
        $this->assertSame(3, substr_count($response->getContent(), '<article class="stat-card">'));
        $this->assertStringContainsString('stats-grid three', $response->getContent());
    }
}
