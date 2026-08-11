<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Indemnites\ConvocationsController;

/*
|--------------------------------------------------------------------------
| Module Indemnités
|--------------------------------------------------------------------------
*/

Route::middleware('sicore.auth')
    ->prefix('indemnites')
    ->name('indemnites.')
    ->group(function (): void {

        Route::get('/convocations', [ConvocationsController::class, 'index'])
            ->name('convocations');

        Route::get('/convocations/nouvelle', [ConvocationsController::class, 'create'])
            ->name('convocations.create');

        // Recherche AJAX d'enseignants pour le tableau de beneficiaires
        // (doit rester avant /convocations/{id} pour ne pas etre capturee par elle).
        Route::get('/convocations/enseignants/rechercher', [ConvocationsController::class, 'rechercherEnseignants'])
            ->name('convocations.enseignants.rechercher');

        Route::post('/convocations', [ConvocationsController::class, 'store'])
            ->name('convocations.store');

        Route::get('/convocations/{id}/modifier', [ConvocationsController::class, 'edit'])
            ->name('convocations.edit');

        Route::put('/convocations/{id}', [ConvocationsController::class, 'update'])
            ->name('convocations.update');

        Route::delete('/convocations/{id}', [ConvocationsController::class, 'destroy'])
            ->name('convocations.destroy');

        Route::get('/convocations/{id}/suivi', [ConvocationsController::class, 'suivi'])
            ->name('convocations.suivi');

        Route::post('/convocations/{id}/beneficiaires', [ConvocationsController::class, 'storeBeneficiaires'])
            ->name('convocations.beneficiaires.store');

        Route::post('/convocations/{id}/fichier', [ConvocationsController::class, 'storeFichier'])
            ->name('convocations.fichier.store');

        Route::post('/convocations/{id}/pdf', [ConvocationsController::class, 'genererPdf'])
            ->name('convocations.pdf.generer');

        Route::get('/convocations/{id}/pdf/telecharger', [ConvocationsController::class, 'telechargerPdf'])
            ->name('convocations.pdf.telecharger');

        Route::post('/convocations/{id}/envoyer', [ConvocationsController::class, 'envoyer'])
            ->name('convocations.envoyer');

        Route::post('/convocations/{id}/relancer', [ConvocationsController::class, 'relancer'])
            ->name('convocations.relancer');

        Route::get('/convocations/{id}', [ConvocationsController::class, 'show'])
            ->name('convocations.show');

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
    ->group(function (): void {

        Route::view('/enregistrer-demande', 'pages.bourses.enregistrer-demande')
            ->name('bourses.enregistrer-demande');

        Route::view('/valider-dossier', 'pages.bourses.valider-dossier')
            ->name('bourses.valider-dossier');

        Route::view('/attribuer-aide', 'pages.bourses.attribuer-aide')
            ->name('bourses.attribuer-aide');

    });