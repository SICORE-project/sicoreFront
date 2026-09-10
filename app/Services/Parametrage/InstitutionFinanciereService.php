<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class InstitutionFinanciereService
{
    public function __construct(protected ApiClient $apiClient)
    {
    }

    public function getAll(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $response = $this->apiClient->get('parametrage/institutions-financieres', array_filter(array_merge($filters, [
                'page' => $page,
                'per_page' => $perPage,
            ]), static fn ($value) => $value !== null && $value !== ''));
        } catch (ConnectionException) {
            return $this->emptyResult($page, $perPage, 'Le service backend est momentanément inaccessible.');
        }

        if ($response->unauthorized()) {
            $result = $this->emptyResult($page, $perPage, 'Votre session backend a expiré. Veuillez vous reconnecter.');
            $result['unauthorized'] = true;

            return $result;
        }

        if (! $response->successful()) {
            return $this->emptyResult($page, $perPage, $response->json('message', 'Impossible de charger les banques.'));
        }

        $payload = $response->json();
        $items = data_get($payload, 'data.data', data_get($payload, 'data', data_get($payload, 'institutions', [])));
        $items = is_array($items) ? $items : [];

        return [
            'items' => $items,
            'error' => null,
            'unauthorized' => false,
            'pagination' => [
                'current_page' => (int) data_get($payload, 'data.current_page', data_get($payload, 'meta.current_page', $page)),
                'last_page' => max(1, (int) data_get($payload, 'data.last_page', data_get($payload, 'meta.last_page', 1))),
                'total' => (int) data_get($payload, 'data.total', data_get($payload, 'meta.total', count($items))),
                'per_page' => (int) data_get($payload, 'data.per_page', data_get($payload, 'meta.per_page', $perPage)),
            ],
        ];
    }

    public function create(array $data): array
    {
        try {
            $response = $this->apiClient->post('parametrage/institutions-financieres', $data);
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => $response->json('message', 'Banque créée avec succès.'),
                'data' => $response->json('data'),
            ];
        }

        return [
            'success' => false,
            'unauthorized' => $response->unauthorized(),
            'message' => $response->json('message', 'Impossible de créer la banque.'),
            'errors' => $response->json('errors', []),
        ];
    }
    public function update(string|int $id, array $data): array
    {
        try {
            $response = $this->apiClient->put("parametrage/institutions-financieres/{$id}", $data);
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => $response->json('message', 'Banque modifiée avec succès.'),
                'data' => $response->json('data'),
            ];
        }

        return [
            'success' => false,
            'unauthorized' => $response->unauthorized(),
            'message' => $response->json('message', 'Impossible de modifier la banque.'),
            'errors' => $response->json('errors', []),
        ];
    }
    public function updateStatus(string|int $id, bool $isActive): array
    {
        try {
            $response = $this->apiClient->patch("parametrage/institutions-financieres/{$id}/statut", [
                'est_actif' => $isActive,
            ]);
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => $response->json('message', $isActive ? 'Banque activée.' : 'Banque désactivée.'),
            ];
        }

        return [
            'success' => false,
            'unauthorized' => $response->unauthorized(),
            'message' => $response->json('message', 'Impossible de modifier le statut de la banque.'),
            'errors' => $response->json('errors', []),
        ];
    }

    public function delete(string|int $id): array
    {
        try {
            $response = $this->apiClient->delete("parametrage/institutions-financieres/{$id}");
        } catch (ConnectionException) {
            return ['success' => false, 'unauthorized' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        return [
            'success' => $response->successful(),
            'unauthorized' => $response->unauthorized(),
            'message' => $response->json('message', $response->successful()
                ? 'Banque supprimée avec succès.'
                : 'Impossible de supprimer la banque.'),
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
