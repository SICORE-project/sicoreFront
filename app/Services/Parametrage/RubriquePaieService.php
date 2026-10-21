<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class RubriquePaieService
{
    public function __construct(private readonly ApiClient $apiClient) {}

    public function paginate(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));

        try {
            $response = $this->apiClient->get('rubriques-paie', array_filter([
                'search' => $filters['search'] ?? null,
                'type' => $filters['type'] ?? null,
                'periodicite' => $filters['periodicite'] ?? null,
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
            return $this->emptyResult($page, $response->json('message', 'Impossible de charger les rubriques de paie.'));
        }

        $payload = (array) $response->json();
        $items = data_get($payload, 'data.data', []);

        return [
            'items' => is_array($items) ? $items : [],
            'error' => null,
            'unauthorized' => false,
            'pagination' => [
                'current_page' => (int) data_get($payload, 'data.current_page', $page),
                'last_page' => max(1, (int) data_get($payload, 'data.last_page', 1)),
                'total' => (int) data_get($payload, 'data.total', 0),
            ],
            'statistics' => [
                'total' => (int) data_get($payload, 'statistics.total', 0),
                'gains' => (int) data_get($payload, 'statistics.gains', 0),
                'retenues' => (int) data_get($payload, 'statistics.retenues', 0),
            ],
        ];
    }

    public function create(array $data): array
    {
        try {
            return $this->result($this->apiClient->post('rubriques-paie', $data), 'Rubrique de paie ajoutée.');
        } catch (ConnectionException) {
            return $this->connectionError();
        }
    }

    public function update(int|string $id, array $data): array
    {
        try {
            return $this->result($this->apiClient->put("rubriques-paie/{$id}", $data), 'Rubrique de paie modifiée.');
        } catch (ConnectionException) {
            return $this->connectionError();
        }
    }

    public function delete(int|string $id): array
    {
        try {
            return $this->result($this->apiClient->delete("rubriques-paie/{$id}"), 'Rubrique de paie supprimée.');
        } catch (ConnectionException) {
            return $this->connectionError();
        }
    }

    private function result($response, string $fallback): array
    {
        return [
            'success' => $response->successful(),
            'unauthorized' => $response->unauthorized(),
            'message' => $response->json('message', $response->successful() ? $fallback : 'L’opération a échoué.'),
            'errors' => (array) $response->json('errors', []),
            'data' => $response->json('data'),
        ];
    }

    private function emptyResult(int $page, string $message): array
    {
        return [
            'items' => [],
            'error' => $message,
            'unauthorized' => false,
            'pagination' => ['current_page' => $page, 'last_page' => 1, 'total' => 0],
            'statistics' => ['total' => 0, 'gains' => 0, 'retenues' => 0],
        ];
    }

    private function connectionError(): array
    {
        return [
            'success' => false,
            'unauthorized' => false,
            'message' => 'Le service backend est momentanément indisponible.',
            'errors' => [],
            'data' => null,
        ];
    }
}
