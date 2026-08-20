<?php

use App\Http\Controllers\Parametrage\DiplomesController;
use Illuminate\Support\Facades\Route;

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

        Route::middleware('diplomes.manage')->group(function (): void {
            Route::get('/parametres/diplomes', [DiplomesController::class, 'index'])
                ->name('parametres.diplomes.index');

            Route::post('/parametres/diplomes', [DiplomesController::class, 'store'])
                ->name('parametres.diplomes.store');

            Route::put('/parametres/diplomes/{diplome}', [DiplomesController::class, 'update'])
                ->name('parametres.diplomes.update');

            Route::delete('/parametres/diplomes/{diplome}', [DiplomesController::class, 'destroy'])
                ->name('parametres.diplomes.destroy');
        });

    });
