<?php

namespace App\Http\Controllers\Indemnites;

use App\Http\Controllers\Controller;
use App\Services\Api\Indemnites\ConvocationService;
use App\Services\Api\Indemnites\FraisDeplacementService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * "Fiche de déplacement" — voir FraisDeplacementController (back) pour la
 * règle métier (montant vacataire fixe / indice fonctionnaire / montant
 * libre contractuel, dossier de pièces justificatives complet obligatoire).
 *
 * Page reorganisee pour suivre le meme plan que "Pieces justificatives"
 * (PiecesJustificativesController) : cartes de stats, filtres
 * session/objet/centre auto-soumis (memes options, ConvocationService::
 * optionsFiltres()), tableau cache tant qu'aucun filtre n'est choisi.
 */
class FraisDeplacementController extends Controller
{
    public function __construct(
        protected FraisDeplacementService $fraisDeplacement,
        protected ConvocationService $convocations
    ) {}

    /**
     * Une ligne par bénéficiaire au dossier complet (seuls ceux-là peuvent
     * recevoir une fiche — cf. demande utilisatrice "seuls ceux qui ont
     * pièces complet s'affichent"), pour toutes les convocations
     * correspondant au filtre session/objet/centre choisi.
     */
    public function index(Request $request): View
    {
        $filtres = array_filter([
            'session' => $request->query('session'),
            'objet' => $request->query('objet'),
            'centre' => $request->query('centre'),
        ]);

        $filtreActif = count($filtres) > 0;

        $optionsFiltres = $this->optionsFiltresPourVue();

        $lignes = [];
        $stats = [
            'total_eligibles' => 0,
            'fiches_creees' => 0,
            'en_attente' => 0,
            'fiches_rejetees' => 0,
        ];

        // Paginator vide par defaut (filtre inactif : la vue n'affiche pas
        // le tableau) — evite tout "Call to a member function on null".
        $convocations = new LengthAwarePaginator([], 0, 10, 1, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        if ($filtreActif) {
            $resultat = $this->convocations->liste(array_merge($filtres, [
                'per_page' => 10,
                'page' => $request->query('page', 1),
            ]));

            if (! $resultat['success']) {
                session()->flash('error', $resultat['message'] ?? 'Impossible de charger les fiches de déplacement.');
            }

            $meta = $resultat['success'] ? ($resultat['data'] ?? []) : [];
            $items = $meta['data'] ?? [];

            $convocations = new LengthAwarePaginator(
                $items,
                $meta['total'] ?? count($items),
                $meta['per_page'] ?? 10,
                $meta['current_page'] ?? (int) $request->query('page', 1),
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            $lignes = $this->construireLignes($items);
            $stats = $this->calculerStats($lignes);
        }

        return view('pages.indemnites.frais-deplacement.index', [
            'filtreActif' => $filtreActif,
            'optionsFiltres' => $optionsFiltres,
            'lignes' => $lignes,
            'stats' => $stats,
            'convocations' => $convocations,
        ]);
    }

    private function optionsFiltresPourVue(): array
    {
        $resultat = $this->convocations->optionsFiltres();

        $vide = ['objets' => [], 'sessions' => [], 'centres' => []];

        return $resultat['success'] ? array_merge($vide, $resultat['data'] ?? []) : $vide;
    }

    /**
     * Aplatit les convocations de la page en une ligne par bénéficiaire au
     * dossier complet — un appel back par convocation (beneficiairesEligibles),
     * meme limitation "page courante" deja acceptee sur Pieces justificatives.
     */
    private function construireLignes(array $items): array
    {
        $lignes = [];

        foreach ($items as $item) {
            $convocationId = $item['id'] ?? null;

            if (! $convocationId) {
                continue;
            }

            $eligiblesResultat = $this->fraisDeplacement->beneficiairesEligibles($convocationId);
            $complets = $eligiblesResultat['success'] ? ($eligiblesResultat['data']['complets'] ?? []) : [];

            foreach ($complets as $beneficiaire) {
                $lignes[] = [
                    'convocation_id' => $convocationId,
                    'objet' => $item['objet'] ?? null,
                    'session' => $item['session'] ?? null,
                    'beneficiaire_id' => $beneficiaire['id'] ?? null,
                    'nom' => $beneficiaire['nom'] ?? null,
                    'prenom' => $beneficiaire['prenom'] ?? null,
                    'matricule' => $beneficiaire['matricule'] ?? null,
                    'categorie_personnel' => $beneficiaire['categorie_personnel'] ?? null,
                    'fiche_deplacement_id' => $beneficiaire['fiche_deplacement_id'] ?? null,
                    'fiche_statut' => $beneficiaire['fiche_statut'] ?? null,
                ];
            }
        }

        return $lignes;
    }

    private function calculerStats(array $lignes): array
    {
        $ficheCreees = 0;
        $enAttente = 0;
        $rejetees = 0;

        foreach ($lignes as $ligne) {
            if (empty($ligne['fiche_deplacement_id'])) {
                continue;
            }

            $ficheCreees++;

            if (in_array($ligne['fiche_statut'], ['brouillon', 'calcule'], true)) {
                $enAttente++;
            }

            if ($ligne['fiche_statut'] === 'rejete') {
                $rejetees++;
            }
        }

        return [
            'total_eligibles' => count($lignes),
            'fiches_creees' => $ficheCreees,
            'en_attente' => $enAttente,
            'fiches_rejetees' => $rejetees,
        ];
    }

    /**
     * Formulaire calqué sur la feuille de déplacement papier, pour UN
     * bénéficiaire au dossier complet d'UNE convocation.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $convocationId = $request->query('convocation_id');
        $beneficiaireId = $request->query('beneficiaire_id');

        if (! $convocationId || ! $beneficiaireId) {
            return redirect()
                ->route('indemnites.frais-deplacement')
                ->with('error', 'Choisissez une convocation puis un bénéficiaire au dossier complet.');
        }

        $convocationResultat = $this->convocations->trouver($convocationId);

        if (! $convocationResultat['success']) {
            return redirect()
                ->route('indemnites.frais-deplacement')
                ->with('error', $convocationResultat['message'] ?? 'Convocation introuvable.');
        }

        $eligiblesResultat = $this->fraisDeplacement->beneficiairesEligibles($convocationId);
        $complets = $eligiblesResultat['success'] ? ($eligiblesResultat['data']['complets'] ?? []) : [];

        $beneficiaire = collect($complets)->first(fn ($b) => (string) ($b['id'] ?? '') === (string) $beneficiaireId);

        if (! $beneficiaire) {
            return redirect()
                ->route('indemnites.frais-deplacement', ['convocation_id' => $convocationId])
                ->with('error', "Ce bénéficiaire n'a pas de dossier complet pour cette convocation.");
        }

        if (! empty($beneficiaire['fiche_deplacement_id'])) {
            return redirect()
                ->route('indemnites.frais-deplacement.show', $beneficiaire['fiche_deplacement_id'])
                ->with('error', 'Une fiche de déplacement existe déjà pour ce bénéficiaire sur cette convocation.');
        }

        return view('pages.indemnites.frais-deplacement.create', [
            'convocation' => $convocationResultat['data'],
            'convocationId' => $convocationId,
            'beneficiaire' => $beneficiaire,
        ]);
    }

    /**
     * Calcul du montant pour TOUS les membres éligibles d'une convocation
     * en une seule page (demande utilisatrice : "tu dois le faire pour
     * tous les membres qui sont sur la convocation") — au lieu de "Nouvelle
     * fiche" qui ne traite qu'un bénéficiaire à la fois. Le barème
     * (Groupe/Taux/ajustement lieu/jours) est appliqué et affiché en
     * direct côté JS pour prévisualisation ; le montant qui fait foi reste
     * celui recalculé côté back à la création de chaque fiche (voir
     * storeGroupe() ci-dessous et FraisDeplacementController::store() côté
     * back).
     */
    public function calculGroupe(Request $request): View|RedirectResponse
    {
        $convocationId = $request->query('convocation_id');

        if (! $convocationId) {
            return redirect()
                ->route('indemnites.frais-deplacement')
                ->with('error', 'Choisissez une convocation pour calculer les frais de tous ses membres.');
        }

        $convocationResultat = $this->convocations->trouver($convocationId);

        if (! $convocationResultat['success']) {
            return redirect()
                ->route('indemnites.frais-deplacement')
                ->with('error', $convocationResultat['message'] ?? 'Convocation introuvable.');
        }

        $eligiblesResultat = $this->fraisDeplacement->beneficiairesEligibles($convocationId);

        if (! $eligiblesResultat['success']) {
            return redirect()
                ->route('indemnites.frais-deplacement')
                ->with('error', $eligiblesResultat['message'] ?? "Impossible de charger les bénéficiaires éligibles.");
        }

        $complets = $eligiblesResultat['data']['complets'] ?? [];

        return view('pages.indemnites.frais-deplacement.calcul-groupe', [
            'convocation' => $convocationResultat['data'],
            'convocationId' => $convocationId,
            'membres' => $complets,
        ]);
    }

    /**
     * Crée une fiche (statut "calculée") pour chaque membre coché, en
     * réutilisant l'endpoint de création existant (une fiche = un appel,
     * comme depuis "Nouvelle fiche") — pas de nouvel endpoint back, chaque
     * membre reste soumis à la même validation/règles métier
     * (dossierComplet(), fiche pas déjà existante, etc.).
     */
    public function storeGroupe(Request $request): RedirectResponse
    {
        $convocationId = $request->input('convocation_id');
        $lieuExamen = $request->input('lieu_examen');
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $beneficiaireIds = array_filter((array) $request->input('beneficiaire_id', []));

        if (empty($beneficiaireIds)) {
            return redirect()
                ->route('indemnites.frais-deplacement.calcul-groupe', ['convocation_id' => $convocationId])
                ->with('error', 'Sélectionnez au moins un membre à calculer.');
        }

        $creees = 0;
        $erreurs = [];

        foreach ($beneficiaireIds as $beneficiaireId) {
            // "provenance" (où le bénéficiaire exerce habituellement) est
            // soumise comme le champ "lieu_service" attendu par le back —
            // le "lieu d'affectation" (centre de cet examen) n'est jamais
            // soumis, il est toujours recalculé côté serveur (voir
            // FraisDeplacementController::centreAffectationEnseignant()).
            $provenance = $request->input("provenance.{$beneficiaireId}");

            $donnees = [
                'convocation_id' => $convocationId,
                'beneficiaire_id' => $beneficiaireId,
                'motif' => $request->input('motif') ?: null,
                'lieu_depart' => $provenance,
                'lieu_destination' => $lieuExamen,
                'lieu_service' => $provenance,
                // Période de trajet par défaut = période d'examen de la
                // convocation (le barème se base dessus - voir
                // nombreJoursPeriodeExamen() côté back) ; ajustable ensuite
                // au cas par cas depuis la fiche ("Modifier").
                'date_depart' => $dateDebut,
                'date_retour' => $dateFin,
                'indice_agent' => $request->input("indice.{$beneficiaireId}"),
                'montant_saisi' => $request->input("montant_mensuel.{$beneficiaireId}"),
            ];

            $resultat = $this->fraisDeplacement->creer($donnees);

            if ($resultat['success']) {
                $creees++;
            } else {
                $erreurs[] = ($request->input("nom.{$beneficiaireId}") ?: "Bénéficiaire #{$beneficiaireId}").' : '.($resultat['message'] ?? 'échec');
            }
        }

        $message = $creees > 0
            ? "{$creees} fiche(s) de déplacement créée(s)."
            : "Aucune fiche n'a pu être créée.";

        return redirect()
            ->route('indemnites.frais-deplacement.calcul-groupe', ['convocation_id' => $convocationId])
            ->with(empty($erreurs) ? 'success' : 'error', empty($erreurs) ? $message : $message.' Erreurs : '.implode(' — ', $erreurs));
    }

    public function store(Request $request): RedirectResponse
    {
        // Feuille de déplacement papier = RECTO-VERSO : 2 champs fichier
        // distincts plutôt qu'un seul (voir FraisDeplacementService::creer()).
        $donnees = $request->except(['_token', 'fichier_recto', 'fichier_verso']);

        $resultat = $this->fraisDeplacement->creer($donnees, $request->file('fichier_recto'), $request->file('fichier_verso'));

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.frais-deplacement.create', [
                    'convocation_id' => $request->input('convocation_id'),
                    'beneficiaire_id' => $request->input('beneficiaire_id'),
                ])
                ->withInput()
                ->with('error', $resultat['message'] ?? 'Échec de la création de la fiche de déplacement.');
        }

        return redirect()
            ->route('indemnites.frais-deplacement.show', $resultat['data']['id'])
            ->with('success', 'Fiche de déplacement créée avec succès.');
    }

    /**
     * Étape "Calcul" — lignes de frais saisies dans le tableau du show
     * (type/quantité/taux/description, mêmes noms de champs indexés que le
     * tableau "avance" du formulaire de création). Seules les lignes dont
     * le type ET la quantité sont renseignés sont envoyées au back (voir
     * CalculerFraisDeplacementRequest côté back : lignes requis, min 1).
     */
    public function calculer(Request $request, string $id): RedirectResponse
    {
        $types = $request->input('ligne_type_frais', []);
        $quantites = $request->input('ligne_quantite', []);
        $taux = $request->input('ligne_taux_unitaire', []);
        $descriptions = $request->input('ligne_description', []);

        $lignes = [];

        foreach ($types as $index => $type) {
            if (trim((string) $type) === '' || ! isset($quantites[$index]) || trim((string) $quantites[$index]) === '') {
                continue;
            }

            $lignes[] = [
                'type_frais' => $type,
                'quantite' => $quantites[$index],
                'taux_unitaire' => $taux[$index] ?? 0,
                'description' => $descriptions[$index] ?? null,
            ];
        }

        if (empty($lignes)) {
            return redirect()
                ->route('indemnites.frais-deplacement.show', $id)
                ->with('error', 'Ajoutez au moins une ligne de frais (type et quantité) avant de calculer.');
        }

        $resultat = $this->fraisDeplacement->calculer($id, $lignes);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.frais-deplacement.show', $id)
                ->with('error', $resultat['message'] ?? 'Échec du calcul de la fiche de déplacement.');
        }

        return redirect()
            ->route('indemnites.frais-deplacement.show', $id)
            ->with('success', 'Fiche de déplacement calculée avec succès.');
    }

    /**
     * Téléchargement d'une pièce jointe (recto ou verso) de la fiche —
     * même principe que PiecesJustificativesController::telecharger().
     */
    public function telechargerJustificatif(string $id, string $justificatifId): StreamedResponse
    {
        $reponse = $this->fraisDeplacement->telechargerJustificatif($id, $justificatifId);

        return response()->streamDownload(function () use ($reponse) {
            echo $reponse->body();
        }, 'feuille-deplacement-'.$justificatifId, [
            'Content-Type' => $reponse->header('Content-Type') ?: 'application/octet-stream',
        ]);
    }

    /**
     * Ajout OU remplacement d'une pièce jointe (recto/verso) depuis le
     * détail de la fiche — demande utilisatrice : "je veux pouvoir
     * télécharger, modifier, supprimer". "Modifier" = déposer le nouveau
     * fichier PUIS supprimer l'ancien (ancien_id, absent si aucun fichier
     * n'existait encore pour cette face) : pas d'endpoint "update" dédié
     * côté back, juste deposerJustificatif()/supprimerJustificatif().
     */
    public function remplacerJustificatif(Request $request, string $id): RedirectResponse
    {
        $fichier = $request->file('fichier');

        if (! $fichier) {
            return redirect()
                ->route('indemnites.frais-deplacement.show', $id)
                ->with('error', 'Choisissez un fichier avant de valider.');
        }

        $commentaire = $request->input('commentaire');
        $ancienId = $request->input('ancien_id');

        $resultat = $this->fraisDeplacement->deposerJustificatif($id, $fichier, $commentaire);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.frais-deplacement.show', $id)
                ->with('error', $resultat['message'] ?? 'Échec du dépôt du fichier.');
        }

        if ($ancienId) {
            $this->fraisDeplacement->supprimerJustificatif($id, $ancienId);
        }

        return redirect()
            ->route('indemnites.frais-deplacement.show', $id)
            ->with('success', ($commentaire ? $commentaire.' mis à jour' : 'Fichier déposé').' avec succès.');
    }

    public function supprimerJustificatif(string $id, string $justificatifId): RedirectResponse
    {
        $resultat = $this->fraisDeplacement->supprimerJustificatif($id, $justificatifId);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.frais-deplacement.show', $id)
                ->with('error', $resultat['message'] ?? 'Échec de la suppression du fichier.');
        }

        return redirect()
            ->route('indemnites.frais-deplacement.show', $id)
            ->with('success', 'Pièce jointe supprimée avec succès.');
    }

    /**
     * CRUD de la fiche elle-même (pas des pièces jointes) — demande
     * utilisatrice : "je parlais du CRUD de la fiche". Le back
     * (UpdateFraisDeplacementRequest) n'autorise la modification que du
     * trajet (lieu/dates/motif/moyen de transport/distance) : le reste
     * (bénéficiaire, montant, décompte des avances...) est figé après
     * création, donc le formulaire de modification ne porte que sur ces
     * champs-là.
     */
    public function edit(string $id): View|RedirectResponse
    {
        $resultat = $this->fraisDeplacement->trouver($id);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.frais-deplacement')
                ->with('error', $resultat['message'] ?? 'Fiche de déplacement introuvable.');
        }

        return view('pages.indemnites.frais-deplacement.edit', [
            'fiche' => $resultat['data'],
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        // Demande utilisatrice : "edit doit être complet, base-toi sur
        // l'edit de convocation" — même liste de champs que le formulaire
        // "Nouvelle fiche" (voir create.blade.php), moins convocation_id/
        // beneficiaire_id (fixés à la création) et fichier_recto/
        // fichier_verso (pas dans ce formulaire).
        $donnees = $request->only([
            'grade_emploi',
            'lieu_depart',
            'heure_depart',
            'lieu_destination',
            'motif',
            'date_depart',
            'date_retour',
            'distance_km',
            'moyen_transport',
            'ordre_service_numero',
            'ordre_service_date',
            'ordre_service_emetteur',
            'accompagne_de',
            'groupe',
            'itineraire',
            'poids_bagages_kg',
            'delivre_par',
            'date_emission_fiche',
            'avance_frais_transport_nombre',
            'avance_frais_transport_taux',
            'avance_indemnite_normale_nombre',
            'avance_indemnite_normale_taux',
            'avance_indemnite_reduite_nombre',
            'avance_indemnite_reduite_taux',
            'avance_indemnite_partielle_nombre',
            'avance_indemnite_partielle_taux',
            'indication_requisitions',
            'poids_bagages_mobilier',
            'avance_versee',
            'arrete_somme',
            'date_fait_avance',
            // VERSO — "DETAIL DES VISAS ET PAIEMENT SUCCESSIFS EN COURS DE
            // ROUTE" (voir create.blade.php pour les noms de champs
            // indexés du tableau des 4 visas).
            'visa_arrivee_lieu',
            'visa_arrivee_date',
            'visa_arrivee_heure',
            'visa_depart_lieu',
            'visa_depart_date',
            'visa_depart_heure',
            'visa_requisitions',
            'visa_poids_bagages',
            'visa_logement_nourriture',
            'visa_avance_indemnite_normale_nombre',
            'visa_avance_indemnite_normale_taux',
            'visa_avance_indemnite_reduite_nombre',
            'visa_avance_indemnite_reduite_taux',
            'visa_avance_indemnite_partielle_nombre',
            'visa_avance_indemnite_partielle_taux',
            'visa_avance_payer_somme',
            'visa_avance_lieu',
            'visa_avance_date',
            'reglement_indemnite_normale_nombre',
            'reglement_indemnite_normale_taux',
            'reglement_indemnite_reduite_nombre',
            'reglement_indemnite_reduite_taux',
            'reglement_indemnite_partielle_nombre',
            'reglement_indemnite_partielle_taux',
            'reglement_montant_avances',
            'reglement_arrete_somme',
            'reglement_lieu',
            'reglement_date',
            'observations',
            'indice_agent',
            'montant_saisi',
            'salaire_global_annuel',
            'lieu_service',
        ]);

        $resultat = $this->fraisDeplacement->mettreAJour($id, $donnees);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.frais-deplacement.edit', $id)
                ->withInput()
                ->with('error', $resultat['message'] ?? 'Échec de la mise à jour de la fiche de déplacement.');
        }

        return redirect()
            ->route('indemnites.frais-deplacement.show', $id)
            ->with('success', 'Fiche de déplacement mise à jour avec succès.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $resultat = $this->fraisDeplacement->supprimer($id);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.frais-deplacement.show', $id)
                ->with('error', $resultat['message'] ?? 'Échec de la suppression de la fiche de déplacement.');
        }

        return redirect()
            ->route('indemnites.frais-deplacement')
            ->with('success', 'Fiche de déplacement supprimée avec succès.');
    }

    /**
     * Téléchargement de la fiche en PDF — demande utilisatrice : "je veux
     * pouvoir télécharger la fiche si possible". Même principe que
     * ConvocationsController::downloadPdf().
     */
    public function telechargerPdf(string $id): RedirectResponse|\Illuminate\Http\Response
    {
        $reponse = $this->fraisDeplacement->telechargerPdf($id);

        if (! $reponse->successful()) {
            return redirect()
                ->route('indemnites.frais-deplacement.show', $id)
                ->with('error', $reponse->json('message') ?? 'Impossible de générer le PDF de cette fiche de déplacement.');
        }

        return response($reponse->body(), 200, [
            'Content-Type' => $reponse->header('Content-Type') ?: 'application/pdf',
            'Content-Disposition' => $reponse->header('Content-Disposition')
                ?: 'attachment; filename="fiche-deplacement-'.$id.'.pdf"',
        ]);
    }

    public function show(string $id): View|RedirectResponse
    {
        $resultat = $this->fraisDeplacement->trouver($id);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.frais-deplacement')
                ->with('error', $resultat['message'] ?? 'Fiche de déplacement introuvable.');
        }

        return view('pages.indemnites.frais-deplacement.show', [
            'fiche' => $resultat['data'],
        ]);
    }
}
