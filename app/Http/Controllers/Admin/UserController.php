<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiClient;
use App\Services\Organisation\RoleStructureMatrix;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private ApiClient $apiClient,
        private RoleStructureMatrix $roleStructureMatrix,
    ) {}

    public function index(): View
    {
        $usersResponse = $this->apiClient->get('admin/users');
        $optionsResponse = $this->apiClient->get('admin/users/organisation-options');
        $nationalOptionsResponse = $this->apiClient->get('admin/users/national-organisation-options');
        $rolesResponse = $this->apiClient->get('admin/roles/all');
        $users = $usersResponse->successful() ? $usersResponse->json('data', []) : [];
        $organisation = $optionsResponse->successful() ? $optionsResponse->json('data', []) : [];
        $structuresNationales = $nationalOptionsResponse->successful() ? $nationalOptionsResponse->json('data', []) : [];
        $roles = $rolesResponse->successful() ? $rolesResponse->json('data', []) : [];
        $apiError = ! $usersResponse->successful()
            ? ($usersResponse->json('message') ?? 'Impossible de charger les utilisateurs.')
            : (! $optionsResponse->successful() ? ($optionsResponse->json('message') ?? 'Impossible de charger les structures.') : null);

        return view('pages.administration.utilisateurs', compact('users', 'organisation', 'structuresNationales', 'roles', 'apiError'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'role_id' => ['required', 'integer'],
            'statut' => ['required', 'in:actif,inactif'],
            'perimetre' => ['required', 'in:national,regional'],
            'structure_organisationnelle_id' => ['nullable', 'required_if:perimetre,national', 'integer'],
            'ia_id' => ['nullable', 'required_if:perimetre,regional', 'integer'],
            'ief_id' => ['nullable', 'integer'],
        ]);

        $organisation = collect($data)->only(['structure_organisationnelle_id', 'ia_id', 'ief_id'])->all();
        $this->validateRoleStructure((int) $data['role_id'], $this->roleStructureMatrix->structureType($organisation));
        $userData = collect($data)->except(['perimetre', 'structure_organisationnelle_id', 'ia_id', 'ief_id'])->all();
        $response = $this->apiClient->post('admin/users', $userData);

        if (! $response->successful()) {
            return $this->redirectFromApiResponse($response, '');
        }

        $userId = $response->json('data.id');
        if (! $userId) {
            return back()->withErrors(['api' => "Utilisateur créé, mais identifiant non retourné par l'API."]);
        }

        $organisationResponse = $this->apiClient->put("admin/users/{$userId}/organisation-access", $organisation);
        if (! $organisationResponse->successful()) {
            return back()->withErrors($organisationResponse->json('errors', [
                'organisation' => "Utilisateur créé, mais lieu de service non affecté.",
            ]));
        }

        return back()->with('success', 'Utilisateur créé et lieu de service affecté avec succès.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email'],
            'role_id' => ['required', 'integer'],
            'statut' => ['required', 'in:actif,inactif'],
        ]);

        $userResponse = $this->apiClient->get("admin/users/{$id}");
        if (! $userResponse->successful()) {
            return back()->withInput()->withErrors(['api' => "Impossible de vérifier l'affectation organisationnelle de l'utilisateur."]);
        }

        $structureType = $this->roleStructureMatrix->structureTypeFromAccess(
            $userResponse->json('data.acces_organisationnel', [])
        );
        $this->validateRoleStructure((int) $data['role_id'], $structureType);

        return $this->redirectFromApiResponse(
            $this->apiClient->put("admin/users/{$id}", $data),
            'Utilisateur modifié avec succès.'
        );
    }

    public function updateOrganisation(Request $request, string $id): RedirectResponse
    {
        $data = $request->validate([
            'ia_id' => ['nullable', 'integer'],
            'ief_id' => ['nullable', 'integer'],
            'lieu_service_id' => ['nullable', 'integer'],
            'structure_organisationnelle_id' => ['nullable', 'integer'],
        ]);

        $userResponse = $this->apiClient->get("admin/users/{$id}");
        if (! $userResponse->successful() || ! $userResponse->json('data.role.id')) {
            return back()->withInput()->withErrors(['api' => "Impossible de vérifier le rôle de l'utilisateur."]);
        }

        $this->validateRoleStructure(
            (int) $userResponse->json('data.role.id'),
            $this->roleStructureMatrix->structureType($data),
        );
        $response = $this->apiClient->put("admin/users/{$id}/organisation-access", $data);

        if ($response->successful()) {
            return back()->with('success', 'Accès organisationnel mis à jour avec succès.');
        }

        return back()->withInput()->withErrors(
            $response->json('errors', ['organisation' => $response->json('message') ?? 'Mise à jour impossible.'])
        );
    }

    private function redirectFromApiResponse($response, string $successMessage): RedirectResponse
    {
        if ($response->successful()) {
            return back()->with('success', $successMessage);
        }

        return back()->withInput()->withErrors(
            $response->json('errors', ['api' => $response->json('message') ?? 'Opération impossible.'])
        );
    }

    private function validateRoleStructure(int $roleId, ?string $structureType): void
    {
        $rolesResponse = $this->apiClient->get('admin/roles/all');
        $role = $rolesResponse->successful()
            ? collect($rolesResponse->json('data', []))->firstWhere('id', $roleId)
            : null;

        if (! $role) {
            throw ValidationException::withMessages([
                'role_id' => "Le rôle sélectionné est introuvable ou n'a pas pu être vérifié.",
            ]);
        }

        if (! $structureType || ! $this->roleStructureMatrix->allows($role, $structureType)) {
            throw ValidationException::withMessages([
                'role_id' => "Ce rôle n'est pas autorisé pour le type de structure sélectionné.",
            ]);
        }
    }
}
