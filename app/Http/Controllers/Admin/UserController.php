<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Administration\UserService;
use App\Services\Organisation\RoleStructureMatrix;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;


class UserController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected RoleStructureMatrix $roleStructureMatrix,
    ) {}


    public function index(Request $request)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        $structureType = $request->query('structure_type');
        $structureType = in_array($structureType, ['national', 'ia', 'ief'], true) ? $structureType : null;

        $response = $this->userService->getUsers($page, $perPage, $structureType);
        $users = $response['items'] ?? [];
        $usersError = $response['error'] ?? null;
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
            $structure = data_get($user, 'acces_organisationnel.lieu_service')
                ?? data_get($user, 'acces_organisationnel.ief')
                ?? data_get($user, 'acces_organisationnel.ia')
                ?? data_get($user, 'acces_organisationnel.structure')
                ?? data_get($user, 'structure_organisationnelle');
            $service = is_array($structure)
                ? implode(' — ', array_unique(array_filter([
                    $structure['code'] ?? $structure['type'] ?? null,
                    $structure['libelle'] ?? $structure['nom'] ?? null,
                ])))
                : '—';
            $status = data_get($user, 'statut', data_get($user, 'status', false));
            $isActive = $status === 'actif' || filter_var($status, FILTER_VALIDATE_BOOLEAN);

            if (is_array($role) && array_key_exists('nom', $role)) {
                $role = $role['nom'];
            }

            if (is_array($service) && array_key_exists('nom', $service)) {
                $service = $service['nom'];
            }

            return [
                $fullName !== '' ? $fullName : (string) data_get($user, 'email', '—'),
                data_get($user, 'email', '—'),
                is_string($role) ? $role : '—',
                is_string($service) ? $service : '—',
                $isActive
                    ? '<span class="badge badge-active">Actif</span>'
                    : '<span class="badge badge-suspended">Suspendu</span>',
                '<div class="table-actions-inline"><a class="table-action" href="'.route('utilisateurs.show', $user['id']).'">Voir</a><a class="table-action" href="'.route('utilisateurs.edit', $user['id']).'">Modifier</a></div>',
            ];
        }, $users);

        config()->set('module-pages.utilisateurs.rows', $rows);
        config()->set('module-pages.utilisateurs.pagination', $pagination);
        config()->set('module-pages.utilisateurs.stats', [
            [
                'label' => 'Comptes',
                'value' => (string) ($pagination['total'] ?? count($users)),
                'note' => 'Tous profils',
                'icon' => 'fa-solid fa-users',
                'color' => 'green',
            ],
            [
                'label' => 'Actifs',
                'value' => (string) collect($users)->where('statut', 'actif')->count(),
                'note' => 'Accès ouverts',
                'icon' => 'fa-solid fa-circle-check',
                'color' => 'blue',
            ],
            [
                'label' => 'Suspendus',
                'value' => (string) collect($users)->reject(fn (array $user): bool => data_get($user, 'statut') === 'actif')->count(),
                'note' => 'À revoir',
                'icon' => 'fa-solid fa-user-lock',
                'color' => 'yellow',
            ],
            [
                'label' => 'Administrateurs',
                'value' => (string) collect($users)->filter(fn (array $user): bool => in_array(data_get($user, 'role.slug'), ['admin', 'super_admin'], true))->count(),
                'note' => 'Accès sensibles',
                'icon' => 'fa-solid fa-user-shield',
                'color' => 'red',
            ],
        ]);
        config()->set('module-pages.utilisateurs.actions', [
            'Nouvel utilisateur',
            'Exporter',
        ]);

        $roles = $this->userService->getRoles();
        $roles = array_map(function (array $role): array {
            $role['structure_types'] = $this->roleStructureMatrix->allowedStructureTypes($role);

            return $role;
        }, $roles);
        $organisation = $this->userService->getOrganisationOptions();

        return view('pages.administration.utilisateurs', compact('roles', 'organisation', 'usersError', 'structureType'));
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
            'perimetre' => ['required', 'in:national,regional'],
            'structure_organisationnelle_id' => ['nullable', 'required_if:perimetre,national', 'integer'],
            'ia_id' => ['nullable', 'required_if:perimetre,regional', 'integer'],
            'ief_id' => ['nullable', 'integer'],
        ]);

        $accessData = collect($data)
            ->only(['structure_organisationnelle_id', 'ia_id', 'ief_id'])
            ->filter(fn ($value): bool => $value !== null && $value !== '')
            ->all();

        $role = collect($this->userService->getRoles())->first(
            fn (array $item): bool => (string) ($item['id'] ?? '') === (string) $data['role_id']
        );
        $structureType = $this->roleStructureMatrix->structureType($accessData);

        if (! $role || ! $structureType || ! $this->roleStructureMatrix->allows($role, $structureType)) {
            throw ValidationException::withMessages([
                'role_id' => "Le rôle sélectionné n'est pas compatible avec la structure choisie.",
            ]);
        }

        $userData = collect($data)
            ->except(['perimetre', 'structure_organisationnelle_id', 'ia_id', 'ief_id'])
            ->all();

        $response = $this->userService->createUser(
            $userData + [
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

        $userId = data_get($response, 'data.id');
        if (! $userId) {
            return redirect()->route('utilisateurs.index')->with(
                'error',
                "Le compte a été créé, mais l'API n'a pas retourné son identifiant."
            );
        }

        $accessResponse = $this->userService->assignOrganisationAccess($userId, $accessData);
        if (! $accessResponse['success']) {
            return redirect()->route('utilisateurs.index')->with(
                'error',
                $accessResponse['message'] ?? "Le compte a été créé sans accès organisationnel."
            );
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

    public function show(int|string $id)
    {
        $result = $this->userService->getUser($id);
        abort_unless($result['success'] && is_array($result['data']), 404, $result['message'] ?: 'Utilisateur introuvable.');
        return view('pages.administration.utilisateurs.show', ['user' => $result['data']]);
    }

    public function edit(int|string $id)
    {
        $result = $this->userService->getUser($id);
        abort_unless($result['success'] && is_array($result['data']), 404, $result['message'] ?: 'Utilisateur introuvable.');
        return view('pages.administration.utilisateurs.edit', ['user' => $result['data'], 'roles' => $this->userService->getRoles()]);
    }

    public function update(Request $request, int|string $id)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'], 'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'], 'role_id' => ['required', 'integer'],
            'statut' => ['required', 'in:actif,inactif'], 'structure_organisationnelle_id' => ['nullable', 'integer'],
            'ia_id' => ['nullable', 'integer'], 'ief_id' => ['nullable', 'integer'],
        ]);
        $accessData = collect($data)->only(['structure_organisationnelle_id', 'ia_id', 'ief_id'])->filter(fn ($value): bool => $value !== null && $value !== '')->all();
        $role = collect($this->userService->getRoles())->first(fn (array $item): bool => (string) ($item['id'] ?? '') === (string) $data['role_id']);
        $structureType = $this->roleStructureMatrix->structureType($accessData);
        if (! $role || ! $structureType || ! $this->roleStructureMatrix->allows($role, $structureType)) {
            throw ValidationException::withMessages(['role_id' => "Le rôle sélectionné n'est pas compatible avec la structure choisie."]);
        }
        $result = $this->userService->updateUser($id, collect($data)->except(['structure_organisationnelle_id', 'ia_id', 'ief_id'])->all());
        if (! $result['success']) return back()->withInput()->withErrors($result['errors'] ?: ['user' => $result['message'] ?: 'Modification impossible.']);
        $accessResult = $this->userService->assignOrganisationAccess($id, $accessData);
        if (! $accessResult['success']) return back()->withInput()->withErrors(['organisation' => $accessResult['message'] ?: 'Rattachement impossible.']);
        return redirect()->route('utilisateurs.show', $id)->with('success', 'Utilisateur modifié avec succès.');
    }
}
