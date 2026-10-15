<?php

namespace App\Http\Controllers\Indemnites;

use App\Http\Controllers\Controller;
use App\Services\Api\Indemnites\ConvocationService;
use App\Services\Api\Indemnites\EnseignantService;
use App\Services\Api\Indemnites\EtatPaieIndemnitesService;
use App\Services\Api\Indemnites\FraisDeplacementService;
use App\Services\Api\Indemnites\IndemniteCorrectionService;
use App\Services\Api\Indemnites\IndemniteSurveillanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * "États de paie" — fiche regroupant, pour une période, les montants d'UN
 * des 3 domaines déjà gérés séparément (Frais de déplacement, Indemnité de
 * correction, Indemnité de surveillance). Construction en plusieurs étapes
 * (demande utilisatrice : "implemente ça après on continue") — pour
 * l'instant seule l'entrée (type + objet + session, avec les centres
 * concernés qui apparaissent dynamiquement) est en place.
 */
class EtatPaieIndemnitesController extends Controller
{
    public function __construct(
        protected ConvocationService $convocations,
        protected FraisDeplacementService $fraisDeplacement,
        protected IndemniteCorrectionService $indemniteCorrection,
        protected IndemniteSurveillanceService $indemniteSurveillance,
        protected EtatPaieIndemnitesService $etatPaieIndemnites,
        protected EnseignantService $enseignants
    ) {}

    /**
     * Mêmes 3 domaines déjà gérés séparément par leurs propres pages
     * (Frais de déplacement / Indemnités de correction / Indemnité de
     * surveillance) — cf. config/navigation.php.
     */
    private const TYPES_INDEMNITE = [
        'frais_deplacement' => 'Frais de déplacement',
        'indemnite_correction' => 'Indemnité de correction',
        'indemnite_surveillance' => 'Indemnité de surveillance',
    ];

    /**
     * Demande utilisatrice : "par defaut pour les president de centre et de
     * jury pour le type de correction le montant est 50000" — montant fixe
     * (pas de barème/copies pour ces 2 rôles sur ce type), à distinguer
     * visuellement d'un montant réellement calculé (voir 'montant_par_defaut').
     */
    private const MONTANT_DEFAUT_CHEF_PRESIDENT_CORRECTION = 50000;

    /**
     * Demande utilisatrice : "je veux fixer tableau filtré après ajout de
     * l'état de paie" — le filtre (type/objet/session/centre) est repris
     * depuis la query string (voir storeEtatPaie() ci-dessous, qui y
     * redirige avec ces mêmes valeurs) pour pré-sélectionner les menus et
     * relancer automatiquement le chargement du tableau côté JS, au lieu de
     * revenir sur une page vide après la création.
     */
    public function index(Request $request): View
    {
        $optionsFiltres = $this->optionsFiltresPourVue();

        $filtres = [
            'type' => $request->query('type') ?: array_key_first(self::TYPES_INDEMNITE),
            'objet' => $request->query('objet'),
            'session' => $request->query('session'),
            'centre' => $request->query('centre'),
        ];

        $resultat = $this->etatPaieIndemnites->liste();
        $etats = collect($resultat['success'] ? ($resultat['data'] ?? []) : []);

        $stats = [
            'generees' => $etats->count(),
            'validees' => $etats->where('statut', 'valide')->count(),
            'en_attente' => $etats->whereIn('statut', ['brouillon', 'genere'])->count(),
            'transmises' => $etats->where('statut', 'transmis')->count(),
        ];

        return view('pages.indemnites.etats-paie', [
            'typesIndemnite' => self::TYPES_INDEMNITE,
            'optionsFiltres' => $optionsFiltres,
            'stats' => $stats,
            'filtres' => $filtres,
        ]);
    }

    private function optionsFiltresPourVue(): array
    {
        $resultat = $this->convocations->optionsFiltres();

        $vide = ['objets' => [], 'sessions' => [], 'centres' => []];

        return $resultat['success'] ? array_merge($vide, $resultat['data'] ?? []) : $vide;
    }

    /**
     * AJAX — centres des convocations correspondant à l'objet et/ou la
     * session choisis (demande utilisatrice : "lorsque je choisi l'objet
     * les centres qui concerne l'objet et la session apparaissent"). Pas
     * d'endpoint back dédié : mêmes filtres que la liste des convocations
     * (ConvocationsController::index() côté back), centres aplatis/dédupliqués
     * ici — même principe déjà utilisé par IndemniteSurveillanceController::index().
     */
    public function centresPourObjetSession(Request $request): JsonResponse
    {
        $filtres = array_filter([
            'objet' => $request->query('objet'),
            'session' => $request->query('session'),
        ]);

        $resultat = $this->convocations->liste(array_merge($filtres, ['per_page' => 100]));

        if (! $resultat['success']) {
            return response()->json([
                'success' => false,
                'message' => $resultat['message'] ?? 'Impossible de charger les centres.',
            ], $resultat['status'] ?? 500);
        }

        $items = $resultat['data']['data'] ?? [];

        $centres = collect($items)
            ->flatMap(fn (array $convocation) => collect($convocation['centres'] ?? [])->pluck('centre'))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return response()->json(['success' => true, 'data' => ['centres' => $centres]]);
    }

    /**
     * AJAX — membres associés au type d'indemnité choisi, pour les
     * convocations correspondant à l'objet/session (et centre si précisé) —
     * demande utilisatrice : "pour chaque type d'indemnité choisi on
     * affiche la liste des membres associé avec le nom prenom categorie
     * fonction et le montant". Chaque type a son propre domaine/table
     * (MissionDeplacement / IndemniteCorrection / IndemniteSurveillance),
     * normalisé ici vers une forme commune.
     */
    public function membresPourFiltre(Request $request): JsonResponse
    {
        $type = $request->query('type');
        $objet = $request->query('objet');
        $session = $request->query('session');
        $centres = $this->centresDemandes($request);

        if (! $type) {
            return response()->json(['success' => true, 'data' => ['membres' => []]]);
        }

        if (! array_key_exists($type, self::TYPES_INDEMNITE)) {
            return response()->json(['success' => false, 'message' => "Type d'indemnité inconnu."], 422);
        }

        $membres = $this->construireMembres($type, $objet, $session, $centres);

        if ($membres === null) {
            return response()->json(['success' => false, 'message' => 'Impossible de charger les membres.'], 500);
        }

        return response()->json(['success' => true, 'data' => ['membres' => $membres->values()]]);
    }

    /**
     * Partagé entre membresPourFiltre() (AJAX, JSON) et genererFichePdf()
     * (PDF) — même agrégation, 'metier' conservé (absent pour frais de
     * déplacement/chef-président, présent pour correction/surveillance :
     * utilisé pour grouper les lignes du PDF comme sur le document papier).
     */
    private function construireMembres(string $type, ?string $objet, ?string $session, array $centres = []): ?\Illuminate\Support\Collection
    {
        $centresMinuscules = array_map(fn (string $centre) => Str::lower($centre), $centres);

        $filtres = array_filter(['objet' => $objet, 'session' => $session]);
        $resultat = $this->convocations->liste(array_merge($filtres, ['per_page' => 100]));

        if (! $resultat['success']) {
            return null;
        }

        $items = $resultat['data']['data'] ?? [];
        $membres = collect();

        // Données de toutes les convocations en un seul appel (au lieu d'un appel par convocation).
        $ids = array_values(array_filter(array_column($items, 'id')));
        $eligiblesFrais = [];
        $correcteurs = [];
        $surveillants = [];

        if ($type === 'frais_deplacement' || $type === 'indemnite_correction') {
            $lot = $this->fraisDeplacement->beneficiairesEligiblesLot($ids);
            $eligiblesFrais = $lot['success'] ? ($lot['data'] ?? []) : [];
        }

        if ($type === 'indemnite_correction') {
            $lot = $this->indemniteCorrection->correcteursEligiblesLot($ids);
            $correcteurs = $lot['success'] ? ($lot['data'] ?? []) : [];
        }

        if ($type === 'indemnite_surveillance') {
            $lot = $this->indemniteSurveillance->surveillantsEligiblesLot($ids);
            $surveillants = $lot['success'] ? ($lot['data'] ?? []) : [];
        }

        foreach ($items as $item) {
            $convocationId = $item['id'] ?? null;

            if (! $convocationId) {
                continue;
            }

            $lignes = match ($type) {
                'frais_deplacement' => $this->lignesFraisDeplacement($eligiblesFrais[$convocationId] ?? []),
                'indemnite_correction' => array_merge(
                    $correcteurs[$convocationId] ?? [],
                    $this->lignesChefEtPresident($eligiblesFrais[$convocationId] ?? [])
                ),
                'indemnite_surveillance' => $surveillants[$convocationId] ?? [],
                default => [],
            };

            foreach ($lignes as $ligne) {
                if ($centresMinuscules !== [] && ! in_array(Str::lower($ligne['centre'] ?? ''), $centresMinuscules, true)) {
                    continue;
                }

                $membres->push([
                    'id' => $ligne['id'] ?? null,
                    'nom' => $ligne['nom'] ?? null,
                    'prenom' => $ligne['prenom'] ?? null,
                    'categorie_personnel' => $ligne['categorie_personnel'] ?? null,
                    'fonction' => $ligne['fonction'] ?? null,
                    'metier' => $ligne['metier'] ?? null,
                    'centre' => $ligne['centre'] ?? null,
                    'montant' => $ligne['montant'] ?? 0,
                    'montant_par_defaut' => $ligne['montant_par_defaut'] ?? false,
                ]);
            }
        }

        return $membres;
    }

    /**
     * AJAX — états de paie déjà créés pour le filtre courant (demande
     * utilisatrice : "ajouter un bouton voir etat qui affiche les
     * informations en modal").
     */
    public function etatsExistants(Request $request): JsonResponse
    {
        // Pas de filtre « centre » : un état peut regrouper plusieurs centres, et un
        // membre déjà payé doit être reconnu quel que soit le centre affiché.
        $filtres = array_filter([
            'type' => $request->query('type'),
            'objet' => $request->query('objet'),
            'session' => $request->query('session'),
        ]);

        if (empty($filtres['type'])) {
            return response()->json(['success' => true, 'data' => ['etats' => []]]);
        }

        $resultat = $this->etatPaieIndemnites->liste($filtres);

        if (! $resultat['success']) {
            return response()->json([
                'success' => false,
                'message' => $resultat['message'] ?? 'Impossible de charger les états de paie.',
            ], $resultat['status'] ?? 500);
        }

        return response()->json(['success' => true, 'data' => ['etats' => $resultat['data'] ?? []]]);
    }

    /**
     * AJAX — coordonnées bancaires d'UN membre, pour pré-remplir la modale
     * "Ajouter état de paie" (demande utilisatrice : "un modal ... on doit
     * avoir code banque code guichet Numero compte clé rib") — déjà
     * enregistrées sur la fiche de l'enseignant si présentes, sinon
     * laissées vides pour saisie manuelle dans la modale.
     */
    public function ribMembre(string $id): JsonResponse
    {
        $resultat = $this->enseignants->coordonneesBancaires([$id]);

        if (! $resultat['success']) {
            return response()->json([
                'success' => false,
                'message' => $resultat['message'] ?? 'Coordonnées bancaires introuvables.',
            ], $resultat['status'] ?? 404);
        }

        $rib = $resultat['data'][$id] ?? [];

        return response()->json(['success' => true, 'data' => [
            'code_banque' => $rib['code_banque'] ?? null,
            'code_guichet' => $rib['code_guichet'] ?? null,
            'numero_compte_bancaire' => $rib['numero_compte_bancaire'] ?? null,
            'cle_rib' => $rib['cle_rib'] ?? null,
        ]]);
    }

    /**
     * Crée la fiche état de paie (demande utilisatrice : "mets un bouton
     * ajouter etat de paie") — regroupe les membres cochés dans le tableau
     * (nom/prenom/categorie/fonction/montant) sous 'details', 'total_montant'
     * calculé serveur. periode_debut/periode_fin (requis côté back, voir
     * StoreEtatPaieIndemniteRequest) ne sont pas saisis sur ce formulaire :
     * dérivés des dates de la/les convocation(s) correspondant à
     * l'objet/session choisis.
     */
    public function storeEtatPaie(Request $request): RedirectResponse
    {
        $type = $request->input('type');
        $objet = $request->input('objet');
        $session = $request->input('session');
        $centre = $request->input('centre');
        $details = $request->input('details', []);

        // Repris sur chaque redirection (succès ou erreur) pour rester sur
        // le meme tableau filtré plutot que de revenir a une page vide.
        $retour = array_filter(compact('type', 'objet', 'session', 'centre'));

        if (! $type || (! $objet && ! $session) || empty($details)) {
            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', "Choisissez un type, un objet ou une session, et au moins un membre avant d'ajouter un état de paie.");
        }

        $filtres = array_filter(['objet' => $objet, 'session' => $session]);
        $resultat = $this->convocations->liste(array_merge($filtres, ['per_page' => 100]));
        $items = $resultat['success'] ? ($resultat['data']['data'] ?? []) : [];

        $datesDebut = collect($items)->pluck('date_debut')->filter();
        $datesFin = collect($items)->pluck('date_fin')->filter();

        if ($datesDebut->isEmpty() || $datesFin->isEmpty()) {
            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', "Impossible de déterminer la période : aucune convocation avec dates ne correspond à ce filtre.");
        }

        $donnees = [
            'type' => $type,
            'periode_debut' => $datesDebut->min(),
            'periode_fin' => $datesFin->max(),
            'lieu_examen' => $centre,
            'session' => $session,
            'perimetre' => array_filter(['type' => $type, 'objet' => $objet, 'session' => $session, 'centre' => $centre]),
            'details' => $details,
        ];

        $creation = $this->etatPaieIndemnites->creer($donnees);

        if (! $creation['success']) {
            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', $creation['message'] ?? "Échec de la création de l'état de paie.");
        }

        return redirect()
            ->route('indemnites.etats-paie', $retour)
            ->with('success', 'État de paie créé avec succès.');
    }

    /**
     * Produit UN état de paie pour tous les bénéficiaires du filtre (un ou plusieurs
     * centres). Rien n'est créé si une règle n'est pas respectée :
     *  - montant à 0 : la personne est ignorée (rien à payer) ;
     *  - déjà présente dans un état de paie du même type, objet et session : ignorée
     *    (évite un double paiement) ;
     *  - coordonnées bancaires incomplètes : la génération est BLOQUÉE et les personnes
     *    concernées sont listées.
     */
    public function genererEtatGroupe(Request $request): RedirectResponse
    {
        $type = $request->input('type');
        $objet = $request->input('objet');
        $session = $request->input('session');
        $centres = $this->centresDemandes($request, 'input');

        $retour = array_filter(['type' => $type, 'objet' => $objet, 'session' => $session, 'centre' => $centres]);

        if (! $type || (! $objet && ! $session) || ! array_key_exists($type, self::TYPES_INDEMNITE)) {
            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', "Choisissez un type d'indemnité et un objet ou une session avant de générer l'état de paie.");
        }

        $membres = $this->construireMembres($type, $objet, $session, $centres);

        if ($membres === null) {
            return redirect()->route('indemnites.etats-paie', $retour)->with('error', 'Impossible de charger les bénéficiaires.');
        }

        $avecMontant = $membres->filter(fn (array $membre) => (float) ($membre['montant'] ?? 0) > 0)->values();
        $sansMontant = $membres->count() - $avecMontant->count();

        $dejaDansUnEtat = $this->identifiantsDejaDansUnEtat($type, $objet, $session);
        $aPayer = $avecMontant
            ->reject(fn (array $membre) => in_array((string) ($membre['id'] ?? ''), $dejaDansUnEtat, true))
            ->values();
        $dejaPayes = $avecMontant->count() - $aPayer->count();

        $ignores = array_filter([
            $sansMontant > 0 ? "{$sansMontant} sans montant à payer" : null,
            $dejaPayes > 0 ? "{$dejaPayes} déjà dans un état de paie" : null,
        ]);

        if ($aPayer->isEmpty()) {
            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', 'Aucun bénéficiaire à payer pour ce filtre'.($ignores ? ' ('.implode(', ', $ignores).').' : '.'));
        }

        $ribResultat = $this->enseignants->coordonneesBancaires($aPayer->pluck('id')->filter()->all());

        if (! $ribResultat['success']) {
            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', $ribResultat['message'] ?? 'Impossible de vérifier les coordonnées bancaires.');
        }

        $ribs = $ribResultat['data'] ?? [];
        $sansRib = $aPayer->filter(fn (array $membre) => empty($ribs[$membre['id']]['complet']))->values();

        if ($sansRib->isNotEmpty()) {
            $noms = $sansRib->take(12)->map(fn (array $membre) => trim(($membre['prenom'] ?? '').' '.($membre['nom'] ?? '')))->implode(', ');
            $reste = $sansRib->count() - 12;

            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', "Génération bloquée : coordonnées bancaires incomplètes pour {$sansRib->count()} bénéficiaire(s) — {$noms}"
                    .($reste > 0 ? " et {$reste} autre(s)" : '').'. Renseignez leur RIB puis recommencez.');
        }

        $filtres = array_filter(['objet' => $objet, 'session' => $session]);
        $convocations = $this->convocations->liste(array_merge($filtres, ['per_page' => 100]));
        $items = $convocations['success'] ? ($convocations['data']['data'] ?? []) : [];

        $datesDebut = collect($items)->pluck('date_debut')->filter();
        $datesFin = collect($items)->pluck('date_fin')->filter();

        if ($datesDebut->isEmpty() || $datesFin->isEmpty()) {
            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', 'Impossible de déterminer la période : aucune convocation avec dates ne correspond à ce filtre.');
        }

        $details = $aPayer->map(function (array $membre) use ($ribs) {
            $rib = $ribs[$membre['id']];

            return [
                'id' => $membre['id'],
                'nom' => $membre['nom'] ?? null,
                'prenom' => $membre['prenom'] ?? null,
                'categorie_personnel' => $membre['categorie_personnel'] ?? null,
                'fonction' => $membre['fonction'] ?? null,
                'metier' => $membre['metier'] ?? null,
                'centre' => $membre['centre'] ?? null,
                'montant' => (float) $membre['montant'],
                'code_banque' => $rib['code_banque'],
                'code_guichet' => $rib['code_guichet'],
                'numero_compte_bancaire' => $rib['numero_compte_bancaire'],
                'cle_rib' => $rib['cle_rib'],
            ];
        })->values()->all();

        $creation = $this->etatPaieIndemnites->creer([
            'type' => $type,
            'periode_debut' => $datesDebut->min(),
            'periode_fin' => $datesFin->max(),
            'lieu_examen' => $centres === [] ? null : Str::limit(implode(', ', $centres), 250, ''),
            'session' => $session,
            'perimetre' => array_filter(['type' => $type, 'objet' => $objet, 'session' => $session, 'centres' => $centres]),
            'details' => $details,
        ]);

        if (! $creation['success']) {
            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', $creation['message'] ?? "Échec de la création de l'état de paie.");
        }

        $total = collect($details)->sum('montant');
        $message = sprintf(
            'État de paie %s généré : %d bénéficiaire(s), total %s F.',
            $creation['data']['reference'] ?? '',
            count($details),
            number_format($total, 0, ',', ' ')
        );

        if ($ignores) {
            $message .= ' Ignoré(s) : '.implode(', ', $ignores).'.';
        }

        return redirect()->route('indemnites.etats-paie', $retour)->with('success', $message);
    }

    /** Centres choisis : `centre` (un seul) ou `centre[]` (plusieurs), sans valeur vide. */
    private function centresDemandes(Request $request, string $source = 'query'): array
    {
        $valeur = $source === 'input' ? $request->input('centre') : $request->query('centre');

        return array_values(array_filter(
            array_map(fn ($centre) => trim((string) $centre), (array) $valeur),
            fn (string $centre) => $centre !== ''
        ));
    }

    /** Identifiants (enseignants) déjà présents dans un état de paie du même type, objet et session. */
    private function identifiantsDejaDansUnEtat(string $type, ?string $objet, ?string $session): array
    {
        $resultat = $this->etatPaieIndemnites->liste(array_filter(['type' => $type, 'objet' => $objet, 'session' => $session]));
        $etats = $resultat['success'] ? ($resultat['data'] ?? []) : [];

        return collect($etats)
            ->flatMap(fn (array $etat) => collect($etat['details'] ?? [])->pluck('id'))
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Génère et télécharge la fiche PDF officielle ("États de paiement
     * examens" — contrôle clé RIB / fichier virement de masse Trésor
     * Public) à partir du filtre courant — demande utilisatrice : "je
     * voudrais a chaque filtre generer un fiche d'etat de paie le fichier
     * doit avoir ses informations" (document papier fourni en photo).
     * Reconstruit les membres via construireMembres() (même agrégation que
     * membresPourFiltre()) plutôt que de faire confiance à un tableau
     * envoyé par le navigateur — évite de dépendre du DOM JS déjà rendu.
     */
    public function genererFichePdf(Request $request): RedirectResponse|\Illuminate\Http\Response
    {
        $type = $request->query('type');
        $objet = $request->query('objet');
        $session = $request->query('session');
        $centres = $this->centresDemandes($request);

        $retour = array_filter(['type' => $type, 'objet' => $objet, 'session' => $session, 'centre' => $centres]);

        if (! $type || (! $objet && ! $session) || ! array_key_exists($type, self::TYPES_INDEMNITE)) {
            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', "Choisissez un type d'indemnité et un objet ou une session avant de générer la fiche.");
        }

        $membres = $this->construireMembres($type, $objet, $session, $centres);

        if ($membres === null || $membres->isEmpty()) {
            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', 'Aucun membre pour ce filtre : impossible de générer la fiche.');
        }

        $typeLibelle = self::TYPES_INDEMNITE[$type];

        $reponse = $this->etatPaieIndemnites->genererFichePdf([
            'type' => $type,
            'type_libelle' => $typeLibelle,
            'objet' => $objet,
            'session' => $session,
            'centre' => $centres === [] ? null : implode(', ', $centres),
            'details' => $membres->values()->all(),
        ]);

        if (! $reponse->successful()) {
            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', $reponse->json('message') ?? 'Impossible de générer la fiche.');
        }

        return response($reponse->body(), 200, [
            'Content-Type' => $reponse->header('Content-Type') ?: 'application/pdf',
            // inline (pas attachment) : demande utilisatrice "afficher le
            // fichier pdf avant de le telecharger" — le navigateur l'ouvre
            // dans l'onglet au lieu de forcer le telechargement direct.
            'Content-Disposition' => $reponse->header('Content-Disposition') ?: 'inline; filename="etat-de-paiement.pdf"',
        ]);
    }

    /**
     * Seuls les bénéficiaires au dossier complet peuvent avoir une fiche de
     * déplacement (même filtre que la page Frais de déplacement) —
     * 'fiche_montant' renommé en 'montant' pour la forme commune.
     *
     * @param  array<string, mixed>  $eligibles  résultat de beneficiairesEligibles() pour UNE convocation
     */
    private function lignesFraisDeplacement(array $eligibles): array
    {
        return array_map(function (array $ligne) {
            $ligne['montant'] = $ligne['fiche_montant'] ?? 0;

            return $ligne;
        }, $eligibles['complets'] ?? []);
    }

    /**
     * Chef de centre et président de jury d'une convocation, quel que soit
     * l'état de leur dossier de pièces justificatives : ils n'ont pas de ligne
     * dans indemnites_correction (ils ne corrigent pas de copies), donc un
     * montant par défaut leur est attribué.
     *
     * @param  array<string, mixed>  $eligibles  résultat de beneficiairesEligibles() pour UNE convocation
     */
    private function lignesChefEtPresident(array $eligibles): array
    {
        $toutes = array_merge($eligibles['complets'] ?? [], $eligibles['incomplets'] ?? []);

        $chefEtPresident = array_filter($toutes, fn (array $ligne) => in_array(
            $ligne['fonction'] ?? null,
            ['Chef de centre', 'Président de jury'],
            true
        ));

        return array_map(function (array $ligne) {
            $ligne['montant'] = self::MONTANT_DEFAUT_CHEF_PRESIDENT_CORRECTION;
            $ligne['montant_par_defaut'] = true;

            return $ligne;
        }, $chefEtPresident);
    }
}
