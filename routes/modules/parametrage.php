<?php

use App\Http\Controllers\Parametrage\DiplomesController;
use App\Http\Controllers\Parametrage\SyndicatController;
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
        Route::post('/parametres/syndicats', [SyndicatController::class, 'store'])
            ->name('parametres.syndicats.store');

        Route::get('/parametres/syndicats', [SyndicatController::class, 'index'])
            ->name('parametres.syndicats.index');

        Route::get('/parametres/syndicats/verifier-unicite', [SyndicatController::class, 'checkUniqueness'])
            ->name('parametres.syndicats.check-uniqueness');

        Route::get('/parametres/syndicats/options-association', [SyndicatController::class, 'associationOptions'])
            ->name('parametres.syndicats.association-options');

        Route::put('/parametres/syndicats/{id}', [SyndicatController::class, 'update'])
            ->whereNumber('id')
            ->name('parametres.syndicats.update');

        Route::delete('/parametres/syndicats/{id}', [SyndicatController::class, 'destroy'])
            ->whereNumber('id')
            ->name('parametres.syndicats.destroy');

    });
