<?php

namespace App\Http\Controllers;

use App\Services\Api\AuthService;
use App\Support\PayrollReturnUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            'acces_organisationnel' => $data['user']['acces_organisationnel'] ?? $data['user']['organisation_access'] ?? [],
        ]);

        $redirect = $returnUrl
            ? redirect()->to($returnUrl)
            : redirect()->route('dashboard');

        return $redirect->with('success', $data['message']);
    }

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


    /**
     * URL de base de l'API sicoreBack (à définir dans .env : SICORE_API_URL)
     */
    private function apiBase(): string
    {
        return rtrim(env('API_BASE_URL', 'http://localhost:8000/api'), '/');
    }


    /**
     * Étape 1 — Affiche le formulaire de saisie de l'email
     */
    public function showForgotPasswordForm(): View
    {
        return view('pages.auth.forgot-password');
    }


    /**
     * Étape 1 — Envoie l'email à l'API pour déclencher l'envoi de l'OTP
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $response = Http::acceptJson()
            ->post($this->apiBase() . '/send-otp', [
                'email' => $request->input('email'),
            ]);

        if ($response->failed())
        {
            return back()
                ->withInput()
                ->withErrors([
                    'email' => $response->json('message') ?? "Impossible d'envoyer le code pour le moment."
                ]);
        }

        session([
            'reset_email' => $request->input('email'),
        ]);

        return redirect()
            ->route('password.reset.otp')
            ->with('status', 'Un code de vérification a été envoyé par email.');
    }


    /**
     * Étape 2 — Affiche le formulaire de saisie du code OTP
     */
    public function showVerifyOtpForm(): View|RedirectResponse
    {
        if (!session('reset_email'))
        {
            return redirect()
                ->route('password.reset.form')
                ->withErrors(['email' => 'Merci de recommencer la procédure.']);
        }

        return view('pages.auth.verify-otp', [
            'email' => session('reset_email'),
        ]);
    }


    /**
     * Étape 2 — Vérifie le code OTP auprès de l'API
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $email = session('reset_email');

        if (!$email)
        {
            return redirect()
                ->route('password.reset.form')
                ->withErrors(['email' => 'Merci de recommencer la procédure.']);
        }

        $response = Http::acceptJson()
            ->post($this->apiBase() . '/verify-otp', [
                'email' => $email,
                'otp'   => $request->input('otp'),
            ]);

        if ($response->failed())
        {
            return back()
                ->withErrors([
                    'otp' => $response->json('message') ?? 'Code invalide ou expiré.'
                ]);
        }

        session([
            'reset_token' => $response->json('reset_token'),
        ]);

        return redirect()->route('password.reset.newpassword');
    }


    /**
     * Étape 2 bis — Renvoi du code OTP
     */
    public function resendOtp(): RedirectResponse
    {
        $email = session('reset_email');

        if (!$email)
        {
            return redirect()->route('password.reset.form');
        }

        $response = Http::acceptJson()
            ->post($this->apiBase() . '/send-otp', [
                'email' => $email,
            ]);

        if ($response->failed())
        {
            return back()->withErrors([
                'otp' => $response->json('message') ?? "Impossible de renvoyer le code."
            ]);
        }

        return back()->with('status', 'Un nouveau code a été envoyé.');
    }


    /**
     * Étape 3 — Affiche le formulaire de nouveau mot de passe
     */
    public function showResetPasswordForm(): View|RedirectResponse
    {
        if (!session('reset_email') || !session('reset_token'))
        {
            return redirect()
                ->route('password.reset.form')
                ->withErrors(['email' => 'Merci de recommencer la procédure.']);
        }

        return view('pages.auth.reset-password');
    }


    /**
     * Étape 3 — Envoie le nouveau mot de passe à l'API
     */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = session('reset_email');
        $token = session('reset_token');

        if (!$email || !$token)
        {
            return redirect()
                ->route('password.reset.form')
                ->withErrors(['email' => 'Merci de recommencer la procédure.']);
        }

        $response = Http::acceptJson()
            ->post($this->apiBase() . '/reset-password-otp', [
                'email'                 => $email,
                'reset_token'           => $token,
                'password'              => $request->input('password'),
                'password_confirmation' => $request->input('password_confirmation'),
            ]);

        if ($response->failed())
        {
            return back()->withErrors([
                'password' => $response->json('message') ?? 'Impossible de réinitialiser le mot de passe.'
            ]);
        }

        session()->forget(['reset_email', 'reset_token']);

        return redirect()
            ->route('login')
            ->with('status', 'Votre mot de passe a été réinitialisé avec succès. Connectez-vous.');
    }
}
