<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('sicore_user')) {
            return redirect()->route('dashboard');
        }

        return view('pages.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $validEmail = hash_equals(
            mb_strtolower((string) config('sicore.test.email')),
            mb_strtolower($credentials['email'])
        );

        $validPassword = hash_equals(
            (string) config('sicore.test.password'),
            $credentials['password']
        );

        if (! $validEmail || ! $validPassword) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Identifiants incorrects. Utilisez le compte de test affiché sous le formulaire.',
                ]);
        }

        $request->session()->regenerate();
        $request->session()->put('sicore_user', [
            'name' => 'Administrateur SICORE',
            'email' => config('sicore.test.email'),
            'role' => 'Administrateur',
            'mode' => 'test',
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Connexion réussie. Bienvenue dans le tableau de bord SICORE.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Vous êtes maintenant déconnecté.');
    }
}
