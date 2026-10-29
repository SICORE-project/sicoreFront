<?php

namespace App\Http\Middleware;

use App\Support\PayrollReturnUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSicoreAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('sicore_user')) {
            $returnUrl = PayrollReturnUrl::capture($request);
            $loginUrl = route('login', array_filter(['next' => $returnUrl]));

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session SICORE expirée ou absente.',
                    'next' => $returnUrl,
                    'login_url' => $loginUrl,
                ], 401);
            }

            return redirect($loginUrl)
                ->with('warning', 'Veuillez vous connecter pour accéder à SICORE.');
        }

        $this->refreshProfile($request);

        return $next($request);
    }

    /**
     * Relit le profil (rôle, permissions, IA/IEF) toutes les 60 s pour que les
     * changements de droits apparaissent sans reconnexion. En cas d'échec réseau,
     * la session existante est conservée.
     */
    private function refreshProfile(Request $request): void
    {
        $session = $request->session();
        $token = $session->get('access_token');

        if (! $token || now()->timestamp - (int) $session->get('sicore_profile_checked_at', 0) < 60) {
            return;
        }

        $session->put('sicore_profile_checked_at', now()->timestamp);

        try {
            $user = app(\App\Contracts\SicoreApiClientInterface::class)->me($token)['user'] ?? null;
        } catch (\Throwable) {
            return;
        }

        if (! is_array($user)) {
            return;
        }

        $session->put('sicore_user', array_merge((array) $session->get('sicore_user', []), [
            'role' => $user['role']['nom'] ?? null,
            'role_slug' => $user['role']['slug'] ?? null,
            'permissions' => $user['permissions'] ?? [],
            'ia_id' => $user['ia_id'] ?? null,
            'ief_id' => $user['ief_id'] ?? null,
            'enseignant_id' => $user['enseignant_id'] ?? null,
            'acces_organisationnel' => $user['acces_organisationnel'] ?? [],
        ]));
    }
}
