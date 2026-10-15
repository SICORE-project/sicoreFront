<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\CommuneService;
use App\Services\Parametrage\DepartementService;
use App\Services\Parametrage\RegionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommuneController extends Controller
{
    public function __construct(
        private readonly CommuneService $communeService,
        private readonly RegionService $regionService,
        private readonly DepartementService $departementService
    ) {
    }

    /**
     * Afficher la liste des communes.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $page = max(
            1,
            (int) $request->query('page', 1)
        );

        $perPage = max(
            1,
            min(
                (int) $request->query('per_page', 10),
                100
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Filtres Commune
        |--------------------------------------------------------------------------
        */

        $filters = [
            'search' => $request->query('search'),
            'region_id' => $request->query('region_id'),
            'departement_id' => $request->query('departement_id'),
            'est_actif' => $request->query('est_actif'),
        ];

        /*
        |--------------------------------------------------------------------------
        | Charger les communes
        |--------------------------------------------------------------------------
        */

        $result = $this->communeService->getAll(
            $page,
            $perPage,
            $filters
        );

        /*
        |--------------------------------------------------------------------------
        | Session backend expirée
        |--------------------------------------------------------------------------
        */

        if ($result['unauthorized'] ?? false) {
            $request->session()->forget([
                'access_token',
                'sicore_user',
            ]);

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Votre session a expiré. Veuillez vous reconnecter.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Charger les régions actives
        |--------------------------------------------------------------------------
        */

        $regionsResult = $this->regionService->getAll(
            1,
            100,
            [
                'est_actif' => true,
                'sort_by' => 'libelle',
                'sort_direction' => 'asc',
            ]
        );

        $regions = $regionsResult['items'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Charger les départements actifs
        |--------------------------------------------------------------------------
        */

        $departementsResult = $this->departementService->getAll(
            1,
            100,
            [
                'est_actif' => true,
                'sort_by' => 'libelle',
                'sort_direction' => 'asc',
            ]
        );

        $departements = $departementsResult['items'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Retourner la vue
        |--------------------------------------------------------------------------
        */

        return view(
            'pages.parametres.communes',
            [
                'communes' => $result['items'] ?? [],

                'pagination' => $result['pagination'] ?? [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total' => 0,
                    'per_page' => $perPage,
                ],

                'error' => $result['error'] ?? null,

                'regions' => $regions,

                'departements' => $departements,
            ]
        );
    }

    /**
     * Créer une commune.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'code' => [
                    'required',
                    'string',
                    'max:20',
                ],

                'libelle' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'region_id' => [
                    'required',
                    'integer',
                ],

                'departement_id' => [
                    'required',
                    'integer',
                ],
            ],
            [
                'code.required' =>
                    'Le code de la commune est obligatoire.',

                'code.max' =>
                    'Le code ne doit pas dépasser 20 caractères.',

                'libelle.required' =>
                    'Le libellé de la commune est obligatoire.',

                'libelle.max' =>
                    'Le libellé ne doit pas dépasser 100 caractères.',

                'region_id.required' =>
                    'La région est obligatoire.',

                'departement_id.required' =>
                    'Le département est obligatoire.',
            ]
        );

        $result = $this->communeService->create(
            $validated
        );

        return $this->redirectAfterSave(
            $result,
            'Commune créée avec succès.'
        );
    }

    /**
     * Modifier une commune.
     */
    public function update(
        Request $request,
        int $commune
    ): RedirectResponse {
        $validated = $request->validate(
            [
                'code' => [
                    'required',
                    'string',
                    'max:20',
                ],

                'libelle' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'region_id' => [
                    'required',
                    'integer',
                ],

                'departement_id' => [
                    'required',
                    'integer',
                ],
            ],
            [
                'code.required' =>
                    'Le code de la commune est obligatoire.',

                'code.max' =>
                    'Le code ne doit pas dépasser 20 caractères.',

                'libelle.required' =>
                    'Le libellé de la commune est obligatoire.',

                'libelle.max' =>
                    'Le libellé ne doit pas dépasser 100 caractères.',

                'region_id.required' =>
                    'La région est obligatoire.',

                'departement_id.required' =>
                    'Le département est obligatoire.',
            ]
        );

        $result = $this->communeService->update(
            $commune,
            $validated
        );

        return $this->redirectAfterSave(
            $result,
            'Commune modifiée avec succès.'
        );
    }

    /**
     * Activer / désactiver une commune.
     */
    public function changeStatus(
        Request $request,
        int $commune
    ): RedirectResponse {
        $validated = $request->validate([
            'est_actif' => [
                'required',
                'boolean',
            ],
        ]);

        $result = $this->communeService->changeStatus(
            $commune,
            (bool) $validated['est_actif']
        );

        if ($result['success'] ?? false) {
            return redirect()
                ->route('parametres.communes.index')
                ->with(
                    'success',
                    $result['message']
                        ?? 'Statut de la commune modifié avec succès.'
                );
        }

        return redirect()
            ->route('parametres.communes.index')
            ->with(
                'error',
                $result['message']
                    ?? 'Impossible de modifier le statut de la commune.'
            );
    }

    /**
     * Supprimer une commune.
     */
    public function destroy(
        int $commune
    ): RedirectResponse {
        $result = $this->communeService->delete(
            $commune
        );

        if ($result['success'] ?? false) {
            return redirect()
                ->route('parametres.communes.index')
                ->with(
                    'success',
                    $result['message']
                        ?? 'Commune supprimée avec succès.'
                );
        }

        return redirect()
            ->route('parametres.communes.index')
            ->with(
                'error',
                $result['message']
                    ?? 'Impossible de supprimer la commune.'
            );
    }

    /**
     * Gérer le retour après création / modification.
     */
    private function redirectAfterSave(
        array $result,
        string $defaultSuccessMessage
    ): RedirectResponse {
        if ($result['success'] ?? false) {
            return redirect()
                ->route('parametres.communes.index')
                ->with(
                    'success',
                    $result['message']
                        ?? $defaultSuccessMessage
                );
        }

        $redirect = redirect()
            ->route('parametres.communes.index')
            ->withInput()
            ->with(
                'error',
                $result['message']
                    ?? 'Enregistrement impossible.'
            );

        if (! empty($result['errors'])) {
            $redirect->withErrors(
                $result['errors']
            );
        }

        return $redirect;
    }
}