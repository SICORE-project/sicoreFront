<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSicoreAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->has('sicore_user')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session SICORE expirée ou absente.',
                ], 401);
            }

            return redirect()->route('login')
                ->with('warning', 'Veuillez vous connecter pour accéder à SICORE.');
        }

        return $next($request);
    }
}
