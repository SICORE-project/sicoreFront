<?php

namespace App\Services\Organisation;

use App\Http\Middleware\EnsureSicorePermission;
use Illuminate\Support\Str;

class InterfaceAccess
{
    public function isDrh(): bool
    {
        return in_array($this->role(), ['agent_drh', 'drh', 'directeur_des_ressources_humaines'], true);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role(), ['admin', 'administrateur', 'super_admin', 'super_administrateur'], true);
    }

    private function role(): string
    {
        return Str::slug((string) (session('sicore_user.role_slug') ?: session('sicore_user.role', '')), '_');
    }

    public function usesPermissions(): bool
    {
        // Les anciennes sessions sans catalogue conservent leur fonctionnement jusqu'à reconnexion.
        return ! $this->isAdmin() && ($this->isDrh() || session()->has('sicore_user.permissions') || session()->has('sicore_permissions'));
    }

    public function allows(string $permission): bool
    {
        if (! $this->usesPermissions()) return true;

        $permissions = array_merge([$permission], config('interface-permissions.aliases', [])[$permission] ?? []);
        return collect($permissions)->contains(fn ($slug) => app(EnsureSicorePermission::class)->allows(request(), $slug));
    }

    public function permissionsForRoute(string $route): array
    {
        $explicit = config('interface-permissions.routes', []);
        if (isset($explicit[$route])) return array_unique([$route, ...(array) $explicit[$route]]);

        $teacherRoutes = [
            'enseignants.legacy.index' => ['enseignants.read', 'personnel.consulter'],
            'enseignants.legacy.create' => ['enseignants.create', 'personnel.creer'],
            'enseignants.iefs' => ['enseignants.read', 'personnel.consulter'],
            'enseignants.etablissements' => ['enseignants.read', 'personnel.consulter'],
            'enseignants.referentiels.store' => ['parametrage.modifier'],
            'enseignants.disciplines.store' => ['enseignants.disciplines.associer'],
        ];
        if (isset($teacherRoutes[$route])) return [$route, ...$teacherRoutes[$route]];

        foreach (array_merge(['enseignants' => 'enseignants'], config('interface-permissions.resources', [])) as $prefix => $resource) {
            if ($route !== $prefix && ! str_starts_with($route, $prefix.'.')) continue;
            $suffix = $route === $prefix ? 'index' : substr($route, strlen($prefix) + 1);
            $action = match ($suffix) {
                'index', 'show', 'permissions' => 'read',
                'create', 'store', 'check-email' => 'create',
                'edit', 'update', 'status', 'toggle-status', 'syncPermissions' => 'update',
                'destroy' => 'delete',
                default => null,
            };
            if ($action === null) return [$route];
            $permissions = [$route, $resource.'.'.$action];
            if ($action !== 'read' && $resource !== 'enseignants') $permissions[] = $resource.'.manage';
            if ($resource === 'enseignants') {
                $permissions[] = 'personnel.'.['read' => 'consulter', 'create' => 'creer', 'update' => 'modifier', 'delete' => 'supprimer'][$action];
            }
            return array_unique($permissions);
        }

        // Les autres écrans utilisent leur permission explicite, sans joker de module.
        return $route === '' ? [] : [$route];
    }

    public function allowsRoute(string $route): bool
    {
        if (! $this->usesPermissions() || in_array($route, ['dashboard', 'logout'], true)) return true;

        if (str_starts_with($route, 'enseignants.')) {
            if (! $this->allows('personnel.consulter')) return false;
            // La liste s'ouvre même sans périmètre ; son contrôleur ne charge alors aucune donnée.
            if ($this->isDrh() && ! app(OrganisationContext::class)->isScoped()
                && ! in_array($route, ['enseignants.index', 'enseignants.legacy.index'], true)) return false;
        }

        return collect($this->permissionsForRoute($route))->contains(fn ($permission) => $this->allows($permission));
    }

    public function navigation(array $items): array
    {
        if (! $this->usesPermissions()) return $items;

        $visible = [];
        foreach ($items as $item) {
            if ($this->isDrh() && ($item['route'] ?? '') === 'enseignants.index') continue;
            if (isset($item['links'])) {
                $item['links'] = $this->navigation($item['links']);
                if ($item['links'] === []) continue;
            } elseif (! $this->allowsRoute($item['route'] ?? '')) {
                continue;
            }
            $visible[] = $item;
        }
        return $visible;
    }
}
