<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class EnseignantService
{
    private const ENDPOINT = 'admin/personnel/enseignants';

    public function __construct(private readonly ApiClient $apiClient) {}

    public function getAll(array $filters = []): array
    {
        try {
            $response = $this->apiClient->get(self::ENDPOINT, $filters);
        } catch (ConnectionException) {
            return $this->emptyResult('Le service backend est momentanément inaccessible.');
        }

        if ($response->unauthorized()) {
            $result = $this->emptyResult('Votre session backend a expiré. Veuillez vous reconnecter.');
            $result['unauthorized'] = true;
            return $result;
        }

        if (! $response->successful()) {
            return $this->emptyResult($response->json('message', 'Impossible de charger les enseignants.'));
        }

        return [
            'items' => $response->json('data.data', $response->json('data', [])),
            'pagination' => [
                'current_page' => (int) $response->json('meta.current_page', $response->json('data.current_page', 1)),
                'last_page' => max(1, (int) $response->json('meta.last_page', $response->json('data.last_page', 1))),
                'total' => (int) $response->json('meta.total', $response->json('data.total', 0)),
            ],
            'error' => null,
            'unauthorized' => false,
        ];
    }

    public function find(int $id): array
    {
        try {
            $response = $this->apiClient->get(self::ENDPOINT . '/' . $id);
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'data' => null];
        }

        return ['success' => $response->successful(), 'message' => $response->json('message', 'Impossible de charger l’enseignant.'), 'data' => $response->json('data')];
    }

    public function create(array $data): array { return $this->save(fn () => $this->apiClient->post(self::ENDPOINT, $data)); }
    public function update(int $id, array $data): array { return $this->save(fn () => $this->apiClient->put(self::ENDPOINT . '/' . $id, $data)); }
    public function delete(int $id): array { return $this->save(fn () => $this->apiClient->delete(self::ENDPOINT . '/' . $id)); }

    private function save(callable $request): array
    {
        try {
            $response = $request();
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        return ['success' => $response->successful(), 'message' => $response->json('message', 'Opération impossible.'), 'errors' => (array) $response->json('errors', [])];
    }

    private function emptyResult(string $message): array
    {
        return ['items' => [], 'pagination' => ['current_page' => 1, 'last_page' => 1, 'total' => 0], 'error' => $message, 'unauthorized' => false];
    }
}
