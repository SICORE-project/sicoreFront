<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;

/*
|--------------------------------------------------------------------------
| Module Administration
|--------------------------------------------------------------------------
*/



Route::middleware('sicore.auth')
    ->prefix('administration')
    ->group(function (): void {

        /*
|--------------------------------------------------------------------------
|  USERS
|--------------------------------------------------------------------------
*/
        Route::view('/utilisateurs', 'pages.administration.utilisateurs')
            ->name('utilisateurs.index');

        Route::get('/utilisateurs/nouveau', [UserController::class, 'create'])
            ->name('utilisateurs.create');

        Route::post('/utilisateurs', [UserController::class, 'store'])
            ->name('utilisateurs.store');

        Route::view('/utilisateurs/profils-roles', 'pages.administration.profils-roles')
            ->name('utilisateurs.profils-roles');

        Route::view('/utilisateurs/permissions', 'pages.administration.permissions')
            ->name('utilisateurs.permissions');
    });
