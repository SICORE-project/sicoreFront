<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Administration\UserService;
use Illuminate\Http\Request;


class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}


    public function index()
    {
        // 
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
