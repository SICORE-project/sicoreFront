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

        $access = app(\App\Services\Organisation\InterfaceAccess::class);
        if ($access->isTeacher()) {
            abort_unless($access->allowsRoute((string) $request->route()?->getName()), 403);
            return $next($request);
        }

        $allowed = app(\App\Services\Organisation\DrhAccess::class)->allowsRoute(
            (string) $request->route()?->getName()
        );
        if (! $allowed && $request->isMethod('GET') && ! $request->expectsJson()) {
            return redirect()->route('dashboard');
        }
        abort_unless($allowed, 403, 'Vous ne disposez pas des droits ou du périmètre requis.');

        return $next($request);
    }
}
