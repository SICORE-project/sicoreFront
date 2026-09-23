<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Liste des profils / rôles
     */
public function index(Request $request)
{
    $client = Http::withToken(session('access_token'));

    // Tous les rôles (actifs + inactifs), avec leurs permissions
    $response = $client->get(
        config('services.backend.url') . '/admin/roles',
        ['per_page' => 1000]
    );

    $responsePermissions = $client->get(
        config('services.backend.url') . '/admin/permissions/all'
    );

    // /admin/roles renvoie un paginator : data.data = liste des rôles
    $items = $response->successful()
        ? $response->json('data.data', [])
        : [];

    // Les plus récents en premier (le nouveau rôle apparaît en tête)
    $items = collect(is_array($items) ? $items : [])
        ->sortByDesc('id')
        ->values()
        ->all();

    $permissions = $responsePermissions->successful()
        ? collect($responsePermissions->json('data', []))
        : collect([]);

    // Pas de pagination serveur : le JS pagine (10 par page)
    $roles = ['data' => $items];

    $rolesError = $response->successful()
        ? null
        : 'Impossible de charger les rôles.';

    return view(
        'pages.administration.profils-roles',
        compact('roles', 'permissions', 'rolesError')
    );
}

    /**
     * Formulaire de création
     */
    public function create()
    {
        $client = Http::withToken(session('access_token'));

        $response = $client->get(
            config('services.backend.url') . '/admin/permissions/all'
        );

        /*
        |--------------------------------------------------------------------------
        | Permissions (liste plate)
        |--------------------------------------------------------------------------
        */
        $permissions = $response->successful()
            ? collect($response->json('data', []))
            : collect([]);

        return view(
            'pages.administration.roles-create',
            compact('permissions')
        );
    }

    /**
     * Afficher un rôle
     */
    public function show($id)
    {
        $response = Http::withToken(session('access_token'))
            ->get(
                config('services.backend.url') . '/admin/roles/' . $id
            );

        $role = $response->successful()
            ? $response->json('data')
            : null;

        if (!$role) {
            return redirect()
                ->route('admin.roles.index')
                ->with(
                    'error',
                    $response->json(
                        'message',
                        'Rôle non trouvé.'
                    )
                );
        }

        return view(
            'pages.administration.roles-show',
            compact('role')
        );
    }

    /**
     * Créer un rôle
     */
        /**
     * Créer un rôle
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:50'],
            'est_actif' => ['required', 'boolean'],
        ]);

        $response = Http::withToken(session('access_token'))
            ->post(
                config('services.backend.url') . '/admin/roles',
                [
                    'nom' => $request->nom,
                    'slug' => $this->roleSlug($request->nom),
                    'description' => $request->description,
                    'est_actif' => $request->est_actif ?? true,
                    'permissions' => $request->permissions ?? [],
                ]
            );

        if ($response->successful()) {
            return redirect()
                ->route('admin.roles.index')
                ->with(
                    'success',
                    'Rôle créé avec succès.'
                );
        }

        return back()->withErrors(
            $response->json()['errors']
                ?? ['error' => 'Erreur ' . $response->status() . ' : ' . $response->body()]
        );
    }

    /**
     * Formulaire de modification
     */
    public function edit($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Récupération du rôle
        |--------------------------------------------------------------------------
        */
        $responseRole = Http::withToken(session('access_token'))
            ->get(
                config('services.backend.url') . '/admin/roles/' . $id
            );

        $role = $responseRole->successful()
            ? $responseRole->json()['data'] ?? null
            : null;

        if (!$role) {
            return redirect()
                ->route('admin.roles.index')
                ->with(
                    'error',
                    'Rôle non trouvé'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Récupération des permissions (liste plate)
        |--------------------------------------------------------------------------
        */
        $client = Http::withToken(session('access_token'));

        $responsePerms = $client->get(
            config('services.backend.url') . '/admin/permissions/all'
        );

        $permissions = $responsePerms->successful()
            ? collect($responsePerms->json()['data'] ?? [])
            : collect([]);

        /*
        |--------------------------------------------------------------------------
        | Permissions actuellement attribuées au rôle
        |--------------------------------------------------------------------------
        */
        $rolePermissions = collect(
            $role['permissions'] ?? []
        )
            ->pluck('id')
            ->toArray();

        return view(
            'pages.administration.roles-edit',
            compact(
                'role',
                'permissions',
                'rolePermissions'
            )
        );
    }

    /**
     * Mettre à jour un rôle
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:50'],
            'est_actif' => ['required', 'boolean'],
        ]);

        $payload = [
            'nom' => $request->nom,
            'slug' => $this->roleSlug($request->nom),
            'description' => $request->description,
            'est_actif' => $request->est_actif ?? true,
        ];

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */
        if ($request->has('permissions')) {
            $payload['permissions'] = $request->permissions ?? [];
        }

        $response = Http::withToken(session('access_token'))
            ->put(
                config('services.backend.url') . '/admin/roles/' . $id,
                $payload
            );

        if ($response->successful()) {
            return redirect()
                ->route('admin.roles.index')
                ->with(
                    'success',
                    'Rôle mis à jour avec succès.'
                );
        }

        return back()->withErrors(
            $response->json()['errors']
                ?? ['error' => 'Erreur lors de la mise à jour']
        );
    }

    /**
     * Supprimer un rôle
     */
    public function destroy($id)
    {
        $response = Http::withToken(session('access_token'))
            ->delete(
                config('services.backend.url') . '/admin/roles/' . $id
            );

        if ($response->successful()) {
            return redirect()
                ->route('admin.roles.index')
                ->with(
                    'success',
                    'Rôle supprimé avec succès.'
                );
        }

        return back()->with(
            'error',
            $response->json()['message']
                ?? 'Erreur lors de la suppression'
        );
    }

    /**
     * Générer le slug du rôle
     */
    private function roleSlug(mixed $name): string
    {
        return Str::of((string) $name)
            ->ascii()
            ->lower()
            ->replaceMatches(
                '/[^a-z0-9]+/',
                '_'
            )
            ->trim('_')
            ->toString();
    }

    /**
     * Page de gestion des permissions d'un rôle
     */
    public function permissions($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Récupération du rôle
        |--------------------------------------------------------------------------
        */
        $responseRole = Http::withToken(session('access_token'))
            ->get(
                config('services.backend.url') . '/admin/roles/' . $id
            );

        $role = $responseRole->successful()
            ? $responseRole->json()['data'] ?? null
            : null;

        if (!$role) {
            return redirect()
                ->route('admin.roles.index')
                ->with(
                    'error',
                    'Rôle non trouvé'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Récupération des permissions (liste plate)
        |--------------------------------------------------------------------------
        */
        $responsePerms = Http::withToken(session('access_token'))
            ->get(
                config('services.backend.url') . '/admin/permissions/all'
            );

        $permissions = $responsePerms->successful()
            ? collect($responsePerms->json()['data'] ?? [])
            : collect([]);

        /*
        |--------------------------------------------------------------------------
        | Permissions du rôle
        |--------------------------------------------------------------------------
        */
        $rolePermissions = collect(
            $role['permissions'] ?? []
        )
            ->pluck('id')
            ->toArray();

        return view(
            'pages.administration.roles-permissions',
            compact(
                'role',
                'permissions',
                'rolePermissions'
            )
        );
    }

    /**
     * Synchroniser les permissions d'un rôle
     */
    public function syncPermissions(Request $request, $id)
    {
        $response = Http::withToken(session('access_token'))
            ->post(
                config('services.backend.url')
                . '/admin/roles/'
                . $id
                . '/sync-permissions',
                [
                    'permissions' => $request->permissions ?? [],
                ]
            );

        if ($response->successful()) {
            return redirect()
                ->route(
                    'admin.roles.permissions',
                    $id
                )
                ->with(
                    'success',
                    'Permissions synchronisées avec succès.'
                );
        }

        return back()->with(
            'error',
            'Erreur lors de la synchronisation'
        );
    }
}