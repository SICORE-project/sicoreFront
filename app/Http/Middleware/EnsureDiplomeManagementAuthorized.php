<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDiplomeManagementAuthorized
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->session()->get('sicore_user.role_slug');

        if (! in_array($role, ['admin', 'super_admin'], true)) {
            abort(403, 'Seuls les Administrateurs et Super Administrateurs peuvent gérer les diplômes.');
        }

        return $next($request);
    }
}
