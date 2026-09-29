<?php

namespace App\Http\Middleware;

use App\Support\SicoreAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dans le module indemnités, un profil sans indemnites.read (ex. DECPC) n'accède
 * qu'aux pages de convocations et de pièces justificatives (et aux notifications). Le reste est réservé à la DAGE.
 */
class EnsureIndemnitesSection
{
    public function handle(Request $request, Closure $next): Response
    {
        $name = (string) $request->route()?->getName();

        $ouverteAuxDepots = str_starts_with($name, 'indemnites.pieces-justificatives')
            || str_starts_with($name, 'indemnites.convocations')
            || $name === 'indemnites.filtres-options'
            || str_contains($name, 'notifications');

        if ($ouverteAuxDepots || SicoreAccess::can($request, 'indemnites.read')) {
            return $next($request);
        }

        abort(403, 'Cette page est réservée à la DAGE.');
    }
}
