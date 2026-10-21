<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class RegionService
{
    public function __construct(private readonly ApiClient $apiClient)
    {
    }

    /**
     * Récupérer la liste paginée des régions.
     */
    public function getAll(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        try {
            $response = $this->apiClient->get('regions', array_filter(array_merge($filters, [
                'page' => $page,
                'per_page' => $perPage,
            ]), static fn ($value) => $value !== null && $value !== ''));
        } catch (ConnectionException) {
            return $this->emptyResult(
                $page,
                $perPage,
                'Le service backend est momentanément inaccessible.'
            );
        }

        if ($response->unauthorized()) {
            $result = $this->emptyResult(
                $page,
                $perPage,
                'Votre session backend a expiré. Veuillez vous reconnecter.'
            );

            $result['unauthorized'] = true;

            return $result;
        }

        if (! $response->successful()) {
            return $this->emptyResult(
                $page,
                $perPage,
                $response->json('message', 'Impossible de charger les régions.')
            );
        }

        $payload = $response->json();

        $items = data_get(
            $payload,
            'data.data',
            data_get($payload, 'data', [])
        );

        $items = is_array($items) ? $items : [];

        return [
            'items' => $items,
            'error' => null,
            'unauthorized' => false,

            'pagination' => [
                'current_page' => (int) data_get(
                    $payload,
                    'data.current_page',
                    data_get($payload, 'meta.current_page', $page)
                ),

                'last_page' => max(
                    1,
                    (int) data_get(
                        $payload,
                        'data.last_page',
                        data_get($payload, 'meta.last_page', 1)
                    )
                ),

                'total' => (int) data_get(
                    $payload,
                    'data.total',
                    data_get($payload, 'meta.total', count($items))
                ),

                'per_page' => (int) data_get(
                    $payload,
                    'data.per_page',
                    data_get($payload, 'meta.per_page', $perPage)
                ),
            ],
        ];
    }

    /**
     * Récupérer une région.
     */
    public function find(int|string $id): array
    {
        try {
            $response = $this->apiClient->get("regions/{$id}");
        } catch (ConnectionException) {
            return [
                'success' => false,
                'message' => 'Le service backend est momentanément inaccessible.',
                'data' => null,
            ];
        }

        return [
            'success' => $response->successful(),
            'message' => $response->json(
                'message',
                $response->successful()
                    ? 'Région chargée avec succès.'
                    : 'Impossible de charger la région.'
            ),
            'data' => $response->json('data'),
        ];
    }

    /**
     * Créer une région.
     */
    public function create(array $data): array
    {
        return $this->saveResponse(
            fn () => $this->apiClient->post('regions', $data)
        );
    }

    /**
     * Modifier une région.
     */
    public function update(int|string $id, array $data): array
    {
        return $this->saveResponse(
            fn () => $this->apiClient->put("regions/{$id}", $data)
        );
    }

    /**
     * Activer/désactiver une région.
     */
    public function changeStatus(int|string $id, bool $estActif): array
    {
        return $this->saveResponse(
            fn () => $this->apiClient->patch(
                "regions/{$id}/statut",
                ['est_actif' => $estActif]
            )
        );
    }

    /**
     * Supprimer une région.
     */
    public function delete(int|string $id): array
    {
        return $this->saveResponse(
            fn () => $this->apiClient->delete("regions/{$id}")
        );
    }

    private function saveResponse(callable $request): array
    {
        try {
            $response = $request();
        } catch (ConnectionException) {
            return [
                'success' => false,
                'message' => 'Le service backend est momentanément inaccessible.',
                'errors' => [],
                'data' => null,
            ];
        }

        return [
            'success' => $response->successful(),

            'message' => $response->json(
                'message',
                $response->successful()
                    ? 'Opération réussie.'
                    : 'Enregistrement impossible.'
            ),

            'errors' => $response->json('errors', []),

            'data' => $response->json('data'),
        ];
    }

    private function emptyResult(int $page, int $perPage, string $message): array
    {
        return [
            'items' => [],
            'error' => $message,
            'unauthorized' => false,

            'pagination' => [
                'current_page' => $page,
                'last_page' => 1,
                'total' => 0,
                'per_page' => $perPage,
            ],
        ];
    }
}