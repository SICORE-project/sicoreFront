<?php

namespace App\Services\Administration;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class UserService
{
    public function __construct(
        protected ApiClient $apiClient
    ) {
    }

    /**
     * Récupérer la liste des rôles depuis le backend.
     */
    public function getRoles(): array
    {
        try {
            $response = $this->apiClient->get('admin/roles');
        } catch (ConnectionException) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $data = $response->json();

        // L'API peut retourner une collection paginée (data.data) ou une
        // liste simple (data). Accepter les deux évite un select vide, qui
        // bloque ensuite la soumission car role_id est obligatoire.
        return data_get($data, 'data.data', data_get($data, 'data', []));
    }

    /**
     * Récupérer la liste des utilisateurs depuis le backend.
     */
    public function getUsers(int $page = 1, int $perPage = 10): array
    {
        try {
            $response = $this->apiClient->get('admin/users', [
                'page' => $page,
                'per_page' => $perPage,
            ]);
        } catch (ConnectionException) {
            return [
                'items' => [],
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => 1,
                    'total' => 0,
                    'per_page' => $perPage,
                ],
            ];
        }

        if (! $response->successful()) {
            return [
                'items' => [],
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => 1,
                    'total' => 0,
                    'per_page' => $perPage,
                ],
            ];
        }

        $data = $response->json();
        $items = data_get($data, 'data.data', data_get($data, 'data', data_get($data, 'users', [])));

        return [
            'items' => is_array($items) ? $items : [],
            'pagination' => [
                'current_page' => (int) data_get($data, 'meta.current_page', $page),
                'last_page' => (int) data_get($data, 'meta.last_page', max(1, (int) ceil((count($items) ?: 1) / max(1, $perPage)))),
                'total' => (int) data_get($data, 'meta.total', count($items)),
                'per_page' => $perPage,
            ],
        ];
    }

    /**
     * Créer un utilisateur via l'API backend.
     */
    public function createUser(array $data): array
    {
        try {
            $response = $this->apiClient->post('admin/users', $data);
        } catch (ConnectionException) {
            return [
                'success' => false,
                'message' => 'Le service backend est momentanément inaccessible. Réessayez dans quelques instants.',
                'errors' => [],
            ];
        }

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => $response->json(
                    'message',
                    'Utilisateur créé avec succès.'
                ),
                'data' => $response->json('data'),
            ];
        }

        return [
            'success' => false,
            'message' => $response->json(
                'message',
                'Impossible de créer l’utilisateur.'
            ),
            'errors' => $response->json('errors', []),
        ];
    }
}
