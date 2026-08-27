<?php

namespace App\Http\Controllers;

use App\Services\Api\AuthService;
use App\Support\PayrollReturnUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}
    
    public function showLogin(Request $request): View|RedirectResponse
    {
        $returnUrl = PayrollReturnUrl::sanitize($request->query('next'));

        if ($request->session()->has('sicore_user')) {
            return $returnUrl
                ? redirect()->to($returnUrl)
                : redirect()->route('dashboard');
        }

        return view('pages.auth.login', ['next' => $returnUrl]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'next' => ['nullable', 'string', 'max:2048'],
        ]);
        $returnUrl = PayrollReturnUrl::sanitize($validated['next'] ?? null);
        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        $result = $this->authService->login($credentials);

        if (! $result['success']) {
            return back()
                ->withInput([
                    'email' => $validated['email'],
                    'next' => $returnUrl,
                ])
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

        $redirect = $returnUrl
            ? redirect()->to($returnUrl)
            : redirect()->route('dashboard');

        return $redirect->with('success', $data['message']);
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
        $returnUrl = PayrollReturnUrl::sanitize($request->input('next'));

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
            ->route('login', array_filter(['next' => $returnUrl]))
            ->with(
                'success',
                'Vous êtes maintenant déconnecté.'
            );
    }
}
