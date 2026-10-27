<?php

namespace App\Support;

/**
 * Filtre le menu (config/navigation.php) selon le profil connecté.
 * Une entrée peut déclarer 'permission' (slug) et/ou 'roles' (slugs de rôles).
 * Un groupe dont tous les liens sont masqués disparaît.
 */
class NavigationAccess
{
    public static function filter(array $items, array $user): array
    {
        $role = (string) ($user['role_slug'] ?? '');
        $permissions = collect((array) ($user['permissions'] ?? []))
            ->map(fn ($permission) => is_array($permission) ? ($permission['slug'] ?? $permission['nom'] ?? null) : $permission)
            ->filter()
            ->values()
            ->all();

        $allowed = function (array $item) use ($role, $permissions, $user): bool {
            // Entrée réservée aux comptes rattachés à un enseignant (même pour le super admin).
            if (! empty($item['enseignant_requis']) && empty($user['enseignant_id'])) {
                return false;
            }
            if ($role === 'super_admin') {
                return true;
            }
            if (isset($item['roles']) && ! in_array($role, $item['roles'], true)) {
                return false;
            }
            if (isset($item['permission']) && array_intersect((array) $item['permission'], $permissions) === []) {
                return false;
            }

            return true;
        };

        $walk = function (array $items) use (&$walk, $allowed): array {
            $kept = [];
            foreach ($items as $item) {
                if (! $allowed($item)) {
                    continue;
                }
                if (isset($item['links'])) {
                    $item['links'] = $walk($item['links']);
                    if ($item['links'] === []) {
                        continue;
                    }
                }
                $kept[] = $item;
            }

            return $kept;
        };

        return $walk($items);
    }
}
