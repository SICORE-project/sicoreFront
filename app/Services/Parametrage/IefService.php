<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class IefService
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    public function getAll(array $filters = []): array
    {
        try {
            $response = $this->apiClient->get('iefs', $filters);
        } catch (ConnectionException) {
            return $this->emptyResult('Le service backend est momentanément inaccessible.');
        }

        if ($response->unauthorized()) {
            $result = $this->emptyResult('Votre session backend a expiré. Veuillez vous reconnecter.');
            $result['unauthorized'] = true;

            return $result;
        }

        if (! $response->successful()) {
            return $this->emptyResult($response->json('message', 'Impossible de charger les IEF.'));
        }

        $payload = $response->json();

        return [
            'items' => data_get($payload, 'data', []),
            'pagination' => [
                'current_page' => (int) data_get($payload, 'meta.current_page', 1),
                'last_page' => max(1, (int) data_get($payload, 'meta.last_page', 1)),
                'total' => (int) data_get($payload, 'meta.total', 0),
            ],
            'error' => null,
            'unauthorized' => false,
        ];
    }

    public function create(array $data): array
    {
        return $this->save(fn () => $this->apiClient->post('iefs', $data));
    }

    public function update(int $id, array $data): array
    {
        return $this->save(fn () => $this->apiClient->put("iefs/{$id}", $data));
    }

    public function delete(int $id): array
    {
        return $this->save(fn () => $this->apiClient->delete("iefs/{$id}"));
    }

    private function save(callable $request): array
    {
        try {
            $response = $request();
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        return [
            'success' => $response->successful(),
            'message' => $response->json('message', $response->successful() ? 'Opération réussie.' : 'Opération impossible.'),
            'errors' => $response->json('errors', []),
        ];
    }

    private function emptyResult(string $message): array
    {
        return [
            'items' => [],
            'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0],
            'error' => $message,
            'unauthorized' => false,
        ];
    }
}
