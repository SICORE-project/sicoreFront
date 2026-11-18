<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RubriquePaieStatisticsTest extends TestCase
{
    public function test_page_displays_six_statistic_cards(): void
    {
        Http::fake([
            '*/rubriques-paie/statuts*' => Http::response(['data' => ['data' => []]]),
            '*/statuts-enseignant*' => Http::response(['data' => ['data' => []]]),
            '*/rubriques-paie*' => Http::response([
                'data' => ['data' => [], 'current_page' => 1, 'last_page' => 1, 'total' => 0],
                'statistics' => ['total' => 9, 'gains' => 6, 'retenues' => 3, 'contractuels' => 4, 'vacataires' => 2, 'mensuelles' => 7],
            ]),
        ]);

        $response = $this->withSession(['access_token' => 'token', 'sicore_user' => ['name' => 'Admin', 'role_slug' => 'super_admin', 'permissions' => ['paie.rubriques.read']]])
            ->get('/parametrage/parametres/rubriques-paie')->assertOk()
            ->assertSeeInOrder(['Total', '9', 'Gains', '6', 'Retenues', '3',
                'Rubriques contractuels', '4', 'Rubriques vacataires', '2', 'Rubriques mensuelles', '7']);

        // 6 cartes dans une grille de 3 colonnes : 2 lignes complètes.
        $this->assertSame(6, substr_count($response->getContent(), '<article class="stat-card">'));
    }
}
