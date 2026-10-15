<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDiplomeManagementAuthorized
{
    public function handle(Request $request, Closure $next): Response
    {
        $permission = $request->isMethodSafe() ? 'parametrage.diplomes.read' : 'parametrage.diplomes.manage';

        if (! \App\Support\SicoreAccess::can($request, $permission)) {
            abort(403, 'Vous ne disposez pas de la permission requise pour les diplômes.');
        }

        return $next($request);
    }
}
