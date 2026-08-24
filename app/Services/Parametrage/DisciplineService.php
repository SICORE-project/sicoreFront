<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class DisciplineService
{
    public function __construct(private readonly ApiClient $apiClient) {}

    public function paginate(array $filters): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));
        try {
            $response = $this->apiClient->get('parametrage/disciplines', array_filter([
                'search' => $filters['search'] ?? null,
                'statut' => $filters['statut'] ?? null,
                'sort' => $filters['sort'] ?? 'code',
                'direction' => $filters['direction'] ?? 'asc',
                'page' => $page,
                'per_page' => 10,
            ], fn ($value) => $value !== null && $value !== ''));
        } catch (ConnectionException) {
            return $this->empty($page, 'Le service backend est momentanément inaccessible.');
        }

        if ($response->unauthorized()) {
            return [...$this->empty($page, 'Votre session backend a expiré. Veuillez vous reconnecter.'), 'unauthorized' => true];
        }
        if (! $response->successful()) {
            return $this->empty($page, $response->json('message', 'Impossible de charger les disciplines.'));
        }

        $payload = $response->json();
        $items = data_get($payload, 'data.data', data_get($payload, 'data', data_get($payload, 'disciplines', [])));
        $items = is_array($items) ? $items : [];

        return [
            'items' => $items,
            'error' => null,
            'unauthorized' => false,
            'pagination' => [
                'current_page' => (int) data_get($payload, 'data.current_page', data_get($payload, 'meta.current_page', $page)),
                'last_page' => max(1, (int) data_get($payload, 'data.last_page', data_get($payload, 'meta.last_page', 1))),
                'total' => (int) data_get($payload, 'data.total', data_get($payload, 'meta.total', count($items))),
            ],
        ];
    }

    public function create(array $data): array
    {
        try {
            $response = $this->apiClient->post('parametrage/disciplines', $data);
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        return [
            'success' => $response->successful(),
            'unauthorized' => $response->unauthorized(),
            'message' => $response->json('message', $response->successful() ? 'Discipline ajoutée.' : 'Impossible d’ajouter la discipline.'),
            'errors' => (array) $response->json('errors', []),
            'data' => $response->json('data'),
            'audit' => $response->json('audit'),
        ];
    }

    public function update(string|int $id, array $data): array
    {
        try {
            $response = $this->apiClient->put("parametrage/disciplines/{$id}", $data);
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => [], 'data' => null, 'audit' => null];
        }

        return [
            'success' => $response->successful(),
            'unauthorized' => $response->unauthorized(),
            'message' => $response->json('message', $response->successful() ? 'Discipline modifiée.' : 'Impossible de modifier la discipline.'),
            'errors' => (array) $response->json('errors', []),
            'data' => $response->json('data'),
            'audit' => $response->json('audit'),
        ];
    }

    public function updateStatus(string|int $id, string $status): array
    {
        try {
            $response = $this->apiClient->patch("parametrage/disciplines/{$id}/statut", ['statut' => $status]);
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'data' => null, 'audit' => null];
        }

        return [
            'success' => $response->successful(),
            'message' => $response->json('message', $response->successful() ? 'Statut de la discipline mis à jour.' : 'Impossible de modifier le statut de la discipline.'),
            'data' => $response->json('data'),
            'audit' => $response->json('audit'),
        ];
    }

    public function getActiveForSelection(): array
    {
        try {
            $response = $this->apiClient->get('parametrage/disciplines', [
                'statut' => 'actif',
                'sort' => 'libelle',
                'direction' => 'asc',
                'per_page' => 100,
            ]);
        } catch (ConnectionException) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $payload = $response->json();
        $items = data_get($payload, 'data.data', data_get($payload, 'data', data_get($payload, 'disciplines', [])));

        return is_array($items) ? $items : [];
    }

    private function empty(int $page, string $message): array
    {
        return ['items' => [], 'error' => $message, 'unauthorized' => false,
            'pagination' => ['current_page' => $page, 'last_page' => 1, 'total' => 0]];
    }
}
