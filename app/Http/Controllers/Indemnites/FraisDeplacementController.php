<?php

namespace App\Http\Controllers\Indemnites;

use App\Http\Controllers\Controller;
use App\Services\Api\Indemnites\ConvocationService;
use App\Services\Api\Indemnites\FraisDeplacementService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

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

    public function store(Request $request): RedirectResponse
    {
        $donnees = $request->except(['_token', 'fichier']);

        $resultat = $this->fraisDeplacement->creer($donnees, $request->file('fichier'));

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
