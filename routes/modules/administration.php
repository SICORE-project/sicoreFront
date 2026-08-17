<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| Module Administration
|--------------------------------------------------------------------------
*/


Route::middleware('sicore.auth')
    ->prefix('administration')
    ->group(function (): void {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::view('/utilisateurs', 'pages.administration.utilisateurs')
            ->name('utilisateurs.index');

        // Ces deux routes passent maintenant par les contrôleurs existants (index())
        // pour récupérer les rôles / permissions réels depuis l'API
        Route::get('/utilisateurs/profils-roles', [RoleController::class, 'index'])
            ->name('utilisateurs.profils-roles');

        Route::get('/utilisateurs/permissions', [PermissionController::class, 'index'])
            ->name('utilisateurs.permissions');

        // ===== RÔLES =====
        Route::prefix('roles')->name('admin.roles.')->group(function () {
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

        // ===== PERMISSIONS =====
        Route::prefix('permissions')->name('admin.permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::get('/create', [PermissionController::class, 'create'])->name('create');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::get('/{id}', [PermissionController::class, 'show'])->name('show'); 
            Route::get('/{id}/edit', [PermissionController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PermissionController::class, 'update'])->name('update');
            Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('destroy');
            Route::get('/sync', [PermissionController::class, 'sync'])->name('sync');
        });

    });