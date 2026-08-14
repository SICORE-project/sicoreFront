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
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;

        $response = $this->userService->getUsers($page, $perPage);
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
            $service = data_get($user, 'service.nom', data_get($user, 'service', '—'));
            $status = data_get($user, 'statut', data_get($user, 'status', false));
            $isActive = filter_var($status, FILTER_VALIDATE_BOOLEAN);

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
                '<div class="table-actions-inline"><button class="table-action " type="button">Voir</button><button class="table-action " type="button">Modifier</button></div>',
            ];
        }, $users);

        config()->set('module-pages.utilisateurs.rows', $rows);
        config()->set('module-pages.utilisateurs.pagination', $pagination);
        config()->set('module-pages.utilisateurs.actions', [
            'Nouvel utilisateur',
            'Exporter',
        ]);

        return view('pages.administration.utilisateurs');
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
        ]);

        // Le formulaire utilise des libellés lisibles, alors que l'API
        // attend un booléen pour le statut actif.
        $data['statut'] = $data['statut'] === 'actif';

        $response = $this->userService->createUser(
            $data + [
                'password_confirmation' => $request->input('password_confirmation'),
            ]
        );

        if (! $response['success']) {
            return back()
                ->withInput()
                ->withErrors($response['errors'] ?? [])
                ->with(
                    'error',
                    $response['message'] ?? 'Une erreur est survenue.'
                );
        }

        return redirect()
            ->route('utilisateurs.index')
            ->with(
                'success',
                $response['message'] ?? 'Utilisateur créé avec succès.'
            );
    }

    public function edit($id)
    {
        // Logic to show the form for editing an existing user
    }

    public function update(Request $request, $id)
    {
        // Logic to update an existing user in the database
    }

    public function destroy($id)
    {
        // Logic to delete a user from the database
    }
}
