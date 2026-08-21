<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class LieuServiceService
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    public function getAll(int $page = 1, int $perPage = 10): array
    {
        try {
            $response = $this->apiClient->get('parametrage/lieux-service', [
                'page' => $page,
                'per_page' => $perPage,
            ]);
        } catch (ConnectionException) {
            return $this->emptyResult($page, $perPage, 'Le service backend est momentanément inaccessible.');
        }

        if ($response->unauthorized()) {
            $result = $this->emptyResult($page, $perPage, 'Votre session backend a expiré. Veuillez vous reconnecter.');
            $result['unauthorized'] = true;

            return $result;
        }

        if (! $response->successful()) {
            return $this->emptyResult($page, $perPage, $response->json('message', 'Impossible de charger les lieux de service.'));
        }

        $payload = $response->json();
        $items = data_get($payload, 'data.data', data_get($payload, 'data', data_get($payload, 'lieux_service', data_get($payload, 'lieux', []))));
        $items = is_array($items) ? $items : [];

        return [
            'items' => $items,
            'error' => null,
            'unauthorized' => false,
            'pagination' => [
                'current_page' => (int) data_get($payload, 'data.current_page', data_get($payload, 'meta.current_page', $page)),
                'last_page' => max(1, (int) data_get($payload, 'data.last_page', data_get($payload, 'meta.last_page', 1))),
                'total' => (int) data_get($payload, 'data.total', data_get($payload, 'meta.total', count($items))),
                'per_page' => $perPage,
            ],
        ];
    }

    private function emptyResult(int $page, int $perPage, string $message): array
    {
        return [
            'items' => [],
            'error' => $message,
            'unauthorized' => false,
            'pagination' => ['current_page' => $page, 'last_page' => 1, 'total' => 0, 'per_page' => $perPage],
        ];
    }
}
