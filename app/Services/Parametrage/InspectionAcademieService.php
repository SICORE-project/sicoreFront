<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class InspectionAcademieService
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    public function getAll(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $response = $this->apiClient->get('ias', array_filter(array_merge($filters, [
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
            return $this->emptyResult($page, $perPage, $response->json('message', 'Impossible de charger les inspections d’académie.'));
        }

        $payload = $response->json();
        $items = data_get($payload, 'data.data', data_get($payload, 'data', data_get($payload, 'ia', data_get($payload, 'inspections', []))));
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

    public function regions(): array
    {
        try {
            $response = $this->apiClient->get('ias/regions');
        } catch (ConnectionException) {
            return [];
        }

        return $response->successful() ? $response->json('data', []) : [];
    }

    public function create(array $data): array
    {
        return $this->saveResponse(fn () => $this->apiClient->post('ias', $data));
    }

    public function update(int|string $id, array $data): array
    {
        return $this->saveResponse(fn () => $this->apiClient->put("ias/{$id}", $data));
    }

    public function delete(int|string $id): array
    {
        return $this->saveResponse(fn () => $this->apiClient->delete("ias/{$id}"));
    }

    private function saveResponse(callable $request): array
    {
        try {
            $response = $request();
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        return [
            'success' => $response->successful(),
            'message' => $response->json('message', $response->successful() ? 'Opération réussie.' : 'Enregistrement impossible.'),
            'errors' => $response->json('errors', []),
        ];
    }

    public function getIefs(string|int $academyId): array
    {
        try {
            $response = $this->apiClient->get("ias/{$academyId}/iefs");
        } catch (ConnectionException) {
            return [
                'items' => [],
                'error' => 'Le service backend est momentanément inaccessible.',
                'unauthorized' => false,
            ];
        }

        if ($response->unauthorized()) {
            return [
                'items' => [],
                'error' => 'Votre session backend a expiré. Veuillez vous reconnecter.',
                'unauthorized' => true,
            ];
        }

        if (! $response->successful()) {
            return [
                'items' => [],
                'error' => $response->json('message', 'Impossible de charger les IEF rattachées.'),
                'unauthorized' => false,
            ];
        }

        $payload = $response->json();
        $items = data_get($payload, 'data.data', data_get($payload, 'data', data_get($payload, 'iefs', [])));

        return [
            'items' => is_array($items) ? $items : [],
            'error' => null,
            'unauthorized' => false,
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
