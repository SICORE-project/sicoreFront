<?php

namespace App\Http\Controllers;

use App\Contracts\SicoreApiClientInterface;
use App\Exceptions\SicoreApiException;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gère la connexion et la déconnexion dans l'application frontend.
 *
 * Chemin complet :
 * routes/web.php → AuthController → app/Contracts/SicoreApiClientInterface.php
 * → app/Services/SicoreApi.php
 * → backend /api/login ou /api/logout.
 *
 * Le mot de passe n'est jamais conservé dans la session frontend. Seuls le
 * profil utilisateur, le jeton API et sa date d'expiration y sont stockés.
 */
class AuthController extends Controller
{
    /** Le contrat API est résolu par app/Providers/ApiClientServiceProvider.php. */
    public function __construct(private readonly SicoreApiClientInterface $api) {}

    /**
     * Affiche la vue resources/views/pages/auth/login.blade.php.
     * Un utilisateur déjà connecté est envoyé directement au tableau de bord.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('sicore_user')) {
            return redirect()->route('dashboard');
        }

        return view('pages.auth.login');
    }

    /**
     * Valide le formulaire de connexion puis transmet les identifiants à l'API.
     * La route correspondante est POST /login dans routes/web.php.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        // Les règles sont centralisées dans app/Http/Requests/LoginRequest.php.
        $credentials = $request->validated();

        try {
            // La vérification réelle des identifiants est faite par le backend.
            $result = $this->api->login($credentials['email'], $credentials['password']);
        } catch (SicoreApiException $exception) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => $exception->status >= 500
                        ? 'Le backend SICORE est indisponible. Vérifiez qu’il est démarré sur le port 8000.'
                        : $exception->getMessage(),
                ]);
        }

        // Régénérer la session empêche la réutilisation d'un ancien identifiant.
        $request->session()->regenerate();
        $user = (array) ($result['user'] ?? []);
        $role = (array) ($user['role'] ?? []);
        $request->session()->put('sicore_user', [
            ...$user,
            'name' => trim(($user['prenom'] ?? '').' '.($user['nom'] ?? '')),
            'role' => $role['libelle'] ?? 'Utilisateur',
        ]);
        $request->session()->put('sicore_token', (string) $result['token']);
        $request->session()->put(
            'sicore_token_expires_at',
            now()->addMinutes((int) config('sicore.api.token_lifetime'))->timestamp
        );

        return redirect()->route('dashboard')
            ->with('success', 'Connexion sécurisée au backend SICORE réussie.');
    }

    /**
     * Révoque le jeton côté backend puis détruit toujours la session locale.
     * Même si l'API est arrêtée, l'utilisateur est déconnecté du frontend.
     */
    public function logout(Request $request): RedirectResponse
    {
        $token = (string) $request->session()->get('sicore_token', '');
        if ($token !== '') {
            try {
                $this->api->logout($token);
            } catch (SicoreApiException) {
                // La session locale est toujours invalidée, même si l'API est indisponible.
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Vous êtes maintenant déconnecté.');
    }
}
