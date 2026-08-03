<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Route::middleware('guest')->group(function (): void {
//     Route::get('/', [AuthController::class, 'showLogin'])->name('login');
//     Route::post('/login', [AuthController::class, 'login'])
//         ->middleware('throttle:6,1')
//         ->name('login.submit');
// });

Route::middleware('guest')->group(function (): void {

        Route::get('/login', [
            AuthController::class,
            'showLogin'
        ])
        ->name('login');


        Route::post('/login', [
            AuthController::class,
            'login'
        ])
        ->middleware('throttle:6,1')
        ->name('login.submit');


        // Optionnel : garder l'ancien accès /
        Route::redirect('/', '/login');
});

Route::middleware('sicore.auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::view('/dashboard', 'pages.dashboard.index')->name('dashboard');

    Route::view('/enseignants', 'pages.enseignants.index')->name('enseignants.index');
    Route::view('/enseignants/nouveau', 'pages.enseignants.create')->name('enseignants.create');

    Route::view('/parametres', 'pages.parametres.index')->name('parametres.index');
    Route::view('/parametres/ief', 'pages.parametres.ief')->name('parametres.ief');

    Route::prefix('paie')->name('paie.')->group(function (): void {
        Route::view('/etats-presence', 'pages.paie.etats-presence')->name('etats-presence');
        Route::view('/avance-tabaski', 'pages.paie.avance-tabaski')->name('avance-tabaski');
        Route::view('/retenue-tabaski', 'pages.paie.retenue-tabaski')->name('retenue-tabaski');
        Route::view('/retenues-rappel', 'pages.paie.retenues-rappel')->name('retenues-rappel');
        Route::view('/exemptions', 'pages.paie.exemptions')->name('exemptions');
        Route::view('/travaux-periodiques', 'pages.paie.travaux-periodiques')->name('travaux-periodiques');
        Route::view('/recap-banque', 'pages.paie.recap-banque')->name('recap-banque');
        Route::view('/cotisations-sociales', 'pages.paie.cotisations-sociales')->name('cotisations-sociales');
        Route::view('/etat-salaires', 'pages.paie.etat-salaires')->name('etat-salaires');
        Route::view('/elements-saisie-dashboard', 'pages.paie.elements-saisie-dashboard')->name('elements-saisie-dashboard');
        Route::view('/generee-ief', 'pages.paie.generee-ief')->name('generee-ief');
        Route::view('/fermeture-periode', 'pages.paie.fermeture-periode')->name('fermeture-periode');
        Route::view('/edition-salaires-banque', 'pages.paie.edition-salaires-banque')->name('edition-salaires-banque');
        Route::view('/bulletins', 'pages.paie.bulletins')->name('bulletins');
        Route::view('/effectifs-corps', 'pages.paie.effectifs-corps')->name('effectifs-corps');
        Route::view('/non-generee', 'pages.paie.non-generee')->name('non-generee');
        Route::view('/sommes-percues', 'pages.paie.sommes-percues')->name('sommes-percues');
    });

    Route::prefix('credits')->name('credits.')->group(function (): void {
        Route::view('/delegation', 'pages.credits.delegation')->name('delegation');
        Route::view('/edition-delegations', 'pages.credits.edition-delegations')->name('edition-delegations');
        Route::view('/edition-engagements', 'pages.credits.edition-engagements')->name('edition-engagements');
    });

    Route::prefix('indemnites')->name('indemnites.')->group(function (): void {
        Route::view('/convocations', 'pages.indemnites.convocations')->name('convocations');
        Route::view('/services-faits', 'pages.indemnites.services-faits')->name('services-faits');
        Route::view('/pieces-justificatives', 'pages.indemnites.pieces-justificatives')->name('pieces-justificatives');
        Route::view('/accuses-reception', 'pages.indemnites.accuses-reception')->name('accuses-reception');
        Route::view('/calcul', 'pages.indemnites.calcul')->name('calcul');
        Route::view('/frais-deplacement', 'pages.indemnites.frais-deplacement')->name('frais-deplacement');
        Route::view('/etats-paie', 'pages.indemnites.etats-paie')->name('etats-paie');
    });

    Route::prefix('bourses')->name('bourses.')->group(function (): void {
        Route::view('/enregistrer-demande', 'pages.bourses.enregistrer-demande')->name('enregistrer-demande');
        Route::view('/valider-dossier', 'pages.bourses.valider-dossier')->name('valider-dossier');
        Route::view('/attribuer-aide', 'pages.bourses.attribuer-aide')->name('attribuer-aide');
    });

    Route::view('/utilisateurs', 'pages.administration.utilisateurs')->name('utilisateurs.index');
    Route::view('/utilisateurs/profils-roles', 'pages.administration.profils-roles')->name('utilisateurs.profils-roles');
    Route::view('/utilisateurs/permissions', 'pages.administration.permissions')->name('utilisateurs.permissions');

});

// Compatibilité avec les anciennes pages HTML du prototype.
$legacyRedirects = [
    '/index.html' => '/',
    '/dashboard.html' => '/dashboard',
    '/enseignant-dashboard.html' => '/enseignants',
    '/enseignant-form.html' => '/enseignants/nouveau',
    '/parametres.html' => '/parametres',
    '/ief.html' => '/parametres/ief',
    '/paie-etats-presence.html' => '/paie/etats-presence',
    '/paie-avance-tabaski.html' => '/paie/avance-tabaski',
    '/paie-retenue-tabaski.html' => '/paie/retenue-tabaski',
    '/paie-retenues-rappel.html' => '/paie/retenues-rappel',
    '/paie-exemptions.html' => '/paie/exemptions',
    '/paie-travaux-periodiques.html' => '/paie/travaux-periodiques',
    '/paie-recap-banque.html' => '/paie/recap-banque',
    '/paie-cotisations-sociales.html' => '/paie/cotisations-sociales',
    '/paie-etat-salaires.html' => '/paie/etat-salaires',
    '/paie-elements-saisie-dashboard.html' => '/paie/elements-saisie-dashboard',
    '/paie-generee-ief.html' => '/paie/generee-ief',
    '/paie-fermeture-periode.html' => '/paie/fermeture-periode',
    '/paie-edition-salaires-banque.html' => '/paie/edition-salaires-banque',
    '/paie-bulletins.html' => '/paie/bulletins',
    '/paie-effectifs-corps.html' => '/paie/effectifs-corps',
    '/paie-non-generee.html' => '/paie/non-generee',
    '/paie-sommes-percues.html' => '/paie/sommes-percues',
    '/credit-delegation.html' => '/credits/delegation',
    '/credit-edition-delegations.html' => '/credits/edition-delegations',
    '/credit-edition-engagements.html' => '/credits/edition-engagements',
    '/indemnites-convocations.html' => '/indemnites/convocations',
    '/indemnites-services-faits.html' => '/indemnites/services-faits',
    '/indemnites-pieces-justificatives.html' => '/indemnites/pieces-justificatives',
    '/indemnites-accuses-reception.html' => '/indemnites/accuses-reception',
    '/indemnites-calcul.html' => '/indemnites/calcul',
    '/indemnites-frais-deplacement.html' => '/indemnites/frais-deplacement',
    '/indemnites-etats-paie.html' => '/indemnites/etats-paie',
    '/bourses-enregistrer-demande.html' => '/bourses/enregistrer-demande',
    '/bourses-valider-dossier.html' => '/bourses/valider-dossier',
    '/bourses-attribuer-aide.html' => '/bourses/attribuer-aide',
    '/utilisateurs.html' => '/utilisateurs',
    '/profils-roles.html' => '/utilisateurs/profils-roles',
    '/permissions.html' => '/utilisateurs/permissions',
];

foreach ($legacyRedirects as $from => $to) {
    Route::redirect($from, $to, 301);
}
