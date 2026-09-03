<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module Bourses et aides
|--------------------------------------------------------------------------
*/

Route::middleware('sicore.auth')
    ->prefix('bourses')
    ->name('bourses.')
    ->group(function (): void {
        Route::view('/enregistrer-demande', 'pages.bourses.enregistrer-demande')->name('enregistrer-demande');
        Route::view('/valider-dossier', 'pages.bourses.valider-dossier')->name('valider-dossier');
        Route::view('/attribuer-aide', 'pages.bourses.attribuer-aide')->name('attribuer-aide');
    });
