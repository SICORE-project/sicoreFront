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
*/



Route::middleware('sicore.auth')
    ->group(function (): void {

        Route::get('/administration/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        /*
|--------------------------------------------------------------------------
|  USERS
|--------------------------------------------------------------------------
*/
        Route::get('/utilisateurs', [UserController::class, 'index'])
            ->name('utilisateurs.index');

        Route::get('/utilisateurs/nouveau', [UserController::class, 'create'])
            ->name('utilisateurs.create');

        Route::post('/utilisateurs', [UserController::class, 'store'])
            ->name('utilisateurs.store');

        Route::get('/utilisateurs/verifier-email', [UserController::class, 'checkEmail'])
            ->name('utilisateurs.check-email');

        Route::get('/utilisateurs/ias', [UserController::class, 'iaOptions'])
            ->name('utilisateurs.ia-options');

        Route::get('/utilisateurs/profils-roles', [RoleController::class, 'index'])
            ->name('utilisateurs.profils-roles');

        Route::get('/utilisateurs/permissions', [PermissionController::class, 'index'])
            ->name('utilisateurs.permissions');

        Route::get('/utilisateurs/structures', fn () => redirect()->route('parametres.structures-organisationnelles.index'));
        Route::get('/utilisateurs/{id}', [UserController::class, 'show'])->whereNumber('id')->name('utilisateurs.show');
        Route::get('/utilisateurs/{id}/modifier', [UserController::class, 'edit'])->whereNumber('id')->name('utilisateurs.edit');
        Route::put('/utilisateurs/{id}', [UserController::class, 'update'])->whereNumber('id')->name('utilisateurs.update');
        Route::delete('/utilisateurs/{id}', [UserController::class, 'destroy'])->whereNumber('id')->name('utilisateurs.destroy');
        Route::post('/utilisateurs/{id}/toggle-status', [UserController::class, 'toggleStatus'])->whereNumber('id')->name('utilisateurs.toggle-status');
        Route::prefix('roles')->name('admin.roles.')->group(function (): void {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::get('/{id}', [RoleController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RoleController::class, 'update'])->name('update');
            Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/permissions', [RoleController::class, 'permissions'])->name('permissions');
            Route::put('/{id}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('syncPermissions');
        });

        Route::prefix('type-roles')->name('admin.type-roles.')->group(function (): void {
            Route::get('/', [TypeRoleController::class, 'index'])->name('index');
            Route::get('/create', [TypeRoleController::class, 'create'])->name('create');
            Route::post('/', [TypeRoleController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [TypeRoleController::class, 'edit'])->name('edit');
            Route::put('/{id}', [TypeRoleController::class, 'update'])->name('update');
            Route::delete('/{id}', [TypeRoleController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('permissions')->name('admin.permissions.')->group(function (): void {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::get('/create', [PermissionController::class, 'create'])->name('create');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::get('/sync', [PermissionController::class, 'sync'])->name('sync');
            Route::get('/{id}', [PermissionController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PermissionController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PermissionController::class, 'update'])->name('update');
            Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('destroy');
        });
    });
