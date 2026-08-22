<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class LieuServiceService
{
    public function __construct(private readonly ApiClient $apiClient) {}

    public function getAll(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $response = $this->apiClient->get('parametrage/lieux-service', array_filter([
                'page' => $page,
                'per_page' => $perPage,
                'search' => $filters['search'] ?? null,
                'ia_id' => $filters['ia_id'] ?? null,
                'ief_id' => $filters['ief_id'] ?? null,
                'statut' => $filters['statut'] ?? null,
                'sort' => $filters['sort'] ?? null,
                'direction' => $filters['direction'] ?? null,
            ], static fn ($value): bool => $value !== null && $value !== ''));
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
        $data = data_get($payload, 'data', data_get($payload, 'lieux_service', data_get($payload, 'lieux', [])));
        $paginatedData = is_array($data) && isset($data['data']) && is_array($data['data']) ? $data : null;
        $items = $paginatedData['data'] ?? $data;
        $items = is_array($items) ? $items : [];

        return [
            'items' => $items,
            'error' => null,
            'unauthorized' => false,
            'pagination' => [
                'current_page' => (int) ($paginatedData['current_page'] ?? data_get($payload, 'meta.current_page', $page)),
                'last_page' => max(1, (int) ($paginatedData['last_page'] ?? data_get($payload, 'meta.last_page', 1))),
                'total' => (int) ($paginatedData['total'] ?? data_get($payload, 'meta.total', count($items))),
                'per_page' => (int) ($paginatedData['per_page'] ?? data_get($payload, 'meta.per_page', $perPage)),
            ],
        ];
    }

    public function create(array $data): array
    {
        try {
            $response = $this->apiClient->post('parametrage/lieux-service', $data);
        } catch (ConnectionException) {
            return ['success' => false, 'unauthorized' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        if ($response->successful()) {
            return ['success' => true, 'message' => $response->json('message', 'Lieu de service créé avec succès.'), 'data' => $response->json('data')];
        }

        return [
            'success' => false,
            'unauthorized' => $response->unauthorized(),
            'message' => $response->json('message', 'Impossible de créer le lieu de service.'),
            'errors' => $response->json('errors', []),
        ];
    }

    public function update(string|int $id, array $data): array
    {
        try {
            $response = $this->apiClient->put("parametrage/lieux-service/{$id}", $data);
        } catch (ConnectionException) {
            return ['success' => false, 'unauthorized' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        if ($response->successful()) {
            return ['success' => true, 'message' => $response->json('message', 'Lieu de service modifié avec succès.'), 'data' => $response->json('data')];
        }

        return [
            'success' => false,
            'unauthorized' => $response->unauthorized(),
            'message' => $response->json('message', 'Impossible de modifier le lieu de service.'),
            'errors' => $response->json('errors', []),
        ];
    }

    public function updateStatus(string|int $id, bool $active): array
    {
        try {
            $response = $this->apiClient->patch("parametrage/lieux-service/{$id}/statut", ['actif' => $active]);
        } catch (ConnectionException) {
            return ['success' => false, 'unauthorized' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => $response->json('message', $active ? 'Lieu de service activé.' : 'Lieu de service désactivé.'),
            ];
        }

        return [
            'success' => false,
            'unauthorized' => $response->unauthorized(),
            'message' => $response->json('message', 'Impossible de modifier le statut du lieu de service.'),
            'errors' => $response->json('errors', []),
        ];
    }

    public function assignTeacher(string|int $locationId, string|int $teacherId, array $data): array
    {
        try {
            $response = $this->apiClient->post("enseignants/{$teacherId}/affectations", [
                'lieu_service_id' => $locationId,
                'date_debut' => $data['date_debut'],
                'actif' => true,
            ]);
        } catch (ConnectionException) {
            return ['success' => false, 'unauthorized' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => $response->json('message', 'Enseignant affecté au lieu de service avec succès.'),
                'data' => $response->json('data'),
            ];
        }

        return [
            'success' => false,
            'unauthorized' => $response->unauthorized(),
            'message' => $response->json('message', 'Impossible d’affecter cet enseignant au lieu de service.'),
            'errors' => $response->json('errors', []),
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
