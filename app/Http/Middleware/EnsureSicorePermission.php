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
        $role = \Illuminate\Support\Str::slug((string) ($user['role_slug'] ?? $user['role'] ?? ''), '_');
        $permissions = collect($user['permissions'] ?? $request->session()->get('sicore_permissions', []))
            ->map(fn ($item) => is_array($item) ? ($item['slug'] ?? $item['nom'] ?? null) : $item)
            ->filter()
            ->all();

        $aliases = [
            'personnel.consulter' => 'enseignants.read',
            'personnel.creer' => 'enseignants.create',
            'personnel.modifier' => 'enseignants.update',
            'personnel.supprimer' => 'enseignants.delete',
        ];
        $required = $aliases[$permission] ?? $permission;
        $permissions = array_map(fn ($slug) => $aliases[$slug] ?? $slug, $permissions);
        if (in_array($role, ['administrateur', 'admin', 'super_administrateur', 'super_admin'], true) || in_array($required, $permissions, true)) {
            return true;
        }

        return false;
    }
}
