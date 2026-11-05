<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyndicatFormTest extends TestCase
{
    public function test_creation_and_update_forward_four_fields_without_status(): void
    {
        $this->withoutMiddleware();
        Http::preventStrayRequests();
        Http::fake(['*/syndicats*' => Http::response(['message' => 'Enregistrement réussi.'], 201)]);
        $fields = [
            'code' => ' synd-01 ',
            'libelle' => ' Syndicat test ',
            'montant_check_off' => '1500.00',
            'montant_oeuvre_sociale' => '1000.00',
        ];

        $this->post(route('parametres.syndicats.store'), $fields)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('parametres.syndicats.index'));
        $this->put(route('parametres.syndicats.update', 1), $fields)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('parametres.syndicats.index'));

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->data() === array_replace($fields, ['code' => 'SYND-01', 'libelle' => 'Syndicat test']));
        Http::assertSent(fn ($request) => $request->method() === 'PUT'
            && $request->data() === array_replace($fields, ['code' => 'SYND-01', 'libelle' => 'Syndicat test']));
    }
}
