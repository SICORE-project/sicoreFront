<?php

namespace App\Http\Controllers\Indemnites;

use App\Http\Controllers\Controller;
use App\Services\Api\Indemnites\ConvocationService;
use App\Services\Api\Indemnites\PieceJustificativeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PiecesJustificativesController extends Controller
{
    /**
     * Memes 6 types que piece_justificatives::TYPES cote back (pas
     * d'acces direct au modele backend depuis le front, donc redefini
     * ici) — sert a construire le dossier complet (6 lignes, presentes ou
     * non) de chaque membre pour la modale "Voir le dossier".
     */
    private const TYPES_PIECES = [
        'service_fait' => 'Service fait',
        'ordre_mission' => 'Ordre de mission',
        'rapport_mission' => 'Rapport de mission',
        'bulletin_salaire' => 'Bulletin de salaire',
        'accuse_reception' => 'Accusé de réception',
        'dossier_convocation' => 'Dossier de convocation',
    ];

    public function __construct(
        protected ConvocationService $convocations,
        protected PieceJustificativeService $pieces
    ) {}

    /**
     * Cartes en haut de page : une ligne par (convocation x centre
     * d'examen), la session etant une colonne de la convocation plutot
     * qu'un axe separe — meme aplatissement que
     * ConvocationsController::construireLignesCentres().
     *
     * Le tableau, lui, reste CACHE tant qu'aucun filtre (session, objet ou
     * centre) n'est choisi — une fois un filtre choisi, il affiche les
     * MEMBRES (bénéficiaires) rattaches au centre/a l'objet selectionnes,
     * pas les dossiers eux-memes — voir construireMembres().
     *
     * Les pieces justificatives ne sont pour l'instant rattachees qu'a une
     * convocation (piece_justificatives.convocation_id), pas a un centre ni
     * a un membre precis — leur nombre (cartes du haut) est donc affiche
     * par convocation, reparti sur toutes ses lignes (centres), tant
     * qu'aucune colonne centre_id/enseignant_id n'existe sur cette table.
     */
    public function index(Request $request): View
    {
        $resultatFiltres = $this->convocations->filtres();
        $optionsFiltres = $resultatFiltres['success'] ? ($resultatFiltres['data'] ?? []) : [];

        $objet = $request->query('objet');
        $session = $request->query('session');
        $centre = $request->query('centre');

        $filtreActif = filled($objet) || filled($session) || filled($centre);

        $filtres = array_filter([
            'objet' => $objet,
            'session' => $session,
            'centre' => $centre,
            'per_page' => 10,
            'page' => $request->query('page', 1),
        ]);

        $resultatConvocations = $this->convocations->liste($filtres);

        $meta = $resultatConvocations['success'] ? ($resultatConvocations['data'] ?? []) : [];
        $items = $meta['data'] ?? [];

        if (! $resultatConvocations['success']) {
            session()->flash('error', $resultatConvocations['message'] ?? 'Impossible de charger les dossiers de pièces justificatives.');
        }

        // Meme reconstruction de paginator "a la Laravel" que
        // ConvocationsController::index() (l'API renvoie une pagination
        // data/current_page/per_page/total, pas un vrai paginateur).
        $convocationsPage = new LengthAwarePaginator(
            $items,
            $meta['total'] ?? count($items),
            $meta['per_page'] ?? 10,
            $meta['current_page'] ?? (int) $request->query('page', 1),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $resultatPieces = $this->pieces->liste(['per_page' => 200]);
        $pieces = $resultatPieces['success'] ? ($resultatPieces['data']['data'] ?? []) : [];

        $piecesParConvocation = collect($pieces)->groupBy('convocation_id');

        $lignes = $this->construireLignes($items, $piecesParConvocation);

        $stats = [
            'total_dossiers' => count($lignes),
            'sessions_avec_pieces' => collect($lignes)->where('nb_pieces', '>', 0)->count(),
            'sessions_sans_pieces' => collect($lignes)->where('nb_pieces', 0)->count(),
            'pieces_rejetees' => collect($pieces)->where('statut', 'rejete')->count(),
        ];

        // Cle "convocationId-enseignantId" : chaque piece est maintenant
        // rattachee a un membre precis (voir migration
        // add_enseignant_centre_to_piece_justificatives_table), donc son
        // dossier peut etre reconstitue individuellement pour la modale
        // "Voir le dossier".
        $piecesParMembre = collect($pieces)->groupBy(
            fn (array $p) => ($p['convocation_id'] ?? 'x').'-'.($p['enseignant_id'] ?? 'x')
        );

        // Un appel beneficiaires() par convocation affichee : cout accepte
        // vu le volume actuel (meme esprit que renderShow()/renderEdit()
        // dans ConvocationsController, qui enchainent deja plusieurs appels
        // API cote front pour assembler une page).
        $membres = $filtreActif ? $this->construireMembres($items, $centre, $piecesParMembre) : [];

        return view('pages.indemnites.pieces-justificatives', [
            'convocations' => $convocationsPage,
            'lignes' => $lignes,
            'stats' => $stats,
            'membres' => $membres,
            'filtreActif' => $filtreActif,
            'optionsFiltres' => $optionsFiltres,
        ]);
    }

    /**
     * Depot groupe des pieces d'UN membre depuis la modale "Ajouter une
     * pièce" (bouton par ligne du tableau) : 5 fichiers obligatoires, un
     * par type — le 6e ("Dossier de convocation") n'a pas de champ fichier
     * ici, il est toujours rattache automatiquement cote back a partir du
     * PDF deja genere pour la convocation (voir
     * PieceJustificativesController::attacherDossierConvocation()). Total
     * attendu : 6 documents par membre avant l'enregistrement.
     *
     * 100 Ko max par fichier (voir demande utilisateur).
     */
    public function deposerPieces(Request $request): RedirectResponse
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

        $fichiers = [];

        foreach (['service_fait', 'ordre_mission', 'rapport_mission', 'bulletin_salaire', 'accuse_reception'] as $champ) {
            if (! $request->hasFile($champ)) {
                continue;
            }

            $fichier = $request->file($champ);

            $fichiers[] = [
                'name' => $champ,
                'contents' => fopen($fichier->getRealPath(), 'r'),
                'filename' => $fichier->getClientOriginalName(),
            ];
        }

        $resultat = $this->pieces->deposerLot([
            'convocation_id' => $data['convocation_id'],
            'enseignant_id' => $data['enseignant_id'],
            'centre_id' => $data['centre_id'] ?? null,
        ], $fichiers);

        return redirect()
            ->back()
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'] ?? 'Erreur lors du dépôt des pièces.');
    }

    /**
     * Relaie le fichier binaire d'une pièce justificative (bouton
     * "Télécharger" de la modale "Voir le dossier") — meme principe que
     * ConvocationsController::downloadPdf(). Affiche le document dans le
     * navigateur (inline) plutot que de forcer un telechargement immediat —
     * voir demande utilisateur, et
     * PieceJustificativesController::download() cote back qui sert deja le
     * fichier avec ce Content-Disposition.
     */
    public function download(int|string $id): RedirectResponse|\Illuminate\Http\Response
    {
        $reponse = $this->pieces->telecharger($id);

        if (! $reponse->successful()) {
            return redirect()
                ->back()
                ->with('error', $reponse->json('message') ?? 'Impossible d\'afficher ce document.');
        }

        return response($reponse->body(), 200, [
            'Content-Type' => $reponse->header('Content-Type') ?: 'application/octet-stream',
            'Content-Disposition' => $reponse->header('Content-Disposition') ?: 'inline',
        ]);
    }

    /**
     * Aplati les convocations en lignes "une par centre" pour les cartes —
     * meme logique que ConvocationsController::construireLignesCentres(),
     * avec en plus le nombre de pieces deposees/validees pour la
     * convocation de cette ligne.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  Collection<int, Collection<int, array<string, mixed>>>  $piecesParConvocation
     * @return array<int, array<string, mixed>>
     */
    private function construireLignes(array $items, Collection $piecesParConvocation): array
    {
        $lignes = [];

        foreach ($items as $item) {
            $centres = $item['centres'] ?? [];
            $piecesConvocation = $piecesParConvocation->get($item['id'] ?? null, collect());

            $ligneCommune = [
                'convocation_id' => $item['id'] ?? null,
                'objet' => $item['objet'] ?? null,
                'session' => $item['session'] ?? null,
                'date_debut' => $item['date_debut'] ?? null,
                'date_fin' => $item['date_fin'] ?? null,
                'nb_pieces' => $piecesConvocation->count(),
                'nb_pieces_validees' => $piecesConvocation->where('statut', 'valide')->count(),
            ];

            if (empty($centres)) {
                $lignes[] = array_merge($ligneCommune, [
                    'centre_id' => null,
                    'centre' => null,
                ]);

                continue;
            }

            foreach ($centres as $centre) {
                $lignes[] = array_merge($ligneCommune, [
                    'centre_id' => $centre['id'] ?? null,
                    'centre' => $centre['centre'] ?? null,
                ]);
            }
        }

        return $lignes;
    }

    /**
     * Une ligne par personne devant deposer des pieces justificatives pour
     * un centre d'une convocation deja filtree par objet/session (filtrage
     * fait cote API, voir ConvocationsController::filtres()/index()) — le
     * filtre centre, lui, est applique ici puisqu'une convocation peut
     * avoir plusieurs centres et qu'on ne veut que les lignes du centre
     * choisi.
     *
     * Deux origines DISTINCTES de lignes, toutes deux necessaires :
     *  - les bénéficiaires (convocation_enseignant) : membres du jury
     *    explicitement rattaches a ce centre ;
     *  - le chef de centre ET le président du jury du centre lui-meme
     *    (ConvocationCentre.chef_centre_id / president_jury_id) : ce sont
     *    des roles, pas des "bénéficiaires" au sens pivot, mais ils doivent
     *    eux aussi deposer leurs propres pieces justificatives — d'ou une
     *    ligne (et un bouton "Ajouter une pièce") chacun, meme s'ils
     *    n'apparaissent pas dans la liste des bénéficiaires.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  Collection<string, Collection<int, array<string, mixed>>>  $piecesParMembre
     * @return array<int, array<string, mixed>>
     */
    private function construireMembres(array $items, ?string $centreFiltre, Collection $piecesParMembre): array
    {
        $membres = [];

        foreach ($items as $item) {
            $convocationId = $item['id'] ?? null;
            $typeConvocation = $item['type_convocation']['libelle'] ?? null;
            $centresParId = collect($item['centres'] ?? [])->keyBy('id');

            $resultatBeneficiaires = $this->convocations->beneficiaires($convocationId);
            $beneficiaires = $resultatBeneficiaires['success'] ? ($resultatBeneficiaires['data'] ?? []) : [];

            foreach ($beneficiaires as $beneficiaire) {
                $centreId = $beneficiaire['pivot']['centre_id'] ?? null;
                $centreObjet = $centreId ? $centresParId->get($centreId) : null;
                $centreNom = $centreObjet['centre'] ?? null;

                // Un membre non rattache a un centre precis (convocation
                // sans centre, ou membre ajoute avant l'existence du
                // rattachement) n'a pas sa place ici : cette page suit les
                // dossiers PAR centre, et l'afficher avec un centre vide
                // (tiret) donnait l'impression que le nom du centre
                // "ne s'affichait pas" plutot que de clairement l'exclure.
                if (! $centreId) {
                    continue;
                }

                if (filled($centreFiltre) && $centreNom !== $centreFiltre) {
                    continue;
                }

                $membres[] = $this->ligneMembre(
                    $convocationId,
                    $item,
                    $centreObjet,
                    $centreId,
                    $centreNom,
                    $typeConvocation,
                    $beneficiaire['id'] ?? null,
                    $beneficiaire['nom'] ?? null,
                    $beneficiaire['prenom'] ?? null,
                    $beneficiaire['pivot']['fonction'] ?? null,
                    $beneficiaire['pivot']['provenance'] ?? null,
                    $piecesParMembre
                );
            }

            foreach ($centresParId as $centreId => $centreObjet) {
                $centreNom = $centreObjet['centre'] ?? null;

                if (filled($centreFiltre) && $centreNom !== $centreFiltre) {
                    continue;
                }

                $chefCentre = $centreObjet['chef_centre'] ?? null;

                if ($chefCentre) {
                    $membres[] = $this->ligneMembre(
                        $convocationId,
                        $item,
                        $centreObjet,
                        $centreId,
                        $centreNom,
                        $typeConvocation,
                        $chefCentre['id'] ?? null,
                        $chefCentre['nom'] ?? null,
                        $chefCentre['prenom'] ?? null,
                        'Chef de centre',
                        null,
                        $piecesParMembre
                    );
                }

                $presidentJury = $centreObjet['president_jury'] ?? null;

                if ($presidentJury) {
                    $membres[] = $this->ligneMembre(
                        $convocationId,
                        $item,
                        $centreObjet,
                        $centreId,
                        $centreNom,
                        $typeConvocation,
                        $presidentJury['id'] ?? null,
                        $presidentJury['nom'] ?? null,
                        $presidentJury['prenom'] ?? null,
                        'Président du jury',
                        null,
                        $piecesParMembre
                    );
                }
            }
        }

        return $membres;
    }

    /**
     * @param  array<string, mixed>  $item  la convocation (item brut de l'API)
     * @param  array<string, mixed>|null  $centreObjet  le centre (item brut de l'API)
     * @param  Collection<string, Collection<int, array<string, mixed>>>  $piecesParMembre
     * @return array<string, mixed>
     */
    private function ligneMembre(
        ?int $convocationId,
        array $item,
        ?array $centreObjet,
        ?int $centreId,
        ?string $centreNom,
        ?string $typeConvocation,
        ?int $enseignantId,
        ?string $nom,
        ?string $prenom,
        ?string $fonction,
        ?string $provenance,
        Collection $piecesParMembre
    ): array {
        $dossier = $this->construireDossier($convocationId, $enseignantId, $piecesParMembre);
        $nbDeposees = collect($dossier)->whereNotNull('statut')->count();

        return [
            'convocation_id' => $convocationId,
            'enseignant_id' => $enseignantId,
            'objet' => $item['objet'] ?? null,
            'session' => $item['session'] ?? null,
            'type_convocation' => $typeConvocation,
            'centre_id' => $centreId,
            'centre' => $centreNom,
            'jury' => $centreObjet['jury'] ?? null,
            'nom' => $nom,
            'prenom' => $prenom,
            'fonction' => $fonction,
            'provenance' => $provenance,
            'dossier' => $dossier,
            'dossier_resume' => $nbDeposees.'/'.count(self::TYPES_PIECES).' déposés',
            'dossier_complet' => $nbDeposees === count(self::TYPES_PIECES),
        ];
    }

    /**
     * Les 5 types attendus (voir TYPES_PIECES), chacun rapproche de la
     * piece deja deposee pour ce membre le cas echeant — pour la modale
     * "Voir le dossier" (statut, date, lien de telechargement) et le
     * resume affiche dans le tableau ("X/5 déposés").
     *
     * @param  Collection<string, Collection<int, array<string, mixed>>>  $piecesParMembre
     * @return array<int, array<string, mixed>>
     */
    private function construireDossier(?int $convocationId, ?int $enseignantId, Collection $piecesParMembre): array
    {
        $pieces = $piecesParMembre->get($convocationId.'-'.$enseignantId, collect())->keyBy('type');

        $dossier = [];

        foreach (self::TYPES_PIECES as $type => $libelle) {
            $piece = $pieces->get($type);

            $dossier[] = [
                'type' => $type,
                'label' => $libelle,
                'id' => $piece['id'] ?? null,
                'statut' => $piece['statut'] ?? null,
                'date_depot' => $piece['date_depot'] ?? null,
                'nom_original' => $piece['nom_original'] ?? null,
            ];
        }

        return $dossier;
    }
}
