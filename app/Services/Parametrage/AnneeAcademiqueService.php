<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class AnneeAcademiqueService
{
    public function __construct(private readonly ApiClient $apiClient) {}

    public function getAll(?string $search = null): array
    {
        try {
            $response = $this->apiClient->get('annees-academiques', array_filter([
                'search' => $search,
            ], fn ($value) => $value !== null && $value !== ''));
        } catch (ConnectionException) {
            return ['items' => [], 'error' => 'Le service backend est momentanément indisponible.', 'unauthorized' => false];
        }

        if ($response->unauthorized()) {
            return ['items' => [], 'error' => 'Votre session backend a expiré.', 'unauthorized' => true];
        }

        if (! $response->successful()) {
            return ['items' => [], 'error' => $response->json('message', 'Impossible de charger les années académiques.'), 'unauthorized' => false];
        }

        return [
            'items' => (array) $response->json('data', []),
            'error' => null,
            'unauthorized' => false,
        ];
    }

    public function create(array $data): array
    {
        try {
            return $this->result($this->apiClient->post('annees-academiques', $data), 'Année académique créée.');
        } catch (ConnectionException) {
            return $this->connectionError();
        }
    }

    public function update(int|string $id, array $data): array
    {
        try {
            return $this->result($this->apiClient->put("annees-academiques/{$id}", $data), 'Année académique modifiée.');
        } catch (ConnectionException) {
            return $this->connectionError();
        }
    }

    public function activate(int|string $id): array
    {
        return $this->patchAction($id, 'activate', 'Année académique activée.');
    }

    public function deactivate(int|string $id): array
    {
        return $this->patchAction($id, 'deactivate', 'Année académique désactivée.');
    }

    public function close(int|string $id): array
    {
        return $this->patchAction($id, 'close', 'Année académique clôturée.');
    }

    public function delete(int|string $id): array
    {
        try {
            return $this->result($this->apiClient->delete("annees-academiques/{$id}"), 'Année académique supprimée.');
        } catch (ConnectionException) {
            return $this->connectionError();
        }
    }

    private function patchAction(int|string $id, string $action, string $fallback): array
    {
        try {
            return $this->result($this->apiClient->patch("annees-academiques/{$id}/{$action}"), $fallback);
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
