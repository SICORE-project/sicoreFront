<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Compatibilité avec les anciennes pages HTML du prototype
|--------------------------------------------------------------------------
*/

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
