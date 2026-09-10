<?php

namespace App\Http\Controllers\Indemnites;

use App\Http\Controllers\Controller;
use App\Services\Api\Indemnites\ConvocationService;
use App\Services\Api\Indemnites\IndemniteCorrectionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * "Indemnités de correction" : montant = nombre de copies corrigées x taux
 * par copie, pour les membres ayant la fonction Correction — pas de fiche à
 * remplir (demande utilisatrice), un calcul groupé directement enregistré.
 * Même plan que Frais de déplacement/Pièces justificatives : stats +
 * filtres session/objet/centre, tableau caché tant qu'aucun filtre n'est
 * choisi. "Calcul groupé" est toujours scopé à UN centre d'UNE convocation
 * (demande utilisatrice : "pour tout les membres qui sont dans le meme
 * centre dans la meme convocation") — le taux varie par métier, saisi
 * librement à chaque calcul (pas de barème préconfiguré).
 */
class IndemniteCorrectionController extends Controller
{
    public function __construct(
        protected IndemniteCorrectionService $indemniteCorrection,
        protected ConvocationService $convocations
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

        $lignes = [];
        $stats = [
            'correcteurs' => 0,
            'fiches_creees' => 0,
            'copies_corrigees' => 0,
            'montant_total' => 0,
        ];

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
                session()->flash('error', $resultat['message'] ?? 'Impossible de charger les indemnités de correction.');
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

            $lignes = $this->construireLignes($items, $filtres['centre'] ?? null);
            $stats = $this->calculerStats($lignes);
        }

        return view('pages.indemnites.calcul', [
            'filtreActif' => $filtreActif,
            'optionsFiltres' => $optionsFiltres,
            'lignes' => $lignes,
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
     * Aplatit les convocations de la page en une ligne par correcteur — un
     * appel back par convocation (correcteursEligibles), même limitation
     * "page courante" déjà acceptée sur Frais de déplacement/Pièces
     * justificatives.
     */
    private function construireLignes(array $items, ?string $filtreCentre = null): array
    {
        $lignes = [];

        foreach ($items as $item) {
            $convocationId = $item['id'] ?? null;

            if (! $convocationId) {
                continue;
            }

            $resultat = $this->indemniteCorrection->correcteursEligibles($convocationId);
            $correcteurs = $resultat['success'] ? ($resultat['data'] ?? []) : [];

            foreach ($correcteurs as $correcteur) {
                if ($filtreCentre && ! str_contains(
                    Str::lower($correcteur['centre'] ?? ''),
                    Str::lower($filtreCentre)
                )) {
                    continue;
                }

                $lignes[] = [
                    'convocation_id' => $convocationId,
                    'objet' => $item['objet'] ?? null,
                    'session' => $item['session'] ?? null,
                    'centre' => $correcteur['centre'] ?? null,
                    'centre_id' => $correcteur['centre_id'] ?? null,
                    'metier' => $correcteur['metier'] ?? null,
                    'id' => $correcteur['id'] ?? null,
                    'nom' => $correcteur['nom'] ?? null,
                    'prenom' => $correcteur['prenom'] ?? null,
                    'matricule' => $correcteur['matricule'] ?? null,
                    'indemnite_correction_id' => $correcteur['indemnite_correction_id'] ?? null,
                    'nombre_copies' => $correcteur['nombre_copies'] ?? null,
                    'montant' => $correcteur['montant'] ?? null,
                    'statut' => $correcteur['statut'] ?? null,
                ];
            }
        }

        return $lignes;
    }

    private function calculerStats(array $lignes): array
    {
        $ficheCreees = 0;
        $copiesCorrigees = 0;
        $montantTotal = 0;

        foreach ($lignes as $ligne) {
            if (empty($ligne['indemnite_correction_id'])) {
                continue;
            }

            $ficheCreees++;
            $copiesCorrigees += (int) ($ligne['nombre_copies'] ?? 0);
            $montantTotal += (float) ($ligne['montant'] ?? 0);
        }

        return [
            'correcteurs' => count($lignes),
            'fiches_creees' => $ficheCreees,
            'copies_corrigees' => $copiesCorrigees,
            'montant_total' => $montantTotal,
        ];
    }

    /**
     * Calcul groupé — toujours scopé à UN centre d'UNE convocation (demande
     * utilisatrice), groupé par métier côté vue (taux propre à chaque
     * métier, pré-remplit les lignes de ce métier).
     */
    public function calculGroupe(Request $request): View|RedirectResponse
    {
        $convocationId = $request->query('convocation_id');
        $centreId = $request->query('centre_id');

        if (! $convocationId || ! $centreId) {
            return redirect()
                ->route('indemnites.calcul')
                ->with('error', 'Choisissez une convocation puis un centre pour calculer les indemnités de correction.');
        }

        $convocationResultat = $this->convocations->trouver($convocationId);

        if (! $convocationResultat['success']) {
            return redirect()
                ->route('indemnites.calcul')
                ->with('error', $convocationResultat['message'] ?? 'Convocation introuvable.');
        }

        $resultat = $this->indemniteCorrection->correcteursEligibles($convocationId, $centreId);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.calcul')
                ->with('error', $resultat['message'] ?? 'Impossible de charger les correcteurs éligibles.');
        }

        $correcteurs = collect($resultat['data'] ?? []);

        $centreNom = $correcteurs->first()['centre'] ?? ($convocationResultat['data']['centres'][0]['centre'] ?? null);

        $groupesMetier = $correcteurs
            ->groupBy('metier')
            ->map(fn ($groupe, $metier) => [
                'metier' => $metier,
                'correcteurs' => $groupe->values()->all(),
            ])
            ->values();

        return view('pages.indemnites.calcul-groupe', [
            'convocation' => $convocationResultat['data'],
            'convocationId' => $convocationId,
            'centreId' => $centreId,
            'centreNom' => $centreNom,
            'groupesMetier' => $groupesMetier,
        ]);
    }

    /**
     * Enregistre les indemnités de correction cochées — $lignes est un
     * tableau indexé numériquement (pas par enseignant_id) car un même
     * correcteur peut apparaître sur plusieurs métiers.
     */
    public function storeGroupe(Request $request): RedirectResponse
    {
        $convocationId = $request->input('convocation_id');
        $centreId = $request->input('centre_id');
        $lignesSoumises = $request->input('lignes', []);

        $lignes = [];

        foreach ($lignesSoumises as $ligne) {
            if (empty($ligne['checked'])) {
                continue;
            }

            $lignes[] = [
                'enseignant_id' => $ligne['enseignant_id'],
                'convocation_centre_id' => $ligne['convocation_centre_id'],
                'metier' => $ligne['metier'],
                'nombre_copies' => $ligne['nombre_copies'],
                'taux_copie' => $ligne['taux_copie'],
            ];
        }

        if (empty($lignes)) {
            return redirect()
                ->route('indemnites.calcul.groupe', ['convocation_id' => $convocationId, 'centre_id' => $centreId])
                ->with('error', 'Sélectionnez au moins un correcteur à calculer.');
        }

        $resultat = $this->indemniteCorrection->calculerGroupe($convocationId, $lignes);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.calcul.groupe', ['convocation_id' => $convocationId, 'centre_id' => $centreId])
                ->with('error', $resultat['message'] ?? "Échec du calcul des indemnités de correction.");
        }

        $creees = $resultat['data']['creees'] ?? 0;
        $erreurs = $resultat['data']['erreurs'] ?? [];

        $message = $creees > 0
            ? "{$creees} indemnité(s) de correction créée(s)."
            : "Aucune indemnité n'a pu être créée.";

        return redirect()
            ->route('indemnites.calcul.groupe', ['convocation_id' => $convocationId, 'centre_id' => $centreId])
            ->with(empty($erreurs) ? 'success' : 'error', empty($erreurs) ? $message : $message.' Erreurs : '.implode(' — ', $erreurs));
    }
}
