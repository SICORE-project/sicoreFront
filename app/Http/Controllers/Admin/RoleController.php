<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/roles', [
                'search' => $request->search,
                'est_actif' => $request->est_actif,
                'per_page' => 15,
            ]);

        $roles = $response->successful()
            ? $response->json()['data'] ?? []
            : [];

        return view('pages.administration.profils-roles', compact('roles'));
    }

    public function create()
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/permissions/all');

        $permissions = $response->successful()
            ? collect($response->json()['data'] ?? [])->groupBy('module')
            : collect([]);

        return view('pages.administration.roles-create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $response = Http::withToken(session('access_token'))
            ->post(config('services.backend.url') . '/admin/roles', [
                'nom' => $request->nom,
                'slug' => $request->slug,
                'description' => $request->description,
                'niveau' => $request->niveau,
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

        $responsePerms = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/permissions/all');

        $permissions = $responsePerms->successful()
            ? collect($responsePerms->json()['data'] ?? [])->groupBy('module')
            : collect([]);

        $rolePermissions = collect($role['permissions'] ?? [])->pluck('id')->toArray();

        return view('pages.administration.roles-edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $response = Http::withToken(session('access_token'))
            ->put(config('services.backend.url') . '/admin/roles/' . $id, [
                'nom' => $request->nom,
                'slug' => $request->slug,
                'description' => $request->description,
                'niveau' => $request->niveau,
                'est_actif' => $request->est_actif ?? true,
                'permissions' => $request->permissions ?? [],
            ]);

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
