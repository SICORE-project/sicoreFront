<?php

namespace App\Services\Api\Indemnites;

use App\Services\Api\ApiClient;

class IndemniteCorrectionService
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
     * Membres "Correction" d'une convocation — voir
     * IndemniteCorrectionController::correcteursEligibles() côté back.
     * $centreId restreint à un seul centre (utilisé par le calcul groupé).
     */
    public function correcteursEligibles(int|string $convocationId, int|string|null $centreId = null): array
    {
        return $this->wrap($this->api->get('indemnite-correction/correcteurs-eligibles', array_filter([
            'convocation_id' => $convocationId,
            'centre_id' => $centreId,
        ])));
    }

    /**
     * $lignes : tableau de ['enseignant_id', 'convocation_centre_id',
     * 'metier', 'nombre_copies', 'taux_copie'].
     */
    public function calculerGroupe(int|string $convocationId, array $lignes): array
    {
        return $this->wrap($this->api->post('indemnite-correction/calculer-groupe', [
            'convocation_id' => $convocationId,
            'lignes' => $lignes,
        ]));
    }
}
