<?php

namespace App\Http\Controllers\Indemnites;

use App\Http\Controllers\Controller;
use App\Services\Api\Indemnites\ConvocationService;
use App\Services\Api\Indemnites\IndemniteSurveillanceService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class IndemniteSurveillanceController extends Controller
{
    public function __construct(
        protected IndemniteSurveillanceService $indemniteSurveillance,
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
            'surveillants' => 0,
            'fiches_creees' => 0,
            'heures_surveillees' => 0,
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
                session()->flash('error', $resultat['message'] ?? 'Impossible de charger les indemnités de surveillance.');
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

        return view('pages.indemnites.calcul-surveillance', [
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

    private function construireLignes(array $items, ?string $filtreCentre = null): array
    {
        $lignes = [];

        foreach ($items as $item) {
            $convocationId = $item['id'] ?? null;

            if (! $convocationId) {
                continue;
            }

            $resultat = $this->indemniteSurveillance->surveillantsEligibles($convocationId);
            $surveillants = $resultat['success'] ? ($resultat['data'] ?? []) : [];

            foreach ($surveillants as $surveillant) {
                if ($filtreCentre && ! str_contains(
                    Str::lower($surveillant['centre'] ?? ''),
                    Str::lower($filtreCentre)
                )) {
                    continue;
                }

                $lignes[] = [
                    'convocation_id' => $convocationId,
                    'objet' => $item['objet'] ?? null,
                    'session' => $item['session'] ?? null,
                    'centre' => $surveillant['centre'] ?? null,
                    'centre_id' => $surveillant['centre_id'] ?? null,
                    'metier' => $surveillant['metier'] ?? null,
                    'id' => $surveillant['id'] ?? null,
                    'nom' => $surveillant['nom'] ?? null,
                    'prenom' => $surveillant['prenom'] ?? null,
                    'matricule' => $surveillant['matricule'] ?? null,
                    'indemnite_surveillance_id' => $surveillant['indemnite_surveillance_id'] ?? null,
                    'nombre_heures' => $surveillant['nombre_heures'] ?? null,
                    'montant' => $surveillant['montant'] ?? null,
                    'statut' => $surveillant['statut'] ?? null,
                ];
            }
        }

        return $lignes;
    }

    private function calculerStats(array $lignes): array
    {
        $ficheCreees = 0;
        $heuresSurveillees = 0;
        $montantTotal = 0;

        foreach ($lignes as $ligne) {
            if (empty($ligne['indemnite_surveillance_id'])) {
                continue;
            }

            $ficheCreees++;
            $heuresSurveillees += (float) ($ligne['nombre_heures'] ?? 0);
            $montantTotal += (float) ($ligne['montant'] ?? 0);
        }

        return [
            'surveillants' => count($lignes),
            'fiches_creees' => $ficheCreees,
            'heures_surveillees' => $heuresSurveillees,
            'montant_total' => $montantTotal,
        ];
    }

    public function calculGroupe(Request $request): View|RedirectResponse
    {
        $convocationId = $request->query('convocation_id');
        $centreId = $request->query('centre_id');

        if (! $convocationId || ! $centreId) {
            return redirect()
                ->route('indemnites.calcul-surveillance')
                ->with('error', 'Choisissez une convocation puis un centre pour calculer les indemnités de surveillance.');
        }

        $convocationResultat = $this->convocations->trouver($convocationId);

        if (! $convocationResultat['success']) {
            return redirect()
                ->route('indemnites.calcul-surveillance')
                ->with('error', $convocationResultat['message'] ?? 'Convocation introuvable.');
        }

        $resultat = $this->indemniteSurveillance->surveillantsEligibles($convocationId, $centreId);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.calcul-surveillance')
                ->with('error', $resultat['message'] ?? 'Impossible de charger les surveillants éligibles.');
        }

        $surveillants = collect($resultat['data'] ?? []);

        $centreNom = $surveillants->first()['centre'] ?? ($convocationResultat['data']['centres'][0]['centre'] ?? null);

        $groupesMetier = $surveillants
            ->groupBy('metier')
            ->map(fn ($groupe, $metier) => [
                'metier' => $metier,
                'surveillants' => $groupe->values()->all(),
            ])
            ->values();

        return view('pages.indemnites.calcul-surveillance-groupe', [
            'convocation' => $convocationResultat['data'],
            'convocationId' => $convocationId,
            'centreId' => $centreId,
            'centreNom' => $centreNom,
            'groupesMetier' => $groupesMetier,
        ]);
    }

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
                'nombre_heures' => $ligne['nombre_heures'],
                'tarif_horaire' => $ligne['tarif_horaire'],
            ];
        }

        if (empty($lignes)) {
            return redirect()
                ->route('indemnites.calcul-surveillance.groupe', ['convocation_id' => $convocationId, 'centre_id' => $centreId])
                ->with('error', 'Sélectionnez au moins un surveillant à calculer.');
        }

        $resultat = $this->indemniteSurveillance->calculerGroupe($convocationId, $lignes);

        if (! $resultat['success']) {
            return redirect()
                ->route('indemnites.calcul-surveillance.groupe', ['convocation_id' => $convocationId, 'centre_id' => $centreId])
                ->with('error', $resultat['message'] ?? "Échec du calcul des indemnités de surveillance.");
        }

        $creees = $resultat['data']['creees'] ?? 0;
        $erreurs = $resultat['data']['erreurs'] ?? [];

        $message = $creees > 0
            ? "{$creees} indemnité(s) de surveillance créée(s)."
            : "Aucune indemnité n'a pu être créée.";

        return redirect()
            ->route('indemnites.calcul-surveillance.groupe', ['convocation_id' => $convocationId, 'centre_id' => $centreId])
            ->with(empty($erreurs) ? 'success' : 'error', empty($erreurs) ? $message : $message.' Erreurs : '.implode(' — ', $erreurs));
    }
}
