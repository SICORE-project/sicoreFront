<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class PeriodePaieService
{
    public function __construct(private readonly ApiClient $apiClient) {}

    public function paginate(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));

        try {
            $response = $this->apiClient->get('periodes-paie', array_filter([
                'search' => $filters['search'] ?? null,
                'page' => $page,
                'per_page' => 10,
            ], fn ($value) => $value !== null && $value !== ''));
        } catch (ConnectionException) {
            return $this->emptyResult($page, 'Le service backend est momentanément indisponible.');
        }

        if ($response->unauthorized()) {
            return [...$this->emptyResult($page, 'Votre session backend a expiré.'), 'unauthorized' => true];
        }

        if (! $response->successful()) {
            return $this->emptyResult($page, $response->json('message', 'Impossible de charger les périodes de paie.'));
        }

        $payload = (array) $response->json();

        return [
            'items' => (array) data_get($payload, 'data.data', []),
            'error' => null,
            'unauthorized' => false,
            'pagination' => [
                'current_page' => (int) data_get($payload, 'data.current_page', $page),
                'last_page' => max(1, (int) data_get($payload, 'data.last_page', 1)),
                'total' => (int) data_get($payload, 'data.total', 0),
            ],
        ];
    }

    public function create(array $data): array
    {
        return $this->mutate('post', 'periodes-paie', $data, 'Période de paie ajoutée.');
    }

    public function update(int|string $id, array $data): array
    {
        return $this->mutate('put', "periodes-paie/{$id}", $data, 'Période de paie modifiée.');
    }

    public function delete(int|string $id): array
    {
        return $this->mutate('delete', "periodes-paie/{$id}", [], 'Période de paie supprimée.');
    }

    private function mutate(string $method, string $uri, array $data, string $fallback): array
    {
        try {
            $response = $method === 'delete'
                ? $this->apiClient->delete($uri)
                : $this->apiClient->{$method}($uri, $data);

            return [
                'success' => $response->successful(),
                'message' => $response->json('message', $response->successful() ? $fallback : 'L’opération a échoué.'),
                'errors' => (array) $response->json('errors', []),
            ];
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément indisponible.', 'errors' => []];
        }
    }

    private function emptyResult(int $page, string $message): array
    {
        return [
            'items' => [],
            'error' => $message,
            'unauthorized' => false,
            'pagination' => ['current_page' => $page, 'last_page' => 1, 'total' => 0],
        ];
    }
}
