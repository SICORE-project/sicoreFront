<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garde d'accès à une section entière (indemnités, paie, paramétrage...).
 * Chaque argument est soit un slug de permission, soit "role:<slug>".
 * L'accès est accordé si AU MOINS UN argument correspond (super_admin : toujours).
 * Les routes de notifications restent ouvertes à tout utilisateur connecté.
 * L'API reste l'autorité : ceci évite seulement d'afficher des pages interdites.
 */
class EnsureSicoreModuleAccess
{
    public function handle(Request $request, Closure $next, string ...$rules): Response
    {
        $user = (array) $request->session()->get('sicore_user', []);
        $role = (string) ($user['role_slug'] ?? '');
        $permissions = (array) ($user['permissions'] ?? []);

        if (str_contains((string) $request->route()?->getName(), 'notifications')) {
            return $next($request);
        }

        if ($role === 'super_admin') {
            return $next($request);
        }

        foreach ($rules as $rule) {
            if (str_starts_with($rule, 'role:') ? $role === substr($rule, 5) : in_array($rule, $permissions, true)) {
                return $next($request);
            }
        }

        abort(403, 'Votre profil n’a pas accès à cette section.');
    }
}
