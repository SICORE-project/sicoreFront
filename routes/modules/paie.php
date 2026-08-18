<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module Paie
|--------------------------------------------------------------------------
*/

Route::middleware('sicore.auth')
    ->prefix('paie')
    ->name('paie.')
    ->group(function (): void {

        Route::view('/etats-presence', 'pages.paie.etats-presence')
            ->name('etats-presence');

        Route::view('/avance-tabaski', 'pages.paie.avance-tabaski')
            ->name('avance-tabaski');

        Route::view('/retenue-tabaski', 'pages.paie.retenue-tabaski')
            ->name('retenue-tabaski');

        Route::view('/retenues-rappel', 'pages.paie.retenues-rappel')
            ->name('retenues-rappel');

        Route::view('/exemptions', 'pages.paie.exemptions')
            ->name('exemptions');

        Route::view('/travaux-periodiques', 'pages.paie.travaux-periodiques')
            ->name('travaux-periodiques');

        Route::view('/recap-banque', 'pages.paie.recap-banque')
            ->name('recap-banque');

        Route::view('/cotisations-sociales', 'pages.paie.cotisations-sociales')
            ->name('cotisations-sociales');

        Route::view('/etat-salaires', 'pages.paie.etat-salaires')
            ->name('etat-salaires');

        Route::view('/elements-saisie-dashboard', 'pages.paie.elements-saisie-dashboard')
            ->name('elements-saisie-dashboard');

        Route::view('/generee-ief', 'pages.paie.generee-ief')
            ->name('generee-ief');

        Route::view('/fermeture-periode', 'pages.paie.fermeture-periode')
            ->name('fermeture-periode');

        Route::view('/edition-salaires-banque', 'pages.paie.edition-salaires-banque')
            ->name('edition-salaires-banque');

        Route::view('/bulletins', 'pages.paie.bulletins')
            ->name('bulletins');

        Route::view('/effectifs-corps', 'pages.paie.effectifs-corps')
            ->name('effectifs-corps');

        Route::view('/non-generee', 'pages.paie.non-generee')
            ->name('non-generee');

        Route::view('/sommes-percues', 'pages.paie.sommes-percues')
            ->name('sommes-percues');

    });


/*
|--------------------------------------------------------------------------
| Module Crédits
|--------------------------------------------------------------------------
*/

Route::middleware('sicore.auth')
    ->prefix('credits')
    ->name('credits.')
    ->group(function (): void {

        Route::view('/delegation', 'pages.credits.delegation')
            ->name('delegation');

        Route::view('/edition-delegations', 'pages.credits.edition-delegations')
            ->name('edition-delegations');

        Route::view('/edition-engagements', 'pages.credits.edition-engagements')
            ->name('edition-engagements');

    });