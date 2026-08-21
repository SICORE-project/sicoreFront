<?php

use App\Http\Controllers\Parametrage\InstitutionFinanciereController;
use App\Http\Controllers\Parametrage\InspectionAcademieController;
use App\Http\Controllers\Parametrage\LieuServiceController;
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

        Route::get('/parametres/ia', [InspectionAcademieController::class, 'index'])
            ->name('parametres.ia.index');

        Route::view('/parametres/ia/nouvelle', 'pages.parametres.ia-create')
            ->name('parametres.ia.create');

        Route::get('/parametres/ia/{ia}', [InspectionAcademieController::class, 'show'])
            ->name('parametres.ia.show');

        Route::get('/parametres/lieux-service', [LieuServiceController::class, 'index'])
            ->name('parametres.lieux-service.index');

        Route::get('/parametres/institutions-financieres', [InstitutionFinanciereController::class, 'index'])
            ->name('parametres.institutions-financieres');
        Route::post('/parametres/institutions-financieres', [InstitutionFinanciereController::class, 'store'])
            ->name('parametres.institutions-financieres.store');
        Route::put('/parametres/institutions-financieres/{institution}', [InstitutionFinanciereController::class, 'update'])
            ->name('parametres.institutions-financieres.update');
        Route::patch('/parametres/institutions-financieres/{institution}/statut', [InstitutionFinanciereController::class, 'updateStatus'])
            ->name('parametres.institutions-financieres.status');
        Route::post('/parametres/comptes-bancaires-enseignants', [InstitutionFinanciereController::class, 'storeTeacherBankAccount'])
            ->name('parametres.comptes-bancaires-enseignants.store');

    });
