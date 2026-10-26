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

    /**
     * Surveillants éligibles de plusieurs convocations en un seul appel : [convocation_id => données].
     *
     * @param  array<int, int|string>  $convocationIds
     */
    public function surveillantsEligiblesLot(array $convocationIds): array
    {
        return $this->wrap($this->api->get('indemnite-surveillance/surveillants-eligibles-lot', [
            'convocation_ids' => array_values($convocationIds),
        ]));
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
