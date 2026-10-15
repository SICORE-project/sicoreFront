<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;

class TeacherAccountController extends Controller
{
    public function create()
    {
        return view('pages.administration.utilisateurs.teacher-account');
    }

    public function teachers(Request $request, ApiClient $api)
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        try {
            $response = $api->get('admin/users/teacher-candidates', $data);
        } catch (ConnectionException) {
            return response()->json(['message' => 'La recherche est indisponible. Réessayez.'], 503);
        }

        return response()->json($response->json(), $response->status());
    }

    public function store(Request $request, ApiClient $api)
    {
        $data = $request->validate([
            'enseignant_id' => ['required', 'integer'],
            'email' => ['required', 'email', 'max:255'],
        ]);
        try {
            $response = $api->post('admin/users/teacher-accounts', $data, 60);
        } catch (ConnectionException) {
            return back()->withInput()->with('error', 'Le service est inaccessible. Vérifiez la liste des utilisateurs avant de réessayer : le compte peut avoir été créé.');
        }
        if (! $response->successful()) {
            return back()->withInput()->withErrors($response->json('errors', []))
                ->with('error', $response->json('message', 'Impossible de créer ce compte enseignant.'));
        }

        return redirect()->route('utilisateurs.index')
            ->with($response->json('email_sent') ? 'success' : 'warning', $response->json('message'));
    }

    public function resend(int $id, ApiClient $api)
    {
        try {
            $response = $api->post("admin/users/{$id}/teacher-invitation", [], 60);
        } catch (ConnectionException) {
            return back()->with('error', 'La messagerie est indisponible. Réessayez dans quelques instants.');
        }

        return back()->with($response->successful() ? 'success' : 'error', $response->json('message', 'Envoi impossible.'));
    }
}
