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

        return $next($request);
    }
}
