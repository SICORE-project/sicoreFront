<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class CorpsService
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    public function getAll(array $filters = []): array
    {
        try {
            $response = $this->apiClient->get('corps', $filters);
        } catch (ConnectionException) {
            return $this->emptyResult('Le service backend est momentanément inaccessible.');
        }

        if ($response->unauthorized()) {
            $result = $this->emptyResult('Votre session backend a expiré. Veuillez vous reconnecter.');
            $result['unauthorized'] = true;
            return $result;
        }

        if (! $response->successful()) {
            return $this->emptyResult($response->json('message', 'Impossible de charger les corps enseignants.'));
        }

        return [
            'items' => $response->json('data.data', []),
            'pagination' => [
                'current_page' => (int) $response->json('data.current_page', 1),
                'last_page' => max(1, (int) $response->json('data.last_page', 1)),
                'total' => (int) $response->json('data.total', 0),
            ],
            'error' => null,
            'unauthorized' => false,
        ];
    }

    public function create(array $data): array { return $this->save(fn () => $this->apiClient->post('corps', $data)); }
    public function update(int $id, array $data): array { return $this->save(fn () => $this->apiClient->put("corps/{$id}", $data)); }
    public function delete(int $id): array { return $this->save(fn () => $this->apiClient->delete("corps/{$id}")); }

    private function save(callable $request): array
    {
        try {
            $response = $request();
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        return ['success' => $response->successful(), 'message' => $response->json('message', 'Opération impossible.'), 'errors' => $response->json('errors', [])];
    }

    private function emptyResult(string $message): array
    {
        return ['items' => [], 'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0], 'error' => $message, 'unauthorized' => false];
    }
}
