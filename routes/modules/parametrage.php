<?php

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

        Route::post('/parametres/syndicats', [SyndicatController::class, 'store'])
            ->name('parametres.syndicats.store');

        Route::get('/parametres/syndicats', [SyndicatController::class, 'index'])
            ->name('parametres.syndicats.index');

        Route::get('/parametres/syndicats/verifier-unicite', [SyndicatController::class, 'checkUniqueness'])
            ->name('parametres.syndicats.check-uniqueness');

    });
