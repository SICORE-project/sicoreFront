<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TypeRoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Module Administration
|--------------------------------------------------------------------------
| Chaque route exige la permission de son action (lecture, création, modification,
| suppression), comme les routes correspondantes de l'API.
*/

Route::middleware(['sicore.auth', 'sicore.module:administration.users.read,administration.roles.read,administration.permissions.read'])
    ->group(function (): void {

        Route::get('/administration/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Utilisateurs
        Route::middleware('sicore.permission:administration.users.read')->group(function (): void {
            Route::get('/utilisateurs', [UserController::class, 'index'])->name('utilisateurs.index');
            Route::get('/utilisateurs/verifier-email', [UserController::class, 'checkEmail'])->name('utilisateurs.check-email');
            Route::get('/utilisateurs/ias', [UserController::class, 'iaOptions'])->name('utilisateurs.ia-options');
            Route::get('/utilisateurs/filtres', [UserController::class, 'filterOptions'])->name('utilisateurs.filter-options');
        });

        Route::middleware('sicore.permission:administration.users.create')->group(function (): void {
            Route::get('/utilisateurs/compte-enseignant', [\App\Http\Controllers\Admin\TeacherAccountController::class, 'create'])->name('utilisateurs.teacher.create');
            Route::get('/utilisateurs/enseignants-sans-compte', [\App\Http\Controllers\Admin\TeacherAccountController::class, 'teachers'])->name('utilisateurs.teacher.candidates');
            Route::post('/utilisateurs/compte-enseignant', [\App\Http\Controllers\Admin\TeacherAccountController::class, 'store'])->name('utilisateurs.teacher.store');
            Route::post('/utilisateurs/{id}/invitation-enseignant', [\App\Http\Controllers\Admin\TeacherAccountController::class, 'resend'])->whereNumber('id')->name('utilisateurs.teacher.resend');
            Route::get('/utilisateurs/nouveau', [UserController::class, 'create'])->name('utilisateurs.create');
            Route::post('/utilisateurs', [UserController::class, 'store'])->name('utilisateurs.store');
        });

        Route::get('/utilisateurs/profils-roles', [RoleController::class, 'index'])
            ->middleware('sicore.permission:administration.roles.read')
            ->name('utilisateurs.profils-roles');

        Route::get('/utilisateurs/permissions', [PermissionController::class, 'index'])
            ->middleware('sicore.permission:administration.permissions.read')
            ->name('utilisateurs.permissions');

        Route::get('/utilisateurs/structures', fn () => redirect()->route('parametres.lieux-service.index'));

        Route::get('/utilisateurs/{id}', [UserController::class, 'show'])
            ->whereNumber('id')
            ->middleware('sicore.permission:administration.users.read')
            ->name('utilisateurs.show');

        Route::middleware('sicore.permission:administration.users.update')->group(function (): void {
            Route::get('/utilisateurs/{id}/modifier', [UserController::class, 'edit'])->whereNumber('id')->name('utilisateurs.edit');
            Route::put('/utilisateurs/{id}', [UserController::class, 'update'])->whereNumber('id')->name('utilisateurs.update');
            Route::post('/utilisateurs/{id}/toggle-status', [UserController::class, 'toggleStatus'])->whereNumber('id')->name('utilisateurs.toggle-status');
        });

        Route::delete('/utilisateurs/{id}', [UserController::class, 'destroy'])
            ->whereNumber('id')
            ->middleware('sicore.permission:administration.users.delete')
            ->name('utilisateurs.destroy');

        // Rôles et types de rôles
        Route::prefix('roles')->name('admin.roles.')->group(function (): void {
            Route::middleware('sicore.permission:administration.roles.manage')->group(function (): void {
                Route::get('/create', [RoleController::class, 'create'])->name('create');
                Route::post('/', [RoleController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [RoleController::class, 'edit'])->name('edit');
                Route::put('/{id}', [RoleController::class, 'update'])->name('update');
                Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
                Route::put('/{id}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('syncPermissions');
            });

            Route::middleware('sicore.permission:administration.roles.read')->group(function (): void {
                Route::get('/', [RoleController::class, 'index'])->name('index');
                Route::get('/{id}', [RoleController::class, 'show'])->name('show');
                Route::get('/{id}/permissions', [RoleController::class, 'permissions'])->name('permissions');
            });
        });

        Route::prefix('type-roles')->name('admin.type-roles.')->group(function (): void {
            Route::middleware('sicore.permission:administration.roles.manage')->group(function (): void {
                Route::get('/create', [TypeRoleController::class, 'create'])->name('create');
                Route::post('/', [TypeRoleController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [TypeRoleController::class, 'edit'])->name('edit');
                Route::put('/{id}', [TypeRoleController::class, 'update'])->name('update');
                Route::delete('/{id}', [TypeRoleController::class, 'destroy'])->name('destroy');
            });

            Route::get('/', [TypeRoleController::class, 'index'])
                ->middleware('sicore.permission:administration.roles.read')
                ->name('index');
        });

        // Permissions
        Route::prefix('permissions')->name('admin.permissions.')->group(function (): void {
            Route::middleware('sicore.permission:administration.permissions.manage')->group(function (): void {
                Route::get('/create', [PermissionController::class, 'create'])->name('create');
                Route::post('/', [PermissionController::class, 'store'])->name('store');
                Route::get('/{id}/edit', [PermissionController::class, 'edit'])->name('edit');
                Route::put('/{id}', [PermissionController::class, 'update'])->name('update');
                Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('destroy');
            });

            Route::middleware('sicore.permission:administration.permissions.read')->group(function (): void {
                Route::get('/', [PermissionController::class, 'index'])->name('index');
                Route::get('/{id}', [PermissionController::class, 'show'])->name('show');
            });
        });
    });
