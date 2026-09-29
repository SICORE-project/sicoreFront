<?php

namespace App\Support;

use Illuminate\Http\Request;

class SicoreAccess
{
    /** Le profil connecté possède-t-il la permission ? (super_admin : toujours) */
    public static function can(Request $request, string $permission): bool
    {
        $user = (array) $request->session()->get('sicore_user', []);

        return ($user['role_slug'] ?? '') === 'super_admin'
            || in_array($permission, (array) ($user['permissions'] ?? []), true);
    }
}
