<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PermissionController extends Controller
{
    /**
     * Liste des permissions (sans pagination, pour permettre
     * une recherche côté client sur l'ensemble des résultats)
     */
    public function index(Request $request)
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/permissions/all');

        $payload = $response->json();
        $items = $response->successful() ? data_get($payload, 'data', []) : [];
        $items = is_array($items) ? array_values($items) : [];

        $permissions = [
            'data' => $items,
        ];

        $permissionsError = $response->successful()
            ? null
            : $response->json('message', "Impossible de charger les permissions (HTTP {$response->status()}).");

        return view('pages.administration.permissions', compact('permissions', 'permissionsError'));
    }

    public function create()
    {
        return view('pages.administration.permissions-create');
    }

    public function show($id)
    {
        $permission = $this->findPermission($id);

        if (!$permission) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Permission non trouvée.');
        }

        return view('pages.administration.permissions-show', compact('permission'));
    }

    /**
     * Créer une permission
     */
    public function store(Request $request)
    {
        $response = Http::withToken(session('access_token'))
            ->post(config('services.backend.url') . '/admin/permissions', [
                'nom' => $request->nom,
                'description' => $request->description,
                'est_actif' => $request->boolean('est_actif'),
            ]);

        if ($response->successful()) {
            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permission créée avec succès.');
        }

        return back()->withErrors($response->json()['errors'] ?? ['error' => 'Erreur lors de la création']);
    }

    public function edit($id)
    {
        $permission = $this->findPermission($id);

        if (!$permission) {
            return redirect()->route('admin.permissions.index')->with('error', 'Permission non trouvée');
        }

        return view('pages.administration.permissions-edit', compact('permission'));
    }

    private function findPermission($id): ?array
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/permissions/all');

        if (!$response->successful()) {
            return null;
        }

        return collect($response->json('data', []))->first(
            fn (array $permission): bool => (string) ($permission['id'] ?? '') === (string) $id
        );
    }

    /**
     * Mettre à jour une permission
     */
    public function update(Request $request, $id)
    {
        $response = Http::withToken(session('access_token'))
            ->put(config('services.backend.url') . '/admin/permissions/' . $id, [
                'nom' => $request->nom,
                'description' => $request->description,
                'est_actif' => $request->boolean('est_actif'),
            ]);

        if ($response->successful()) {
            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permission mise à jour avec succès.');
        }

        return back()->withErrors($response->json()['errors'] ?? ['error' => 'Erreur lors de la mise à jour']);
    }

    public function destroy($id)
    {
        $response = Http::withToken(session('access_token'))
            ->delete(config('services.backend.url') . '/admin/permissions/' . $id);

        if ($response->successful()) {
            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permission supprimée avec succès.');
        }

        return back()->with('error', $response->json()['message'] ?? 'Erreur lors de la suppression');
    }
}