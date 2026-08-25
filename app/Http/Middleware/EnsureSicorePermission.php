<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSicorePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if ($this->allows($request, $permission)) {
            return $next($request);
        }

        abort(403, 'Vous ne disposez pas de la permission requise.');
    }

    public function allows(Request $request, string $permission): bool
    {
        $user = (array) $request->session()->get('sicore_user', []);
        $role = mb_strtolower((string) ($user['role_slug'] ?? $user['role'] ?? ''));
        $permissions = collect($user['permissions'] ?? $request->session()->get('sicore_permissions', []))
            ->map(fn ($item) => is_array($item) ? ($item['slug'] ?? $item['nom'] ?? null) : $item)
            ->filter()
            ->all();

        if (in_array($role, ['administrateur', 'admin', 'super-admin', 'super_admin'], true) || in_array($permission, $permissions, true)) {
            return true;
        }

        return false;
    }
}
