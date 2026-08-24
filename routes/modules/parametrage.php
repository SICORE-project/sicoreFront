<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\StructureOrganisationnelleController;

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

        Route::prefix('structures-organisationnelles')->name('parametres.structures-organisationnelles.')->group(function (): void {
            Route::get('/', [StructureOrganisationnelleController::class, 'index'])->name('index');
            Route::post('/', [StructureOrganisationnelleController::class, 'store'])->name('store');
            Route::put('/{id}', [StructureOrganisationnelleController::class, 'update'])->whereNumber('id')->name('update');
            Route::delete('/{id}', [StructureOrganisationnelleController::class, 'destroy'])->whereNumber('id')->name('destroy');
        });

    });
