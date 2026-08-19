<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/permissions/all');

        $payload = $response->json();
        $items = $response->successful() ? data_get($payload, 'data', []) : [];
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
        $permissions = $paginator->toArray();

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

    public function store(Request $request)
    {
        $response = Http::withToken(session('access_token'))
            ->post(config('services.backend.url') . '/admin/permissions', [
                'nom' => $request->nom,
                'slug' => $this->permissionSlug($request),
                'groupe' => $request->groupe,
                'module' => $request->module,
                'action' => $request->action,
                'description' => $request->description,
                'est_actif' => $request->est_actif ?? true,
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

    public function update(Request $request, $id)
    {
        $response = Http::withToken(session('access_token'))
            ->put(config('services.backend.url') . '/admin/permissions/' . $id, [
                'nom' => $request->nom,
                'slug' => $this->permissionSlug($request),
                'groupe' => $request->groupe,
                'module' => $request->module,
                'action' => $request->action,
                'description' => $request->description,
                'est_actif' => $request->est_actif ?? true,
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

    private function permissionSlug(Request $request): string
    {
        return collect([$request->groupe, $request->module, $request->action])
            ->map(fn ($part): string => Str::of((string) $part)
                ->ascii()
                ->lower()
                ->replaceMatches('/[^a-z0-9]+/', '_')
                ->trim('_')
                ->toString())
            ->filter()
            ->implode('.');
    }

    public function sync()
    {
        $response = Http::withToken(session('access_token'))
            ->post(config('services.backend.url') . '/admin/permissions/sync');

        if ($response->successful()) {
            return redirect()->route('admin.permissions.index')
                ->with('success', $response->json()['message'] ?? 'Permissions synchronisées avec succès.');
        }

        return back()->with('error', 'Erreur lors de la synchronisation');
    }
}
