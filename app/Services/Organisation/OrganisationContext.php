<?php

namespace App\Services\Organisation;

class OrganisationContext
{
    public function access(): array
    {
        $access = session('sicore_user.acces_organisationnel', []);
        return is_array($access) ? $access : [];
    }

    public function query(): array
    {
        $access = $this->access();
        $iefId = data_get($access, 'ief_id', data_get($access, 'ief.id'));
        $iaId = data_get($access, 'ia_id', data_get($access, 'ia.id'));
        $structureId = data_get($access, 'lieu_service_id', data_get($access, 'structure.id'));

        if ($iefId) return ['ief_id' => $iefId];
        if ($iaId) return ['ia_id' => $iaId];
        if ($structureId) return ['lieu_service_id' => $structureId];
        return [];
    }

    public function label(): string
    {
        $access = $this->access();
        $structure = data_get($access, 'ief', data_get($access, 'ia', data_get($access, 'structure')));
        if (! is_array($structure)) return 'Périmètre global';
        $parts = [data_get($structure, 'code'), data_get($structure, 'libelle'), data_get($structure, 'nom')];
        return collect($parts)->filter()->unique()->join(' — ') ?: 'Périmètre organisationnel';
    }

    public function isScoped(): bool
    {
        return $this->query() !== [];
    }
}
