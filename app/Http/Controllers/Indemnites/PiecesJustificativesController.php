<?php

namespace App\Http\Controllers\Indemnites;

use App\Http\Controllers\Controller;
use App\Services\Api\Indemnites\ConvocationService;
use App\Services\Api\Indemnites\PieceJustificativeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Page "Pièces justificatives" : un membre (enseignant du jury, chef de
 * centre ou président du jury — voir ConvocationsController::index() côté
 * back, commentaire sur l'eager loading centres.chefCentre/presidentJury)
 * par ligne, avec son dossier de 6 pièces (5 déposées manuellement + le
 * dossier de convocation, rattaché automatiquement côté back).
 *
 * NB : cette page utilisait une vue déjà écrite (pieces-justificatives.blade.php,
 * ~40 Ko) mais dont la route était restée un simple Route::view() sans
 * aucune donnée — d'où l'erreur "Undefined variable $filtreActif". Ce
 * contrôleur a été reconstruit en lisant entièrement la vue existante pour
 * en extraire le contrat de données exact (variables, clés de tableaux,
 * routes attendues).
 */
class PiecesJustificativesController extends Controller
{
    /**
     * Les 5 types de pièces déposées manuellement (voir
     * DeposerLotPiecesJustificativesRequest côté back) — le 6e type,
     * "dossier_convocation", n'apparaît jamais dans ce dossier de 5 lignes
     * puisqu'il est rattaché automatiquement, jamais déposé à la main.
     */
    private const TYPES_MANUELS = [
        'service_fait' => 'Service fait',
        'ordre_mission' => 'Ordre de mission',
        'rapport_mission' => 'Rapport de mission',
        'bulletin_salaire' => 'Bulletin de salaire',
        'accuse_reception' => 'Accusé de réception',
    ];

    /**
     * Les 5 manuels + le "dossier de convocation" auto-rattaché (voir
     * PieceJustificativesController::attacherDossierConvocation() côté
     * back) — c'est la définition COMPLÈTE d'un dossier, celle utilisée par
     * FraisDeplacementController::beneficiairesEligibles() (back) pour
     * decider si un membre est eligible. A utiliser pour la
     * lecture/l'affichage du dossier (badge "complet", modal "Voir le
     * dossier") — TYPES_MANUELS reste utilisé uniquement pour le formulaire
     * de dépôt (ce 6e type ne se dépose jamais à la main). Avant ce
     * correctif, le badge "X/5 déposées" pouvait afficher "complet" pour un
     * dossier auquel il manquait en réalité le dossier de convocation, ce
     * qui le rendait quand même inéligible côté Frais de déplacement — sans
     * aucun moyen de le voir sur cette page.
     */
    private const TYPES_TOUS = self::TYPES_MANUELS + [
        'dossier_convocation' => 'Dossier de convocation (automatique)',
    ];

    public function __construct(
        protected ConvocationService $convocations,
        protected PieceJustificativeService $pieces
    ) {}

    public function index(Request $request): View
    {
        $filtres = array_filter([
            'session' => $request->query('session'),
            'objet' => $request->query('objet'),
            'centre' => $request->query('centre'),
        ]);

        $filtreActif = count($filtres) > 0;

        $optionsFiltres = $this->optionsFiltresPourVue($filtres);

        $membres = [];
        $stats = [
            'total_dossiers' => 0,
            'sessions_avec_pieces' => 0,
            'sessions_sans_pieces' => 0,
            'pieces_rejetees' => 0,
        ];

        // Paginator vide par defaut (filtre inactif : la vue n'affiche pas
        // le tableau, mais boucle quand meme sur $convocations->... plus
        // bas dans la pagination — un LengthAwarePaginator vide evite tout
        // "Call to a member function on null").
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
                session()->flash('error', $resultat['message'] ?? 'Impossible de charger les dossiers.');
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

            $membres = $this->construireMembres($items, $filtres['centre'] ?? null);

            $stats = $this->calculerStats($membres);
        }

        return view('pages.indemnites.pieces-justificatives', [
            'filtreActif' => $filtreActif,
            'optionsFiltres' => $optionsFiltres,
            'membres' => $membres,
            'stats' => $stats,
            'convocations' => $convocations,
        ]);
    }

    private function optionsFiltresPourVue(array $filtres = []): array
    {
        $resultat = $this->convocations->optionsFiltres($filtres);

        $vide = ['objets' => [], 'sessions' => [], 'centres' => []];

        return $resultat['success'] ? array_merge($vide, $resultat['data'] ?? []) : $vide;
    }

    /**
     * Aplatit les convocations (avec leurs centres imbriqués) en une ligne
     * par membre : chaque enseignant de la table pivot, PLUS le chef de
     * centre et le président du jury de chaque centre (qui déposent eux
     * aussi leurs pièces justificatives — voir le commentaire sur
     * ConvocationsController::index() côté back).
     *
     * NB : un appel API par membre pour récupérer son dossier de pièces
     * (N+1 sur la page courante) — même limitation déjà acceptée ailleurs
     * dans ce module (ex. stats de ConvocationsController::index() côté
     * front, calculées sur la page courante uniquement).
     *
     * $filtreCentre : le filtre "centre" (ConvocationsController::index()
     * côté back) ne s'applique qu'au niveau de la CONVOCATION — whereHas()
     * renvoie toute convocation ayant AU MOINS un centre correspondant, y
     * compris ses AUTRES centres qui ne correspondent pas. Sans ce filtre
     * réappliqué ici centre par centre, une convocation à plusieurs centres
     * affichait les membres de tous ses centres dès qu'un seul filtrait,
     * même ceux d'un centre non sélectionné (ex: filtrer "CFP Pikine"
     * affichait aussi les membres de "CFP Dakar" sur la même convocation).
     */
    private function construireMembres(array $items, ?string $filtreCentre = null): array
    {
        $membres = [];

        foreach ($items as $item) {
            $centres = $item['centres'] ?? [];

            foreach ($centres as $centre) {
                if ($filtreCentre && ! str_contains(
                    Str::lower($centre['centre'] ?? ''),
                    Str::lower($filtreCentre)
                )) {
                    continue;
                }

                $centreCommun = [
                    'convocation_id' => $item['id'] ?? null,
                    'centre_id' => $centre['id'] ?? null,
                    'centre' => $centre['centre'] ?? null,
                    'objet' => $item['objet'] ?? null,
                    'session' => $item['session'] ?? null,
                ];

                // "Jury" = le champ dédié du tableau Centres d'examen (ex:
                // "Jury 1"), le MÊME pour tout le monde sur ce centre — pas
                // le nom du centre, ni le rôle de la personne (bug corrigé :
                // ces deux anciens repères faisaient passer "CFP Ziguinchor"
                // ou "Chef de centre" pour la valeur de "Jury").
                $jury = $centre['jury'] ?? null;

                if (! empty($centre['chef_centre'])) {
                    $membres[] = $this->construireMembre(
                        $centreCommun,
                        array_merge($centre['chef_centre'], ['provenance_override' => $centre['chef_centre_provenance'] ?? null]),
                        'Chef de centre',
                        $jury
                    );
                }

                if (! empty($centre['president_jury'])) {
                    $membres[] = $this->construireMembre(
                        $centreCommun,
                        array_merge($centre['president_jury'], ['provenance_override' => $centre['president_jury_provenance'] ?? null]),
                        'Président du jury',
                        $jury
                    );
                }

                foreach ($item['enseignants'] ?? [] as $enseignant) {
                    $pivotCentreId = $enseignant['pivot']['centre_id'] ?? null;

                    if ($pivotCentreId && (int) $pivotCentreId !== (int) ($centre['id'] ?? 0)) {
                        continue;
                    }

                    // Convocation a un seul centre : pas d'ambiguite meme
                    // sans centre_id explicite sur le pivot (ancien format).
                    if (! $pivotCentreId && count($centres) > 1) {
                        continue;
                    }

                    $membres[] = $this->construireMembre(
                        $centreCommun,
                        $enseignant,
                        $enseignant['pivot']['fonction'] ?? 'Membre du jury',
                        $jury
                    );
                }
            }
        }

        return $membres;
    }

    private function construireMembre(array $centreCommun, array $enseignant, string $fonction, ?string $jury): array
    {
        $enseignantId = $enseignant['id'] ?? null;
        $convocationId = $centreCommun['convocation_id'] ?? null;

        $dossier = $this->recupererDossier($convocationId, $enseignantId, $centreCommun['centre_id'] ?? null);

        $deposees = collect($dossier)->filter(fn ($piece) => ! empty($piece['statut']))->count();
        $complet = collect($dossier)->every(fn ($piece) => ! empty($piece['statut']) && $piece['statut'] !== 'rejete');

        return array_merge($centreCommun, [
            'enseignant_id' => $enseignantId,
            'nom' => $enseignant['nom'] ?? null,
            'prenom' => $enseignant['prenom'] ?? null,
            'fonction' => $fonction,
            'type_convocation' => $this->determinerTypeConvocation($fonction),
            'jury' => $jury,
            // Priorité : provenance dédiée du centre pour un chef de
            // centre/président de jury (colonne chef_centre_provenance /
            // president_jury_provenance, injectée par construireMembres()
            // sous 'provenance_override' — ces deux rôles n'ont pas de
            // ligne pivot) > provenance saisie pour CETTE convocation
            // (pivot convocation_enseignant.provenance, ex: import Word,
            // pour un membre du jury ordinaire) > lieu de service permanent
            // de l'enseignant (souvent vide en pratique) — même priorité
            // que FraisDeplacementController::provenanceEnseignant() côté back.
            'provenance' => $enseignant['provenance_override'] ?? $enseignant['pivot']['provenance'] ?? $enseignant['lieu_service']['libelle'] ?? null,
            'dossier_complet' => $complet,
            // Denominateur dynamique (count($dossier), pas TYPES_MANUELS) :
            // $dossier contient maintenant les 6 types (voir
            // recupererDossier()), donc ce resume affiche "X/6" et reflete
            // la vraie definition de "complet" (celle du back).
            'dossier_resume' => $deposees.'/'.count($dossier).' pièces déposées',
            'dossier' => $dossier,
        ]);
    }

    /**
     * Chaque membre a son propre type de convocation (Président de jury,
     * Président de centre, Correction, Surveillance), déduit de sa fonction
     * — il n'y a plus de type unique choisi pour toute la convocation à
     * l'import (voir modal "Importer une convocation"). "Membre du jury"
     * n'a pas de type dédié (retourne null, affiché "—" dans la vue).
     *
     * L'ordre des tests compte pour les anciennes données saisies avec la
     * fonction "Surveillant/correcteur" (avant la séparation en deux
     * options distinctes) : elles contiennent à la fois "surveillant" et
     * "correcteur", et retombent sur "Correction" faute de mieux.
     */
    private function determinerTypeConvocation(string $fonction): ?string
    {
        $normalisee = Str::of($fonction)->lower()->ascii()->toString();

        return match (true) {
            str_contains($normalisee, 'president') && str_contains($normalisee, 'jury') => 'Président de jury',
            str_contains($normalisee, 'chef') && str_contains($normalisee, 'centre') => 'Président de centre',
            str_contains($normalisee, 'president') && str_contains($normalisee, 'centre') => 'Président de centre',
            str_contains($normalisee, 'correct') => 'Correction',
            str_contains($normalisee, 'surveill') => 'Surveillance',
            default => null,
        };
    }

    /**
     * Construit les 6 lignes du dossier (une par type attendu, meme si non
     * deposee — les 5 manuels + le dossier de convocation auto-rattache)
     * pour un membre — voir modal "Voir le dossier" cote vue.
     */
    private function recupererDossier(?int $convocationId, ?int $enseignantId, ?int $centreId): array
    {
        $parType = [];

        if ($convocationId && $enseignantId) {
            // PAS de filtre centre_id ici : les pieces (service fait, ordre
            // de mission, bulletin de salaire...) sont attachees a la
            // PERSONNE pour cette convocation, pas a un centre precis — un
            // enseignant membre de PLUSIEURS centres/metiers d'une meme
            // convocation ne depose ces documents qu'UNE fois. centre_id
            // sur piece_justificatives n'est qu'une info de tracking (sous
            // quel centre le depot a ete fait), pas une cle de partition :
            // filtrer dessus ici faisait apparaitre un dossier "incomplet"
            // sur les lignes des AUTRES centres de la meme personne, alors
            // que ses documents existaient deja (deposes depuis une autre
            // ligne/centre). Coherent avec FraisDeplacementController::
            // beneficiairesEligibles() (back), qui calcule deja la
            // completude par (convocation_id, enseignant_id) sans centre_id.
            $resultat = $this->pieces->liste(array_filter([
                'convocation_id' => $convocationId,
                'enseignant_id' => $enseignantId,
            ]));

            if ($resultat['success']) {
                $pieces = $resultat['data']['data'] ?? $resultat['data'] ?? [];

                foreach ($pieces as $piece) {
                    $type = $piece['type'] ?? null;

                    if ($type && array_key_exists($type, self::TYPES_TOUS)) {
                        $parType[$type] = $piece;
                    }
                }
            }
        }

        $dossier = [];

        foreach (self::TYPES_TOUS as $type => $label) {
            $piece = $parType[$type] ?? null;

            $dossier[] = [
                'type' => $type,
                'label' => $label,
                'statut' => $piece['statut'] ?? null,
                'date_depot' => $piece['date_depot'] ?? null,
                'id' => $piece['id'] ?? null,
                // Pour pre-remplir la modale "Modifier" avec le fichier
                // deja en place (demande utilisatrice) — affiche a la place
                // du placeholder "Cliquez pour joindre un fichier".
                'nom_original' => $piece['nom_original'] ?? null,
            ];
        }

        return $dossier;
    }

    /**
     * Stats calculees sur la page courante uniquement (le back ne fournit
     * pas d'agregat dedie) — meme limitation deja acceptee sur les stats de
     * ConvocationsController::index() cote front.
     */
    private function calculerStats(array $membres): array
    {
        $sessionsAvecPieces = [];
        $sessionsSansPieces = [];
        $piecesRejetees = 0;

        foreach ($membres as $membre) {
            $session = $membre['session'] ?? null;
            $aAuMoinsUnePiece = collect($membre['dossier'] ?? [])->contains(fn ($piece) => ! empty($piece['statut']));

            if ($session) {
                if ($aAuMoinsUnePiece) {
                    $sessionsAvecPieces[$session] = true;
                } else {
                    $sessionsSansPieces[$session] = true;
                }
            }

            foreach ($membre['dossier'] ?? [] as $piece) {
                if (($piece['statut'] ?? null) === 'rejete') {
                    $piecesRejetees++;
                }
            }
        }

        return [
            'total_dossiers' => count($membres),
            'sessions_avec_pieces' => count($sessionsAvecPieces),
            'sessions_sans_pieces' => count($sessionsSansPieces),
            'pieces_rejetees' => $piecesRejetees,
        ];
    }

    public function deposer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'convocation_id' => ['required', 'integer'],
            'enseignant_id' => ['required', 'integer'],
            'centre_id' => ['nullable', 'integer'],
            'service_fait' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:100'],
            'ordre_mission' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:100'],
            'rapport_mission' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:100'],
            'bulletin_salaire' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:100'],
            'accuse_reception' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:100'],
        ]);

        $donnees = array_filter([
            'convocation_id' => $data['convocation_id'],
            'enseignant_id' => $data['enseignant_id'],
            'centre_id' => $data['centre_id'] ?? null,
        ]);

        $fichiers = [
            'service_fait' => $request->file('service_fait'),
            'ordre_mission' => $request->file('ordre_mission'),
            'rapport_mission' => $request->file('rapport_mission'),
            'bulletin_salaire' => $request->file('bulletin_salaire'),
            'accuse_reception' => $request->file('accuse_reception'),
        ];

        $resultat = $this->pieces->deposerLot($donnees, $fichiers);

        if (! $resultat['success']) {
            return redirect()
                ->back()
                ->with('error', $resultat['message'] ?? "Échec du dépôt des pièces justificatives.");
        }

        return redirect()
            ->back()
            ->with('success', 'Pièces justificatives déposées avec succès.');
    }

    /**
     * Modification du dossier d'UN membre — bouton "Modifier" du tableau,
     * meme formulaire complet que deposer() ci-dessus (demande
     * utilisatrice : "en cliquant sur modifier il doit afficher le
     * formulaire complet en recuperant le fichier qui y etait"), mais les 5
     * fichiers sont optionnels : seuls ceux fournis remplacent la piece
     * existante, les autres restent tels quels.
     */
    public function modifier(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'convocation_id' => ['required', 'integer'],
            'enseignant_id' => ['required', 'integer'],
            'centre_id' => ['nullable', 'integer'],
            'service_fait' => ['sometimes', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:100'],
            'ordre_mission' => ['sometimes', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:100'],
            'rapport_mission' => ['sometimes', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:100'],
            'bulletin_salaire' => ['sometimes', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:100'],
            'accuse_reception' => ['sometimes', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:100'],
        ]);

        $donnees = array_filter([
            'convocation_id' => $data['convocation_id'],
            'enseignant_id' => $data['enseignant_id'],
            'centre_id' => $data['centre_id'] ?? null,
        ]);

        $fichiers = [
            'service_fait' => $request->file('service_fait'),
            'ordre_mission' => $request->file('ordre_mission'),
            'rapport_mission' => $request->file('rapport_mission'),
            'bulletin_salaire' => $request->file('bulletin_salaire'),
            'accuse_reception' => $request->file('accuse_reception'),
        ];

        $resultat = $this->pieces->modifierLot($donnees, $fichiers);

        if (! $resultat['success']) {
            return redirect()
                ->back()
                ->with('error', $resultat['message'] ?? 'Échec de la modification du dossier.');
        }

        return redirect()
            ->back()
            ->with('success', 'Dossier modifié avec succès.');
    }

    /**
     * Boutons "Valider"/"Rejeter" de la modale "Voir le dossier" — appelés
     * en AJAX (fetch), voir remplirDossier() côté vue : réponse JSON directe
     * plutôt qu'une redirection, pour mettre à jour le badge de statut sans
     * recharger la page ni fermer la modale.
     */
    public function valider(string $id): JsonResponse
    {
        $resultat = $this->pieces->valider($id);

        return response()->json([
            'success' => $resultat['success'],
            'message' => $resultat['message'] ?? null,
            'statut' => $resultat['success'] ? 'valide' : null,
        ], $resultat['success'] ? 200 : ($resultat['status'] ?? 422));
    }

    public function rejeter(Request $request, string $id): JsonResponse
    {
        $data = $request->validate([
            'commentaire_rejet' => ['required', 'string', 'max:1000'],
        ]);

        $resultat = $this->pieces->rejeter($id, $data['commentaire_rejet']);

        return response()->json([
            'success' => $resultat['success'],
            'message' => $resultat['message'] ?? null,
            'statut' => $resultat['success'] ? 'rejete' : null,
        ], $resultat['success'] ? 200 : ($resultat['status'] ?? 422));
    }

    public function telecharger(string $id): StreamedResponse
    {
        $reponse = $this->pieces->telecharger($id);

        return response()->streamDownload(function () use ($reponse) {
            echo $reponse->body();
        }, 'piece-justificative-'.$id, [
            'Content-Type' => $reponse->header('Content-Type') ?: 'application/octet-stream',
        ]);
    }
}
