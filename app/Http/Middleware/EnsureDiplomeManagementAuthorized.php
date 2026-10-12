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

        $access = app(\App\Services\Organisation\InterfaceAccess::class);
        $hasPermission = $access->usesPermissions() && $access->allowsRoute((string) $request->route()?->getName());
        if (! $access->isAdmin() && ! $hasPermission) {
            abort(403, 'Seuls les Administrateurs et Super Administrateurs peuvent gérer les diplômes.');
        }

        return $next($request);
    }
}
