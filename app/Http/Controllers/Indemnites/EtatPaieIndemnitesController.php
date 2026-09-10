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
            'type' => $request->query('type'),
            'objet' => $request->query('objet'),
            'session' => $request->query('session'),
            'centre' => $request->query('centre'),
        ];

        // A 0 pour l'instant : la generation des fiches etat de paie (back
        // EtatPaieIndemnitesController) n'est pas encore implementee — a
        // relier a de vrais compteurs des que la liste/generation existera.
        $stats = [
            'generees' => 0,
            'validees' => 0,
            'en_attente' => 0,
            'transmises' => 0,
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

        if (empty($filtres)) {
            return response()->json(['success' => true, 'data' => ['centres' => []]]);
        }

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
        $centre = $request->query('centre');

        if (! $type || (! $objet && ! $session)) {
            return response()->json(['success' => true, 'data' => ['membres' => []]]);
        }

        if (! array_key_exists($type, self::TYPES_INDEMNITE)) {
            return response()->json(['success' => false, 'message' => "Type d'indemnité inconnu."], 422);
        }

        $membres = $this->construireMembres($type, $objet, $session, $centre);

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
    private function construireMembres(string $type, ?string $objet, ?string $session, ?string $centre): ?\Illuminate\Support\Collection
    {
        $filtres = array_filter(['objet' => $objet, 'session' => $session]);
        $resultat = $this->convocations->liste(array_merge($filtres, ['per_page' => 100]));

        if (! $resultat['success']) {
            return null;
        }

        $items = $resultat['data']['data'] ?? [];
        $membres = collect();

        foreach ($items as $item) {
            $convocationId = $item['id'] ?? null;

            if (! $convocationId) {
                continue;
            }

            $lignes = match ($type) {
                'frais_deplacement' => $this->lignesFraisDeplacement($convocationId),
                'indemnite_correction' => $this->lignesIndemniteCorrection($convocationId),
                'indemnite_surveillance' => $this->lignesIndemniteSurveillance($convocationId),
                default => [],
            };

            foreach ($lignes as $ligne) {
                if ($centre && Str::lower($ligne['centre'] ?? '') !== Str::lower($centre)) {
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
        $filtres = array_filter([
            'type' => $request->query('type'),
            'objet' => $request->query('objet'),
            'session' => $request->query('session'),
            'centre' => $request->query('centre'),
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
        $resultat = $this->enseignants->trouver($id);

        if (! $resultat['success']) {
            return response()->json([
                'success' => false,
                'message' => $resultat['message'] ?? 'Enseignant introuvable.',
            ], $resultat['status'] ?? 404);
        }

        $enseignant = $resultat['data'];

        return response()->json(['success' => true, 'data' => [
            'code_banque' => $enseignant['code_banque'] ?? null,
            'code_guichet' => $enseignant['code_guichet'] ?? null,
            'numero_compte_bancaire' => $enseignant['numero_compte_bancaire'] ?? null,
            'cle_rib' => $enseignant['cle_rib'] ?? null,
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
        $centre = $request->query('centre');

        $retour = array_filter(compact('type', 'objet', 'session', 'centre'));

        if (! $type || (! $objet && ! $session) || ! array_key_exists($type, self::TYPES_INDEMNITE)) {
            return redirect()
                ->route('indemnites.etats-paie', $retour)
                ->with('error', "Choisissez un type d'indemnité et un objet ou une session avant de générer la fiche.");
        }

        $membres = $this->construireMembres($type, $objet, $session, $centre);

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
            'centre' => $centre,
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
     */
    private function lignesFraisDeplacement(int|string $convocationId): array
    {
        $resultat = $this->fraisDeplacement->beneficiairesEligibles($convocationId);
        $complets = $resultat['success'] ? ($resultat['data']['complets'] ?? []) : [];

        return array_map(function (array $ligne) {
            $ligne['montant'] = $ligne['fiche_montant'] ?? 0;

            return $ligne;
        }, $complets);
    }

    /**
     * Demande utilisatrice : "lorsque le type = correction affiche
     * uniquement les membres correction et les président de jury et de
     * centre" — le chef de centre/président de jury n'ont pas de ligne
     * dans indemnites_correction (ils ne corrigent pas de copies), donc
     * pas de montant pour eux ici ; on les ajoute quand même à la liste
     * (montant à 0) en les récupérant via lignesChefEtPresident().
     */
    private function lignesIndemniteCorrection(int|string $convocationId): array
    {
        $resultat = $this->indemniteCorrection->correcteursEligibles($convocationId);
        $correcteurs = $resultat['success'] ? ($resultat['data'] ?? []) : [];

        return array_merge($correcteurs, $this->lignesChefEtPresident($convocationId));
    }

    private function lignesIndemniteSurveillance(int|string $convocationId): array
    {
        $resultat = $this->indemniteSurveillance->surveillantsEligibles($convocationId);

        return $resultat['success'] ? ($resultat['data'] ?? []) : [];
    }

    /**
     * Chef de centre et président de jury d'une convocation, quel que soit
     * l'état de leur dossier de pièces justificatives (contrairement à
     * lignesFraisDeplacement() ci-dessus, ce filtre-là ne s'applique pas
     * ici) — récupérés via beneficiairesEligibles() car c'est la seule
     * source exposant déjà leur identité/catégorie, montant forcé à 0
     * puisqu'ils n'ont pas de ligne dans indemnites_correction.
     */
    private function lignesChefEtPresident(int|string $convocationId): array
    {
        $resultat = $this->fraisDeplacement->beneficiairesEligibles($convocationId);

        if (! $resultat['success']) {
            return [];
        }

        $toutes = array_merge($resultat['data']['complets'] ?? [], $resultat['data']['incomplets'] ?? []);

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
