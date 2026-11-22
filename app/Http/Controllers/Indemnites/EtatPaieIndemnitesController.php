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
use Symfony\Component\HttpFoundation\StreamedResponse;

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
    // Demande utilisatrice : l'état de paie ne gère que les indemnités de
    // correction et de surveillance — les frais de déplacement ont leur
    // propre état de paiement (PDF + fichier SICA, page Frais de
    // déplacement). Le premier type reste le type par défaut.
    private const TYPES_INDEMNITE = [
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
     * depuis la query string (voir genererEtatGroupe() ci-dessous, qui y
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

        // Correction : les bénéficiaires des frais de déplacement fournissent
        // le chef de centre et le président du jury (montant par défaut).
        if ($type === 'indemnite_correction') {
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
                    .($reste > 0 ? " et {$reste} autre(s)" : '').'. Le RIB complet (24 caractères) est obligatoire : renseignez-le puis recommencez.');
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

    /** AJAX — états de paie enregistrés (filtrables par type, objet et session), avec leurs bénéficiaires. */
    public function listeEtats(Request $request): JsonResponse
    {
        $filtres = array_filter([
            'type' => $request->query('type'),
            'objet' => $request->query('objet'),
            'session' => $request->query('session'),
        ]);

        $resultat = $this->etatPaieIndemnites->liste($filtres);

        if (! $resultat['success']) {
            return response()->json([
                'success' => false,
                'message' => $resultat['message'] ?? 'Impossible de charger les états de paie.',
            ], $resultat['status'] ?? 500);
        }

        $etats = collect($resultat['data'] ?? [])->map(fn (array $etat) => [
            'id' => $etat['id'] ?? null,
            'reference' => $etat['reference'] ?? null,
            'type' => $etat['type'] ?? null,
            'type_libelle' => self::TYPES_INDEMNITE[$etat['type'] ?? ''] ?? ($etat['type'] ?? null),
            'session' => $etat['session'] ?? null,
            'objet' => $etat['perimetre']['objet'] ?? null,
            'periode_debut' => $etat['periode_debut'] ?? null,
            'periode_fin' => $etat['periode_fin'] ?? null,
            'lieu_examen' => $etat['lieu_examen'] ?? null,
            'statut' => $etat['statut'] ?? 'brouillon',
            'date_generation' => $etat['date_generation'] ?? null,
            'total_montant' => $etat['total_montant'] ?? 0,
            'details' => $etat['details'] ?? [],
        ])->values();

        return response()->json(['success' => true, 'data' => ['etats' => $etats]]);
    }

    /** PDF d'un état enregistré. */
    public function telechargerEtat(string $id): RedirectResponse|\Illuminate\Http\Response
    {
        $reponse = $this->etatPaieIndemnites->pdf($id);

        if (! $reponse->successful()) {
            return redirect()
                ->route('indemnites.etats-paie')
                ->with('error', $reponse->json('message') ?? "Impossible de générer le PDF de l'état de paie.");
        }

        return response($reponse->body(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $reponse->header('Content-Disposition') ?: 'inline; filename="etat-de-paie.pdf"',
        ]);
    }

    /** Supprime un état de paie en brouillon (l'API refuse les autres statuts). */
    public function supprimerEtat(Request $request, string $id): RedirectResponse
    {
        $resultat = $this->etatPaieIndemnites->supprimer($id);

        return redirect()
            ->route('indemnites.etats-paie', array_filter($request->only(['type', 'objet', 'session'])))
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? ($resultat['success'] ? 'État de paie supprimé.' : "Impossible de supprimer l'état de paie."));
    }

    /** Valider, transmettre ou archiver un état de paie (circuit de la DAGE). */
    public function changerStatutEtat(Request $request, string $id, string $action): RedirectResponse
    {
        abort_unless(in_array($action, ['valider', 'transmettre', 'archiver'], true), 404);

        $resultat = $this->etatPaieIndemnites->changerStatut($id, $action);

        return redirect()
            ->route('indemnites.etats-paie', array_filter($request->only(['type', 'objet', 'session'])))
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? "Impossible de modifier le statut de l'état de paie.");
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
     * Génère et télécharge la fiche d'état de paiement du filtre courant,
     * au format CSV (demande utilisatrice ; c'était un PDF auparavant) —
     * "je voudrais a chaque filtre generer un fiche d'etat de paie le
     * fichier doit avoir ses informations". Reconstruit les membres via
     * construireMembres() (même agrégation que membresPourFiltre()) plutôt
     * que de faire confiance à un tableau envoyé par le navigateur. Le nom
     * de la méthode et de la route (etats-paie.fiche-pdf) est conservé pour
     * ne pas casser les liens existants.
     */
    public function genererFichePdf(Request $request): RedirectResponse|StreamedResponse
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

        // Coordonnées bancaires de chaque membre (compte bancaire de sa fiche
        // enseignant) — même source que la génération de l'état de paie
        // (store() ci-dessus). Sans elles, la colonne "Coordonnées bancaires"
        // de la fiche PDF restait toujours vide ("—"). Ici on ne bloque pas
        // si un RIB manque : la fiche est un aperçu, la cellule reste "—".
        $ribResultat = $this->enseignants->coordonneesBancaires($membres->pluck('id')->filter()->unique()->values()->all());
        $ribs = $ribResultat['success'] ? ($ribResultat['data'] ?? []) : [];

        $details = $membres->map(function (array $membre) use ($ribs) {
            $rib = $ribs[$membre['id'] ?? ''] ?? [];

            return array_merge($membre, [
                'code_banque' => $rib['code_banque'] ?? null,
                'code_guichet' => $rib['code_guichet'] ?? null,
                'numero_compte_bancaire' => $rib['numero_compte_bancaire'] ?? null,
                'cle_rib' => $rib['cle_rib'] ?? null,
            ]);
        })->values()->all();

        // Demande utilisatrice : la fiche est désormais un fichier CSV (et
        // non plus un PDF). Même format que les autres fichiers SICA de
        // l'application : UTF-8 avec BOM (accents lisibles dans Excel),
        // séparateur ";", cellules neutralisées contre l'injection de
        // formules (=, +, -, @ en début de cellule).
        $colonnes = [
            'N°', 'Nom', 'Prénom', 'Fonction', 'Catégorie', 'Centre',
            'Code banque', 'Code guichet', 'Numéro de compte', 'Clé RIB',
            'Montant (FCFA)', "Type d'indemnité", 'Objet', 'Session',
        ];

        $lignes = array_map(fn (array $ligne, int $index) => [
            $index + 1,
            $ligne['nom'] ?? '',
            $ligne['prenom'] ?? '',
            $ligne['fonction'] ?? '',
            ucfirst((string) ($ligne['categorie_personnel'] ?? '')),
            $ligne['centre'] ?? '',
            $ligne['code_banque'] ?? '',
            $ligne['code_guichet'] ?? '',
            $ligne['numero_compte_bancaire'] ?? '',
            $ligne['cle_rib'] ?? '',
            (int) round((float) ($ligne['montant'] ?? 0)),
            $typeLibelle,
            $objet ?? '',
            $session ?? '',
        ], $details, array_keys($details));

        $nomFichier = 'etat-de-paiement-'.Str::slug($typeLibelle)
            .($objet ? '-'.Str::slug($objet) : '')
            .($session ? '-'.Str::slug($session) : '').'.csv';

        return response()->streamDownload(function () use ($colonnes, $lignes): void {
            $flux = fopen('php://output', 'wb');
            fwrite($flux, "\xEF\xBB\xBF");
            fputcsv($flux, $colonnes, ';', '"', '');

            foreach ($lignes as $ligne) {
                fputcsv($flux, array_map(function (mixed $cellule): string {
                    $valeur = (string) $cellule;

                    return preg_match('/^[=+@\-\t\r]/', $valeur) ? "'".$valeur : $valeur;
                }, $ligne), ';', '"', '');
            }

            fclose($flux);
        }, $nomFichier, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
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
