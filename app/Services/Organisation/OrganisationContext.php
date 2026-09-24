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
        if (app(InterfaceAccess::class)->isDecpc()) {
            $scope = session('sicore_user.decpc.perimetre', []);
            if (($scope['type'] ?? null) === 'national') return [];
            if (in_array($scope['type'] ?? null, ['ia_id', 'ief_id', 'lieu_service_id'], true)) {
                $id = $scope['id'] ?? null;
                return filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                    ? [$scope['type'] => (int) $id] : [];
            }
            return [];
        }
        if (app(InterfaceAccess::class)->isIa()) {
            $id = session('sicore_user.ia_id') ?: session('sicore_user.ia.id') ?: data_get($this->access(), 'ia_id', data_get($this->access(), 'ia.id'));
            return $id ? ['ia_id' => $id] : [];
        }
        $scope = session('sicore_user.drh.perimetre', []);
        if (($scope['type'] ?? null) === 'national') return [];
        if (in_array($scope['type'] ?? null, ['ia_id', 'ief_id', 'lieu_service_id'], true) && ! empty($scope['id'])) {
            return [$scope['type'] => $scope['id']];
        }
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
        if (app(InterfaceAccess::class)->isDecpc()) {
            return $this->isScoped() ? 'Périmètre DECPC' : 'Périmètre DECPC non défini';
        }
        if (app(InterfaceAccess::class)->isIa()) {
            $name = session('sicore_user.ia.libelle') ?: data_get($this->access(), 'ia.libelle');
            return $name ? (preg_match('/^IA\b/iu', $name) ? $name : 'IA de '.$name) : 'IA non disponible';
        }
        if (session('sicore_user.drh.perimetre.type') === 'national') return 'Périmètre national';
        $access = $this->access();
        $structure = data_get($access, 'ief', data_get($access, 'ia', data_get($access, 'structure')));
        if (! is_array($structure)) return $this->isScoped() ? 'Périmètre organisationnel' : 'Périmètre global';
        $parts = [data_get($structure, 'code'), data_get($structure, 'libelle'), data_get($structure, 'nom')];
        return collect($parts)->filter()->unique()->join(' — ') ?: 'Périmètre organisationnel';
    }

    public function isScoped(): bool
    {
        if (app(InterfaceAccess::class)->isDecpc()) return session('sicore_user.decpc.perimetre.type') === 'national' || $this->query() !== [];

        return session('sicore_user.drh.perimetre.type') === 'national' || $this->query() !== [];
    }
}
