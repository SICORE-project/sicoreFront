<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Administration\UserService;
use Illuminate\Http\Request;


class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}


    public function index(Request $request)
    {
        $filters = $request->validate([
            'nom' => ['nullable', 'string', 'max:100'], 'matricule' => ['nullable', 'string', 'max:30'],
            'ia_id' => ['nullable', 'integer', 'min:1', 'required_with:ief_id,etablissement_id'],
            'ief_id' => ['nullable', 'integer', 'min:1', 'required_with:etablissement_id'],
            'etablissement_id' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:10,20,50'],
        ]);
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 10);

        $response = $this->userService->getUsers($page, $perPage, null, $filters);
        $users = $response['items'] ?? [];
        $pagination = $response['pagination'] ?? [
            'current_page' => $page,
            'last_page' => 1,
            'total' => count($users),
            'per_page' => $perPage,
        ];

        $rows = array_map(function (array $user): array {
            $prenom = (string) data_get($user, 'prenom', '');
            $nom = (string) data_get($user, 'nom', '');
            $fullName = trim($prenom . ' ' . $nom);
            $role = data_get($user, 'role.nom', data_get($user, 'role', '—'));
            $status = data_get($user, 'statut', data_get($user, 'status', false));
            $isActive = $status === 'actif' || filter_var($status, FILTER_VALIDATE_BOOLEAN);
            $awaitingVerification = ! empty($user['enseignant_id']) && empty($user['password_defined']);

            if (is_array($role) && array_key_exists('nom', $role)) {
                $role = $role['nom'];
            }

            return [
                (!empty($user['is_online']) ? '<span class="user-presence-prefix">'.view('components.online-indicator')->render().'</span>' : '')
                    . e($fullName !== '' ? $fullName : (string) data_get($user, 'email', '—')),
                e(data_get($user, 'matricule') ?: '—'),
                e(is_string($role) ? $role : '—'),
                $isActive
                    ? ($awaitingVerification
                        ? '<span class="badge badge-suspended">En attente de vérification</span>'
                        : '<span class="badge badge-active">Actif</span>')
                    : '<span class="badge badge-suspended">Suspendu</span>',
                $this->actions($user, $isActive),
            ];
        }, $users);

        $options = $this->userService->userFilterOptions($request->only(['ia_id', 'ief_id']));
        $listError = $response['error'] ?? $options['error'] ?? null;
        config()->set('module-pages.utilisateurs.filter_options', $options['data']);
        config()->set('module-pages.utilisateurs.columns', ['Nom complet', 'Matricule', 'Profil', 'Statut', 'Actions']);
        config()->set('module-pages.utilisateurs.rows', $rows);
        config()->set('module-pages.utilisateurs.pagination', $pagination);
        config()->set('module-pages.utilisateurs.actions', [
            'Nouvel utilisateur',
            'Exporter',
        ]);

        $roles = $this->userService->getRoles();
        $organisation = $this->userService->getOrganisationOptions();
        $structures = array_merge($organisation['national'] ?? [], $organisation['regional'] ?? []);

        return view('pages.administration.utilisateurs', compact('roles', 'organisation', 'structures', 'listError'));
    }

    public function filterOptions(Request $request)
    {
        $filters = $request->validate([
            'ia_id' => ['nullable', 'integer', 'min:1', 'required_with:ief_id'],
            'ief_id' => ['nullable', 'integer', 'min:1'],
        ]);
        $result = $this->userService->userFilterOptions($filters);
        return response()->json(['data' => $result['data'], 'message' => $result['error']], $result['status']);
    }

    public function create()
    {
        $roles = $this->userService->getRoles();

        return view(
            'pages.administration.utilisateurs.create',
            compact('roles')
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'integer'],
            'statut' => ['required', 'in:actif,inactif'],
            'lieu_service_id' => ['nullable', 'integer'],
        ]);

        $response = $this->userService->createUser(
            $data + [
                'password_confirmation' => $request->input('password_confirmation'),
            ]
        );

        if (! $response['success']) {
            $redirect = back()
                ->withInput()
                ->withErrors($response['errors'] ?? []);

            if (empty($response['errors'])) {
                $redirect->with(
                    'error',
                    $response['message'] ?? 'Une erreur est survenue.'
                );
            }

            return $redirect;
        }

        return redirect()
            ->route('utilisateurs.index')
            ->with(
                'success',
                $response['message'] ?? 'Utilisateur créé avec succès.'
            );
    }

    public function checkEmail(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        return response()->json(
            $this->userService->checkEmail($data['email'])
        );
    }

    public function iaOptions()
    {
        return response()->json($this->userService->ias());
    }

    public function edit($id)
    {
        return redirect()->route('utilisateurs.index');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'role_id' => ['required', 'integer'],
            'statut' => ['required', 'in:actif,inactif'],
            'lieu_service_id' => ['nullable', 'integer'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $response = $this->userService->updateUser($id, $data);

        if (! $response['success']) {
            return back()
                ->withInput()
                ->withErrors($response['errors'] ?? [])
                ->with('error', $response['message'] ?? 'Mise à jour impossible.')
                ->with('edit_user_id', $id);
        }

        return redirect()->route('utilisateurs.index')->with('success', $response['message'] ?? 'Utilisateur mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $response = $this->userService->deleteUser($id);

        return redirect()->route('utilisateurs.index')->with(
            $response['success'] ? 'success' : 'error',
            $response['message'] ?? ($response['success'] ? 'Utilisateur supprimé.' : 'Suppression impossible.')
        );
    }

    public function toggleStatus($id)
    {
        $response = $this->userService->toggleUserStatus($id);

        return redirect()->route('utilisateurs.index')->with(
            $response['success'] ? 'success' : 'error',
            $response['message'] ?? 'Mise à jour du statut impossible.'
        );
    }

    private function actions(array $user, bool $isActive): string
    {
        $json = e(json_encode($user, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG));
        $name = e((string) data_get($user, 'nom_complet', $user['email'] ?? ''));

        $invitation = ! empty($user['enseignant_id']) && $isActive && empty($user['password_defined'])
            ? view('pages.administration.utilisateurs.teacher-invitation-action', ['id' => $user['id']])->render() : '';

        return '<div class="table-actions-inline user-icon-actions">'
            . '<button class="table-action" type="button" title="Voir" aria-label="Voir" data-user-action="view" data-user="' . $json . '"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>'
            . '<button class="table-action" type="button" title="Modifier" aria-label="Modifier" data-user-action="edit" data-user="' . $json . '"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i></button>'
            . '<button class="table-action delete" type="button" title="Supprimer" aria-label="Supprimer" data-user-action="delete" data-user-id="' . (int) $user['id'] . '" data-user-name="' . $name . '"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>'
            . $invitation
            . '</div>';
    }
}
