<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module Indemnités
|--------------------------------------------------------------------------
*/

Route::middleware('sicore.auth')
    ->prefix('indemnites')
    ->name('indemnites.')
    ->group(function (): void {

        Route::view('/convocations', 'pages.indemnites.convocations')
            ->name('convocations');

        Route::view('/services-faits', 'pages.indemnites.services-faits')
            ->name('services-faits');

        Route::view('/pieces-justificatives', 'pages.indemnites.pieces-justificatives')
            ->name('pieces-justificatives');

        Route::view('/accuses-reception', 'pages.indemnites.accuses-reception')
            ->name('accuses-reception');

        Route::view('/calcul', 'pages.indemnites.calcul')
            ->name('calcul');

        Route::view('/frais-deplacement', 'pages.indemnites.frais-deplacement')
            ->name('frais-deplacement');

        Route::view('/etats-paie', 'pages.indemnites.etats-paie')
            ->name('etats-paie');

    });


/*
|--------------------------------------------------------------------------
| Module Bourses
|--------------------------------------------------------------------------
*/

Route::middleware('sicore.auth')
    ->prefix('bourses')
    ->name('bourses.')
    ->group(function (): void {

        Route::view('/enregistrer-demande', 'pages.bourses.enregistrer-demande')
            ->name('enregistrer-demande');

        Route::view('/valider-dossier', 'pages.bourses.valider-dossier')
            ->name('valider-dossier');

        Route::view('/attribuer-aide', 'pages.bourses.attribuer-aide')
            ->name('attribuer-aide');

    });