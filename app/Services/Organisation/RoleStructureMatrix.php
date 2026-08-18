<?php

namespace App\Services\Organisation;

class RoleStructureMatrix
{
    public function allowedStructureTypes(array $role): array
    {
        return config('organisation-access.role_levels.'.($role['niveau'] ?? ''), []);
    }

    public function allows(array $role, string $structureType): bool
    {
        return in_array($structureType, $this->allowedStructureTypes($role), true);
    }

    public function structureType(array $organisation): ?string
    {
        if (! empty($organisation['structure_organisationnelle_id'])) {
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
}
