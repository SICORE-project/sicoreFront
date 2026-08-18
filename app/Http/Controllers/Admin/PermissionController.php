<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/permissions', [
                'groupe' => $request->groupe,
                'module' => $request->module,
                'per_page' => 50,
            ]);

        $permissions = $response->successful()
            ? $response->json()['data'] ?? []
            : [];

        return view('pages.administration.permissions', compact('permissions'));
    }

    public function create()
    {
        return view('pages.administration.permissions-create');
    }

    public function store(Request $request)
    {
        $response = Http::withToken(session('access_token'))
            ->post(config('services.backend.url') . '/admin/permissions', [
                'nom' => $request->nom,
                'slug' => $request->slug,
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
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url') . '/admin/permissions/' . $id);

        $permission = $response->successful() ? $response->json()['data'] ?? null : null;

        if (!$permission) {
            return redirect()->route('admin.permissions.index')->with('error', 'Permission non trouvée');
        }

        return view('pages.administration.permissions-edit', compact('permission'));
    }

    public function update(Request $request, $id)
    {
        $response = Http::withToken(session('access_token'))
            ->put(config('services.backend.url') . '/admin/permissions/' . $id, [
                'nom' => $request->nom,
                'slug' => $request->slug,
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
