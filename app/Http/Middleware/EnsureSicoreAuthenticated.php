<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protège toutes les routes placées dans le groupe "sicore.auth".
 *
 * Déclaration : bootstrap/app.php.
 * Utilisation : routes/web.php.
 * Données contrôlées : utilisateur, jeton API et date d'expiration en session.
 */
class EnsureSicoreAuthenticated
{
    /**
     * Autorise la requête si la session est complète et encore valide.
     * Sinon, renvoie une erreur JSON pour AJAX ou redirige vers la connexion.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Une date absente vaut 0 et est donc considérée comme expirée.
        $expired = (int) $request->session()->get('sicore_token_expires_at', 0) <= now()->timestamp;
        if (
            ! $request->session()->has('sicore_user')
            || ! $request->session()->has('sicore_token')
            || $expired
        ) {
            // Supprimer les valeurs ensemble évite une session partiellement valide.
            $request->session()->forget([
                'sicore_user',
                'sicore_token',
                'sicore_token_expires_at',
            ]);
            // Les appels JavaScript attendent du JSON et non une page de redirection.
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session SICORE expirée ou absente.',
                ], 401);
            }

            return redirect()->route('login')
                ->with('warning', 'Veuillez vous connecter pour accéder à SICORE.');
        }

        // Continuer vers le contrôleur demandé lorsque tous les contrôles passent.
        return $next($request);
    }
}
