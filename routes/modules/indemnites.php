<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Indemnites\ConvocationsController;
use App\Http\Controllers\Indemnites\PiecesJustificativesController;

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

        // Modèle Word à remplir puis import du document rempli (une
        // convocation complète par document : infos + centres + membres).
        // Doit rester avant /convocations/{id} pour ne pas etre capturee par elle.
        Route::get('/convocations/modele-word', [ConvocationsController::class, 'telechargerModeleWord'])
            ->name('convocations.modele-word');

        Route::post('/convocations/import', [ConvocationsController::class, 'import'])
            ->name('convocations.import');

        Route::post('/convocations', [ConvocationsController::class, 'store'])
            ->name('convocations.store');

        // Suppression multiple depuis les cases a cocher de la liste.
        Route::delete('/convocations', [ConvocationsController::class, 'destroyMultiple'])
            ->name('convocations.destroy-multiple');

        Route::get('/convocations/{id}/modifier', [ConvocationsController::class, 'edit'])
            ->name('convocations.edit');

        Route::put('/convocations/{id}', [ConvocationsController::class, 'update'])
            ->name('convocations.update');

        Route::delete('/convocations/{id}', [ConvocationsController::class, 'destroy'])
            ->name('convocations.destroy');

        Route::post('/convocations/{id}/beneficiaires', [ConvocationsController::class, 'storeBeneficiaires'])
            ->name('convocations.beneficiaires.store');

        Route::put('/convocations/{id}/beneficiaires/{enseignantId}', [ConvocationsController::class, 'updateBeneficiaire'])
            ->name('convocations.beneficiaires.update');

        Route::delete('/convocations/{id}/beneficiaires/{enseignantId}', [ConvocationsController::class, 'destroyBeneficiaire'])
            ->name('convocations.beneficiaires.destroy');

        Route::post('/convocations/{id}/centres', [ConvocationsController::class, 'storeCentres'])
            ->name('convocations.centres.store');

        Route::put('/convocations/{id}/centres/{centreId}', [ConvocationsController::class, 'updateCentre'])
            ->name('convocations.centres.update');

        Route::delete('/convocations/{id}/centres/{centreId}', [ConvocationsController::class, 'destroyCentre'])
            ->name('convocations.centres.destroy');

        // Metiers d'UN centre (un centre peut en couvrir plusieurs, chacun
        // avec ses propres membres du jury).
        Route::post('/convocations/{id}/centres/{centreId}/metiers', [ConvocationsController::class, 'storeMetier'])
            ->name('convocations.metiers.store');

        Route::put('/convocations/{id}/centres/{centreId}/metiers/{metierId}', [ConvocationsController::class, 'updateMetier'])
            ->name('convocations.metiers.update');

        Route::delete('/convocations/{id}/centres/{centreId}/metiers/{metierId}', [ConvocationsController::class, 'destroyMetier'])
            ->name('convocations.metiers.destroy');

        // Fiche detail / modification d'UN centre precis (une ligne de la
        // liste = un centre) : doivent rester avant /convocations/{id} et
        // /convocations/{id}/modifier pour ne pas etre capturees par elles.
        Route::get('/convocations/{id}/centres/{centreId}/modifier', [ConvocationsController::class, 'editCentre'])
            ->name('convocations.centres.edit');

        Route::get('/convocations/{id}/centres/{centreId}', [ConvocationsController::class, 'showCentre'])
            ->name('convocations.centres.show');

        Route::get('/convocations/{id}/pdf', [ConvocationsController::class, 'downloadPdf'])
            ->name('convocations.pdf');

        // Envoi de la convocation aux beneficiaires par e-mail, relance des
        // envois en echec, et suivi/historique - doivent rester avant
        // /convocations/{id} pour ne pas etre capturees par elle.
        Route::post('/convocations/{id}/envoyer', [ConvocationsController::class, 'envoyer'])
            ->name('convocations.envoyer');

        Route::post('/convocations/{id}/relancer', [ConvocationsController::class, 'relancer'])
            ->name('convocations.relancer');

        Route::get('/convocations/{id}/suivi', [ConvocationsController::class, 'suivi'])
            ->name('convocations.suivi');

        Route::get('/convocations/{id}', [ConvocationsController::class, 'show'])
            ->name('convocations.show');

        Route::get('/pieces-justificatives', [PiecesJustificativesController::class, 'index'])
            ->name('pieces-justificatives');

        Route::post('/pieces-justificatives/deposer', [PiecesJustificativesController::class, 'deposerPieces'])
            ->name('pieces-justificatives.deposer');

        Route::get('/pieces-justificatives/{id}/telecharger', [PiecesJustificativesController::class, 'download'])
            ->name('pieces-justificatives.telecharger');

    });

  /*
|--------------------------------------------------------------------------
| Module Bourses
|--------------------------------------------------------------------------
*/
