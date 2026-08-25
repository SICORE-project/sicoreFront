<?php

namespace App\Services\Organisation;

class RoleStructureMatrix
{
    public function allowedStructureTypes(array $role): array
    {
        $slug = $this->normalise($role['slug'] ?? $role['code'] ?? $role['nom'] ?? null);
        if ($slug && config()->has('organisation-access.role_slugs.'.$slug)) {
            return config('organisation-access.role_slugs.'.$slug, []);
        }

        return config('organisation-access.role_levels.'.$this->normalise($role['niveau'] ?? null), []);
    }

    public function allows(array $role, string $structureType): bool
    {
        return in_array($structureType, $this->allowedStructureTypes($role), true);
    }

    public function structureType(array $organisation): ?string
    {
        if (! empty($organisation['lieu_service_id'])) {
            return 'national';
        }

        if (! empty($organisation['ief_id'])) {
            return 'ief';
        }

        if (! empty($organisation['ia_id'])) {
            return 'ia';
        }

        return null;
    }

    public function structureTypeFromAccess(array $access): ?string
    {
        $level = $access['niveau'] ?? null;

        if ($level === 'national' || ! empty($access['structure'])) {
            return 'national';
        }

        if ($level === 'ief' || ! empty($access['ief'])) {
            return 'ief';
        }

        if ($level === 'ia' || ! empty($access['ia'])) {
            return 'ia';
        }

        return null;
    }

    private function normalise(mixed $value): string
    {
        return str((string) $value)->ascii()->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();
    }
}
