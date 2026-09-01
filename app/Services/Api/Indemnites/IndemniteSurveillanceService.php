<?php

namespace App\Services\Api\Indemnites;

use App\Services\Api\ApiClient;

class IndemniteSurveillanceService
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

    public function surveillantsEligibles(int|string $convocationId, int|string|null $centreId = null): array
    {
        return $this->wrap($this->api->get('indemnite-surveillance/surveillants-eligibles', array_filter([
            'convocation_id' => $convocationId,
            'centre_id' => $centreId,
        ])));
    }

    public function calculerGroupe(int|string $convocationId, array $lignes): array
    {
        return $this->wrap($this->api->post('indemnite-surveillance/calculer-groupe', [
            'convocation_id' => $convocationId,
            'lignes' => $lignes,
        ]));
    }
}
