<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use App\Services\Api\AuthService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}
    
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

        $result = $this->authService->login($credentials);

        if (! $result['success']) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => $result['message'],
                ]);
        }

        $data = $result['data'];

        $request->session()->regenerate();

        $request->session()->put('access_token', $data['access_token']);

        $request->session()->put('sicore_user', [
            'id' => $data['user']['id'],
            'nom' => $data['user']['nom'],
            'prenom' => $data['user']['prenom'],
            'email' => $data['user']['email'],
            'role' => $data['user']['role']['nom'] ?? null,
            'role_slug' => $data['user']['role']['slug'] ?? null,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', $data['message']);
    }

    // public function logout(Request $request): RedirectResponse
    // {
    //     if ($request->session()->has('access_token')) {
    //         $this->authService->logout();
    //     }


    //     $request->session()->invalidate();

    //     $request->session()->regenerateToken();


    //     return redirect()
    //         ->route('login')
    //         ->with(
    //             'success',
    //             'Vous êtes maintenant déconnecté.'
    //         );
    // }
    public function logout(Request $request): RedirectResponse
    {
        try {

            $this->authService->logout();

        } catch (\Exception $e) {

            // On continue la déconnexion locale
        }


        $request->session()->forget([
            'access_token',
            'sicore_user'
        ]);


        $request->session()->invalidate();

        $request->session()->regenerateToken();


        return redirect()
            ->route('login')
            ->with(
                'success',
                'Vous êtes maintenant déconnecté.'
            );
    }
}
