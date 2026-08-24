<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LieuServiceController;

/*
|--------------------------------------------------------------------------
| Module Paramétrage
|--------------------------------------------------------------------------
*/

Route::middleware('sicore.auth')
    ->prefix('parametrage')
    ->group(function (): void {

        /*
        |--------------------------------------------------------------------------
        | Gestion des enseignants
        |--------------------------------------------------------------------------
        */

        Route::view('/enseignants', 'pages.enseignants.index')
            ->name('enseignants.index');

        Route::view('/enseignants/nouveau', 'pages.enseignants.create')
            ->name('enseignants.create');

        /*
        |--------------------------------------------------------------------------
        | Paramètres
        |--------------------------------------------------------------------------
        */

        Route::view('/parametres', 'pages.parametres.index')
            ->name('parametres.index');

        Route::view('/parametres/ief', 'pages.parametres.ief')
            ->name('parametres.ief');

        Route::prefix('lieux-service')->name('parametres.lieux-service.')->group(function (): void {
            Route::get('/', [LieuServiceController::class, 'index'])->name('index');
            Route::post('/', [LieuServiceController::class, 'store'])->name('store');
            Route::put('/{id}', [LieuServiceController::class, 'update'])->whereNumber('id')->name('update');
            Route::delete('/{id}', [LieuServiceController::class, 'destroy'])->whereNumber('id')->name('destroy');
        });

    });
