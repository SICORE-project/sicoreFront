<?php

namespace Tests\Unit;

use App\Services\Organisation\RoleStructureMatrix;
use Tests\TestCase;

class RoleStructureMatrixTest extends TestCase
{
    private RoleStructureMatrix $matrix;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matrix = app(RoleStructureMatrix::class);
    }

    public function test_system_role_is_restricted_to_national_structures(): void
    {
        $role = ['niveau' => 'systeme'];

        $this->assertTrue($this->matrix->allows($role, 'national'));
        $this->assertFalse($this->matrix->allows($role, 'ia'));
        $this->assertFalse($this->matrix->allows($role, 'ief'));
    }

    public function test_manager_role_accepts_every_declared_structure_type(): void
    {
        $role = ['niveau' => 'gestionnaire'];

        $this->assertTrue($this->matrix->allows($role, 'national'));
        $this->assertTrue($this->matrix->allows($role, 'ia'));
        $this->assertTrue($this->matrix->allows($role, 'ief'));
    }

    public function test_specific_manager_roles_are_limited_to_their_structure_level(): void
    {
        $this->assertSame(['ia'], $this->matrix->allowedStructureTypes([
            'slug' => 'gestionnaire_ia',
            'niveau' => 'gestionnaire',
        ]));
        $this->assertSame(['ief'], $this->matrix->allowedStructureTypes([
            'slug' => 'gestionnaire_ief',
            'niveau' => 'gestionnaire',
        ]));
        $this->assertSame(['national'], $this->matrix->allowedStructureTypes([
            'slug' => 'drh',
            'niveau' => 'gestionnaire',
        ]));
    }

    public function test_unknown_role_level_is_denied(): void
    {
        $this->assertFalse($this->matrix->allows(['niveau' => 'inconnu'], 'national'));
    }

    public function test_most_specific_structure_type_is_detected(): void
    {
        $this->assertSame('national', $this->matrix->structureType(['structure_organisationnelle_id' => 1]));
        $this->assertSame('ia', $this->matrix->structureType(['ia_id' => 2]));
        $this->assertSame('ief', $this->matrix->structureType(['ia_id' => 2, 'ief_id' => 3]));
    }
}
