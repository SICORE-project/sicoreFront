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
    public function getUsers(int $page = 1, int $perPage = 10, ?string $structureType = null, array $filters = []): array
    {
        $pagination = ['current_page' => $page, 'last_page' => 1, 'total' => 0, 'per_page' => $perPage];
        try {
            $response = $this->apiClient->get('admin/users', array_merge($filters, [
                'page' => $page, 'per_page' => $perPage, 'type_structure' => $structureType,
            ]));
        } catch (ConnectionException) {
            return ['items' => [], 'pagination' => $pagination, 'error' => 'Le service backend est inaccessible.'];
        }
        if (! $response->successful()) {
            return ['items' => [], 'pagination' => $pagination, 'error' => $response->json('message', 'Impossible de charger les utilisateurs.')];
        }
        return [
            'items' => $response->json('data', []),
            'pagination' => array_merge($pagination, $response->json('meta', [])),
            'error' => null,
        ];
    }

    public function userFilterOptions(array $filters = []): array
    {
        try {
            $response = $this->apiClient->get('admin/users/filter-options', $filters);
        } catch (ConnectionException) {
            return ['data' => [], 'error' => 'Impossible de charger les listes IA, IEF et établissements.', 'status' => 503];
        }
        return [
            'data' => $response->json('data', []),
            'error' => $response->successful() ? null : $response->json('message', 'Chargement des filtres impossible.'),
            'status' => $response->status(),
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

    public function deleteUser(int|string $userId): array
    {
        try {
            $response = $this->apiClient->delete("admin/users/{$userId}");
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est inaccessible.'];
        }

        return ['success' => $response->successful(), 'message' => $response->json('message')];
    }

    public function toggleUserStatus(int|string $userId): array
    {
        try {
            $response = $this->apiClient->post("admin/users/{$userId}/toggle-status");
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est inaccessible.'];
        }

        return ['success' => $response->successful(), 'message' => $response->json('message')];
    }

    public function structures(): array
    {
        try {
            $response = $this->apiClient->get('lieux-service/manage');
        } catch (ConnectionException) {
            return ['success' => false, 'data' => [], 'message' => 'Le service backend est inaccessible.'];
        }

        return ['success' => $response->successful(), 'data' => $response->json('data', []), 'message' => $response->json('message')];
    }

    public function ias(): array
    {
        try {
            $response = $this->apiClient->get('lieux-service/ias');
        } catch (ConnectionException) {
            return [];
        }

        return $response->successful() ? $response->json('data', []) : [];
    }

    public function saveStructure(array $data, int|string|null $id = null): array
    {
        try {
            $response = $id === null
                ? $this->apiClient->post('lieux-service', $data)
                : $this->apiClient->put("lieux-service/{$id}", $data);
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est inaccessible.', 'errors' => []];
        }

        return ['success' => $response->successful(), 'message' => $response->json('message'), 'errors' => $response->json('errors', [])];
    }

    public function deleteStructure(int|string $id): array
    {
        try {
            $response = $this->apiClient->delete("lieux-service/{$id}");
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est inaccessible.'];
        }

        return ['success' => $response->successful(), 'message' => $response->json('message')];
    }

    private function organisationType(array $user): ?string
    {
        $access = data_get($user, 'acces_organisationnel', []);
        $type = strtolower((string) data_get($access, 'niveau', data_get($access, 'type_structure', '')));
        if (in_array($type, ['national', 'ia', 'ief'], true)) return $type;
        if (data_get($access, 'ief') || data_get($access, 'ief_id')) return 'ief';
        if (data_get($access, 'ia') || data_get($access, 'ia_id')) return 'ia';
        if (data_get($access, 'structure') || data_get($access, 'lieu_service_id')) return 'national';
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
            $response = $this->apiClient->get('lieux-service');
        } catch (ConnectionException) {
            return ['national' => [], 'regional' => []];
        }

        if (! $response->successful()) {
            return ['national' => [], 'regional' => []];
        }

        $structures = $response->json('data', []);

        return [
            'national' => collect($structures)->where('perimetre', 'national')->values()->all(),
            'regional' => collect($structures)->where('perimetre', 'regional')->values()->all(),
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
