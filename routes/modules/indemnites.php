<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Indemnites\ConvocationsController;
use App\Http\Controllers\Indemnites\EtatPaieIndemnitesController;
use App\Http\Controllers\Indemnites\FraisDeplacementController;
use App\Http\Controllers\Indemnites\IndemniteCorrectionController;
use App\Http\Controllers\Indemnites\IndemniteSurveillanceController;
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

        // AJAX partagee par les panneaux de filtres Session/Objet/Metier/
        // Centre de Convocations, Frais de deplacement, Calcul,
        // Calcul-surveillance et Pieces justificatives (demande
        // utilisatrice : "quand je selectionne un objet le select suivant
        // doit etre relatif", instantane sans recharger la page, meme
        // principe que indemnites.etats-paie.centres) — doit rester avant
        // /convocations pour ne pas dependre de son groupe de routes.
        Route::get('/filtres-options', [ConvocationsController::class, 'filtresOptionsJson'])
            ->name('filtres-options');

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

        Route::view('/services-faits', 'pages.indemnites.services-faits')
            ->name('services-faits');

        // Doivent rester avant /pieces-justificatives (route sans wildcard,
        // pas de conflit ici) mais AVANT le GET ci-dessous quand meme, par
        // coherence avec les autres blocs de ce fichier (fixe avant {id}).
        Route::post('/pieces-justificatives/deposer', [PiecesJustificativesController::class, 'deposer'])
            ->name('pieces-justificatives.deposer');

        // Bouton "Modifier" du tableau — meme formulaire complet que
        // "Ajouter une pièce" (demande utilisatrice), mais les 5 fichiers
        // sont optionnels : seuls ceux fournis remplacent la piece
        // existante.
        Route::post('/pieces-justificatives/modifier', [PiecesJustificativesController::class, 'modifier'])
            ->name('pieces-justificatives.modifier');

        Route::get('/pieces-justificatives/{id}/telecharger', [PiecesJustificativesController::class, 'telecharger'])
            ->name('pieces-justificatives.telecharger');

        // Remplace l'ancien Route::view() (page statique sans donnees) qui
        // provoquait "Undefined variable $filtreActif" : la vue existante
        // (~40 Ko, ecrite par un coequipier) attendait deja $filtreActif,
        // $optionsFiltres, $membres, $stats, $convocations, mais aucun
        // controleur ne les fournissait encore.
        Route::get('/pieces-justificatives', [PiecesJustificativesController::class, 'index'])
            ->name('pieces-justificatives');

        Route::view('/accuses-reception', 'pages.indemnites.accuses-reception')
            ->name('accuses-reception');

        // "Calcul des indemnités" = indemnités de correction (copies
        // corrigées x taux par copie) — demande utilisatrice : integrer la
        // maquette dans cette vue plutôt que de créer un module séparé.
        // /groupe doit rester avant /calcul (segment fixe, pas de wildcard,
        // aucun risque de conflit ici, mais coherence avec le reste du fichier).
        Route::get('/calcul/groupe', [IndemniteCorrectionController::class, 'calculGroupe'])
            ->name('calcul.groupe');

        Route::post('/calcul/groupe', [IndemniteCorrectionController::class, 'storeGroupe'])
            ->name('calcul.groupe.store');

        Route::get('/calcul', [IndemniteCorrectionController::class, 'index'])
            ->name('calcul');

        // "Indemnité de surveillance" = heures surveillées x tarif horaire,
        // meme plan que "Calcul des indemnités" (indemnite de correction)
        // juste au-dessus. /groupe avant /calcul-surveillance meme raison.
        Route::get('/calcul-surveillance/groupe', [IndemniteSurveillanceController::class, 'calculGroupe'])
            ->name('calcul-surveillance.groupe');

        Route::post('/calcul-surveillance/groupe', [IndemniteSurveillanceController::class, 'storeGroupe'])
            ->name('calcul-surveillance.groupe.store');

        Route::get('/calcul-surveillance', [IndemniteSurveillanceController::class, 'index'])
            ->name('calcul-surveillance');

        Route::get('/frais-deplacement', [FraisDeplacementController::class, 'index'])
            ->name('frais-deplacement');

        Route::get('/frais-deplacement/nouvelle', [FraisDeplacementController::class, 'create'])
            ->name('frais-deplacement.create');

        // Calcul du montant pour tous les membres éligibles d'une même
        // convocation en une seule page (au lieu d'une fiche à la fois) —
        // doit rester avant /frais-deplacement/{id} (segment fixe).
        Route::get('/frais-deplacement/calcul-groupe', [FraisDeplacementController::class, 'calculGroupe'])
            ->name('frais-deplacement.calcul-groupe');

        Route::post('/frais-deplacement/calcul-groupe', [FraisDeplacementController::class, 'storeGroupe'])
            ->name('frais-deplacement.calcul-groupe.store');

        Route::post('/frais-deplacement', [FraisDeplacementController::class, 'store'])
            ->name('frais-deplacement.store');

        Route::get('/frais-deplacement/{id}', [FraisDeplacementController::class, 'show'])
            ->name('frais-deplacement.show');

        // CRUD de la fiche elle-même (demande utilisatrice : "le CRUD de la
        // fiche") — doivent rester avant aucune autre route wildcard
        // conflictuelle : /modifier a un segment de plus, pas de conflit
        // avec /frais-deplacement/{id} ci-dessus.
        Route::get('/frais-deplacement/{id}/modifier', [FraisDeplacementController::class, 'edit'])
            ->name('frais-deplacement.edit');

        Route::put('/frais-deplacement/{id}', [FraisDeplacementController::class, 'update'])
            ->name('frais-deplacement.update');

        Route::delete('/frais-deplacement/{id}', [FraisDeplacementController::class, 'destroy'])
            ->name('frais-deplacement.destroy');

        // Téléchargement de la fiche en PDF.
        Route::get('/frais-deplacement/{id}/telecharger', [FraisDeplacementController::class, 'telechargerPdf'])
            ->name('frais-deplacement.pdf');

        // Étape "Calcul" directement depuis la fiche (demande utilisatrice :
        // "gère-moi juste le show du fiche") — pas de page de liste séparée
        // pour l'instant, le formulaire de lignes de frais vit sur le show.
        Route::post('/frais-deplacement/{id}/calculer', [FraisDeplacementController::class, 'calculer'])
            ->name('frais-deplacement.calculer');

        // Téléchargement d'une pièce jointe (recto ou verso) de la fiche.
        Route::get('/frais-deplacement/{id}/justificatifs/{justificatifId}/telecharger', [FraisDeplacementController::class, 'telechargerJustificatif'])
            ->name('frais-deplacement.justificatifs.telecharger');

        // Ajout/remplacement et suppression d'une pièce jointe depuis le
        // détail de la fiche (demande utilisatrice : "télécharger,
        // modifier, supprimer").
        Route::post('/frais-deplacement/{id}/justificatifs/remplacer', [FraisDeplacementController::class, 'remplacerJustificatif'])
            ->name('frais-deplacement.justificatifs.remplacer');

        Route::delete('/frais-deplacement/{id}/justificatifs/{justificatifId}', [FraisDeplacementController::class, 'supprimerJustificatif'])
            ->name('frais-deplacement.justificatifs.destroy');

        // AJAX : centres des convocations correspondant a l'objet/session
        // choisis sur le formulaire — doit rester avant /etats-paie
        // ci-dessous par coherence avec le reste du fichier (segment fixe
        // avant route racine), meme si aucun risque reel de conflit ici.
        Route::get('/etats-paie/centres', [EtatPaieIndemnitesController::class, 'centresPourObjetSession'])
            ->name('etats-paie.centres');

        // AJAX : membres associes au type d'indemnite choisi.
        Route::get('/etats-paie/membres', [EtatPaieIndemnitesController::class, 'membresPourFiltre'])
            ->name('etats-paie.membres');

        // AJAX : coordonnees bancaires d'un membre (pre-remplissage de la
        // modale "Ajouter etat de paie").
        Route::get('/etats-paie/membres/{id}/rib', [EtatPaieIndemnitesController::class, 'ribMembre'])
            ->name('etats-paie.membres.rib');

        // AJAX : etats de paie deja crees pour le filtre courant ("Voir etat").
        Route::get('/etats-paie/existants', [EtatPaieIndemnitesController::class, 'etatsExistants'])
            ->name('etats-paie.existants');

        // Genere/telecharge la fiche PDF officielle a partir du filtre courant.
        Route::get('/etats-paie/fiche-pdf', [EtatPaieIndemnitesController::class, 'genererFichePdf'])
            ->name('etats-paie.fiche-pdf');

        Route::get('/etats-paie', [EtatPaieIndemnitesController::class, 'index'])
            ->name('etats-paie');

        Route::post('/etats-paie', [EtatPaieIndemnitesController::class, 'storeEtatPaie'])
            ->name('etats-paie.store');

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