<?php

use App\Http\Controllers\Systeme\ImportDonneesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module Système (profil Admin System)
|--------------------------------------------------------------------------
*/

Route::middleware(['sicore.auth', 'sicore.module:systeme.import'])
    ->prefix('systeme/import')
    ->name('systeme.import')
    ->group(function (): void {
        Route::get('/{entite}/modele', [ImportDonneesController::class, 'modele'])->name('.modele');
        Route::post('/', [ImportDonneesController::class, 'traiter'])->name('.traiter');
        Route::get('/', [ImportDonneesController::class, 'index']);
    });
