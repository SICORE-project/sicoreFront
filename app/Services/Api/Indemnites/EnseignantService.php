<?php

namespace App\Services\Api\Indemnites;

use App\Services\Api\ApiClient;

class EnseignantService
{
    public function __construct(
        protected ApiClient $api
    ) {}

    protected function wrap($response): array
    {
        if (! $response->successful()) {
            return [
                'success' => false,
                'status' => $response->status(),
                'message' => $response->json('message') ?? 'Le service SICORE Backend est injoignable.',
                'errors' => $response->json('errors'),
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'status' => $response->status(),
            'message' => $response->json('message'),
            'data' => $response->json('data'),
        ];
    }

    /**
     * Coordonnées bancaires de plusieurs enseignants : [id => code_banque, code_guichet,
     * numero_compte_bancaire, cle_rib, complet].
     */
    public function coordonneesBancaires(array $ids): array
    {
        return $this->wrap($this->api->get('enseignants/coordonnees-bancaires', ['ids' => array_values($ids)]));
    }

    public function trouver(int|string $id): array
    {
        return $this->wrap($this->api->get("enseignants/{$id}"));
    }
}
