<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TypeRoleController extends Controller
{
    public function index()
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url').'/admin/type-roles/all');

        return view('pages.administration.type-roles.index', [
            'typeRoles' => $response->successful() ? $response->json('data', []) : [],
        ]);
    }

    public function create()
    {
        return view('pages.administration.type-roles.form', ['typeRole' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $response = Http::withToken(session('access_token'))
            ->post(config('services.backend.url').'/admin/type-roles', $data);

        return $this->redirectAfterWrite($response, 'Type de rôle créé avec succès.');
    }

    public function edit(int $id)
    {
        $response = Http::withToken(session('access_token'))
            ->get(config('services.backend.url')."/admin/type-roles/{$id}");

        if (! $response->successful()) {
            return redirect()->route('admin.type-roles.index')->with('error', 'Type de rôle non trouvé.');
        }

        return view('pages.administration.type-roles.form', ['typeRole' => $response->json('data')]);
    }

    public function update(Request $request, int $id)
    {
        $data = $this->validated($request);
        $response = Http::withToken(session('access_token'))
            ->put(config('services.backend.url')."/admin/type-roles/{$id}", $data);

        return $this->redirectAfterWrite($response, 'Type de rôle mis à jour avec succès.');
    }

    public function destroy(int $id)
    {
        $response = Http::withToken(session('access_token'))
            ->delete(config('services.backend.url')."/admin/type-roles/{$id}");

        if ($response->successful()) {
            return redirect()->route('admin.type-roles.index')->with('success', 'Type de rôle supprimé avec succès.');
        }

        return back()->with('error', $response->json('message', 'Suppression impossible.'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'libelle' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'est_actif' => ['required', 'boolean'],
        ]);
    }

    private function redirectAfterWrite($response, string $success)
    {
        if ($response->successful()) {
            return redirect()->route('admin.type-roles.index')->with('success', $success);
        }

        return back()->withInput()->withErrors($response->json('errors', ['error' => 'Enregistrement impossible.']));
    }
}
