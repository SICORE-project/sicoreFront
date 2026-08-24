<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $client = Http::withToken(session('access_token'));
        $response = $client->get(config('services.backend.url') . '/admin/roles/all');
        $responseTypeRoles = $client->get(config('services.backend.url') . '/admin/type-roles/all');

        $items = $response->successful() ? $response->json('data', []) : [];
        $items = is_array($items) ? array_values($items) : [];
        $perPage = 10;
        $page = max(1, $request->integer('page', 1));
        $paginator = new LengthAwarePaginator(
            array_slice($items, ($page - 1) * $perPage, $perPage),
            count($items),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $roles = $paginator->toArray();
        $typeRoles = $responseTypeRoles->successful() ? $responseTypeRoles->json('data', []) : [];

        return view('pages.administration.profils-roles', compact('roles', 'typeRoles'));
    }

    public function create()
    {
        $client = Http::withToken(session('access_token'));
        $response = $client->get(config('services.backend.url') . '/admin/permissions/all');
        $responseTypeRoles = $client->get(config('services.backend.url') . '/admin/type-roles/all');

        $permissions = $response->successful()
            ? collect($response->json()['data'] ?? [])->groupBy('module')
            : collect([]);

        $typeRoles = $responseTypeRoles->successful() ? $responseTypeRoles->json('data', []) : [];

        return view('pages.administration.roles-create', compact('permissions', 'typeRoles'));
    }

    public function show($id)
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/roles/' . $id);

        $role = $response->successful() ? $response->json('data') : null;

        if (!$role) {
            return redirect()->route('admin.roles.index')
                ->with('error', $response->json('message', 'Rôle non trouvé.'));
        }

        return view('pages.administration.roles-show', compact('role'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:50'],
            'type_role_id' => ['required', 'integer'],
            'est_actif' => ['required', 'boolean'],
        ]);

        $response = Http::withToken(session('access_token'))
            ->post(config('services.backend.url') . '/admin/roles', [
                'nom' => $request->nom,
                'slug' => $this->roleSlug($request->nom),
                'description' => $request->description,
                'type_role_id' => $request->integer('type_role_id'),
                'est_actif' => $request->est_actif ?? true,
                'permissions' => $request->permissions ?? [],
            ]);

        if ($response->successful()) {
            return redirect()->route('admin.roles.index')
                ->with('success', 'Rôle créé avec succès.');
        }

        return back()->withErrors($response->json()['errors'] ?? ['error' => 'Erreur lors de la création']);
    }

    public function edit($id)
    {
        $responseRole = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/roles/' . $id);

        $role = $responseRole->successful() ? $responseRole->json()['data'] ?? null : null;

        if (!$role) {
            return redirect()->route('admin.roles.index')->with('error', 'Rôle non trouvé');
        }

        $client = Http::withToken(session('access_token'));
        $responsePerms = $client->get(config('services.backend.url') . '/admin/permissions/all');
        $responseTypeRoles = $client->get(config('services.backend.url') . '/admin/type-roles/all');

        $permissions = $responsePerms->successful()
            ? collect($responsePerms->json()['data'] ?? [])->groupBy('module')
            : collect([]);

        $rolePermissions = collect($role['permissions'] ?? [])->pluck('id')->toArray();
        $typeRoles = $responseTypeRoles->successful() ? $responseTypeRoles->json('data', []) : [];

        return view('pages.administration.roles-edit', compact('role', 'permissions', 'rolePermissions', 'typeRoles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:50'],
            'type_role_id' => ['required', 'integer'],
            'est_actif' => ['required', 'boolean'],
        ]);

        $payload = [
                'nom' => $request->nom,
                'slug' => $this->roleSlug($request->nom),
                'description' => $request->description,
                'type_role_id' => $request->integer('type_role_id'),
                'est_actif' => $request->est_actif ?? true,
            ];

        if ($request->has('permissions')) {
            $payload['permissions'] = $request->permissions ?? [];
        }

        $response = Http::withToken(session('access_token'))
            ->put(config('services.backend.url') . '/admin/roles/' . $id, $payload);

        if ($response->successful()) {
            return redirect()->route('admin.roles.index')
                ->with('success', 'Rôle mis à jour avec succès.');
        }

        return back()->withErrors($response->json()['errors'] ?? ['error' => 'Erreur lors de la mise à jour']);
    }

    public function destroy($id)
    {
        $response = Http::withToken(session('access_token'))
            ->delete(config('services.backend.url') . '/admin/roles/' . $id);

        if ($response->successful()) {
            return redirect()->route('admin.roles.index')
                ->with('success', 'Rôle supprimé avec succès.');
        }

        return back()->with('error', $response->json()['message'] ?? 'Erreur lors de la suppression');
    }

    private function roleSlug(mixed $name): string
    {
        return Str::of((string) $name)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }

    public function permissions($id)
    {
        $responseRole = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/roles/' . $id);

        $role = $responseRole->successful() ? $responseRole->json()['data'] ?? null : null;

        if (!$role) {
            return redirect()->route('admin.roles.index')->with('error', 'Rôle non trouvé');
        }

        $responsePerms = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/permissions/all');

        $permissions = $responsePerms->successful()
            ? collect($responsePerms->json()['data'] ?? [])->groupBy('module')
            : collect([]);

        $rolePermissions = collect($role['permissions'] ?? [])->pluck('id')->toArray();

        return view('pages.administration.roles-permissions', compact('role', 'permissions', 'rolePermissions'));
    }

    public function syncPermissions(Request $request, $id)
    {
        $response = Http::withToken(session('access_token'))
            ->post(config('services.backend.url') . '/admin/roles/' . $id . '/sync-permissions', [
                'permissions' => $request->permissions ?? [],
            ]);

        if ($response->successful()) {
            return redirect()->route('admin.roles.permissions', $id)
                ->with('success', 'Permissions synchronisées avec succès.');
        }

        return back()->with('error', 'Erreur lors de la synchronisation');
    }
}
