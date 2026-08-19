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
            $response = $this->apiClient->get('admin/roles/all');
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
        return data_get($data, 'data', []);
    }

    /**
     * Récupérer la liste des utilisateurs depuis le backend.
     */
    public function getUsers(int $page = 1, int $perPage = 10, ?string $structureType = null): array
    {
        try {
            $response = $this->apiClient->get('admin/users/all');
        } catch (ConnectionException) {
            return [
                'items' => [],
                'error' => 'Le service backend est inaccessible. Vérifiez qu’il est démarré sur le port configuré.',
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
                'error' => $response->json('message', "Impossible de charger les utilisateurs (HTTP {$response->status()})."),
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => 1,
                    'total' => 0,
                    'per_page' => $perPage,
                ],
            ];
        }

        $data = $response->json();
        $allItems = data_get($data, 'data.data', data_get($data, 'data', data_get($data, 'users', [])));
        $allItems = is_array($allItems) ? array_values($allItems) : [];
        if ($structureType) {
            $allItems = array_values(array_filter($allItems, fn (array $user): bool => $this->organisationType($user) === $structureType));
        }
        $total = count($allItems);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $items = array_slice($allItems, ($page - 1) * $perPage, $perPage);

        return [
            'items' => is_array($items) ? $items : [],
            'error' => null,
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'total' => $total,
                'per_page' => $perPage,
            ],
        ];
    }

    /**
     * Créer un utilisateur via l'API backend.
     */
    public function getUser(int|string $userId): array
    {
        try { $response = $this->apiClient->get("admin/users/{$userId}"); }
        catch (ConnectionException) { return ['success' => false, 'message' => 'Le service backend est inaccessible.', 'data' => null]; }
        return ['success' => $response->successful(), 'message' => $response->json('message'), 'data' => $response->json('data')];
    }

    public function updateUser(int|string $userId, array $data): array
    {
        try { $response = $this->apiClient->put("admin/users/{$userId}", $data); }
        catch (ConnectionException) { return ['success' => false, 'message' => 'Le service backend est inaccessible.', 'errors' => []]; }
        return ['success' => $response->successful(), 'message' => $response->json('message'), 'errors' => $response->json('errors', [])];
    }

    private function organisationType(array $user): ?string
    {
        $access = data_get($user, 'acces_organisationnel', []);
        $type = strtolower((string) data_get($access, 'niveau', data_get($access, 'type_structure', '')));
        if (in_array($type, ['national', 'ia', 'ief'], true)) return $type;
        if (data_get($access, 'ief') || data_get($access, 'ief_id')) return 'ief';
        if (data_get($access, 'ia') || data_get($access, 'ia_id')) return 'ia';
        if (data_get($access, 'structure') || data_get($access, 'structure_organisationnelle_id')) return 'national';
        return null;
    }
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

    public function getOrganisationOptions(): array
    {
        try {
            $regional = $this->apiClient->get('admin/users/organisation-options');
            $national = $this->apiClient->get('admin/users/national-organisation-options');
        } catch (ConnectionException) {
            return ['national' => [], 'regional' => []];
        }

        return [
            'national' => $national->successful() ? $national->json('data', []) : [],
            'regional' => $regional->successful() ? $regional->json('data', []) : [],
        ];
    }

    public function assignOrganisationAccess(int|string $userId, array $data): array
    {
        try {
            $response = $this->apiClient->put("admin/users/{$userId}/organisation-access", $data);
        } catch (ConnectionException) {
            return [
                'success' => false,
                'message' => "Le compte a été créé, mais l'accès organisationnel n'a pas pu être affecté.",
                'errors' => [],
            ];
        }

        return [
            'success' => $response->successful(),
            'message' => $response->json('message'),
            'errors' => $response->json('errors', []),
        ];
    }

    /**
     * Vérifier auprès du backend si une adresse e-mail est disponible.
     */
    public function checkEmail(string $email): array
    {
        try {
            $response = $this->apiClient->get('admin/users/all');
        } catch (ConnectionException) {
            return [
                'available' => false,
                'message' => 'Impossible de vérifier cette adresse pour le moment.',
            ];
        }

        if (! $response->successful()) {
            return [
                'available' => false,
                'message' => $response->json('message', 'Impossible de vérifier cette adresse.'),
            ];
        }

        $normalisedEmail = mb_strtolower(trim($email));
        $alreadyUsed = collect($response->json('data', []))->contains(
            fn (array $user): bool => mb_strtolower(trim((string) ($user['email'] ?? ''))) === $normalisedEmail
        );

        return [
            'available' => ! $alreadyUsed,
            'message' => $alreadyUsed ? 'Cette adresse e-mail est déjà utilisée.' : null,
        ];
    }
}
