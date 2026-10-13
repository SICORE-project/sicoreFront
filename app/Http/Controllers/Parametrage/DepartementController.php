<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\DepartementService;
use App\Services\Parametrage\RegionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartementController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | LISTE
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request,
        DepartementService $departementService,
        RegionService $regionService
    ): View|RedirectResponse {

        $result = $departementService->getAll(
            max(1, $request->integer('page', 1)),
            10,
            [
                'search' => $request->string('search')
                    ->trim()
                    ->toString(),

                'region_id' => $request->input('region_id'),

                'est_actif' => $request->has('est_actif')
                    && $request->input('est_actif') !== ''
                        ? $request->input('est_actif')
                        : null,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | SESSION BACKEND EXPIRÉE
        |--------------------------------------------------------------------------
        */

        if ($result['unauthorized']) {

            $request->session()->forget([
                'access_token',
                'sicore_user',
            ]);

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with(
                    'warning',
                    $result['error']
                );
        }

        /*
        |--------------------------------------------------------------------------
        | RÉGIONS ACTIVES
        |--------------------------------------------------------------------------
        */

        $regionsResult = $regionService->getAll(
            1,
            100,
            [
                'est_actif' => true,
                'sort_by' => 'libelle',
                'sort_direction' => 'asc',
            ]
        );

        $regions = $regionsResult['items'] ?? [];

        return view(
            'pages.parametres.departements',
            [
                'items' => $result['items'],
                'pagination' => $result['pagination'],
                'error' => $result['error'],
                'regions' => $regions,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CRÉATION
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        DepartementService $service
    ): RedirectResponse {

        $data = $this->validated($request);

        $result = $service->create($data);

        return $this->redirectAfterSave($result);
    }


    /*
    |--------------------------------------------------------------------------
    | MODIFICATION
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        int $departement,
        DepartementService $service
    ): RedirectResponse {

        $data = $this->validated($request);

        $result = $service->update(
            $departement,
            $data
        );

        return $this->redirectAfterSave($result);
    }


    /*
    |--------------------------------------------------------------------------
    | CHANGEMENT DE STATUT
    |--------------------------------------------------------------------------
    */

    public function changeStatus(
        Request $request,
        int $departement,
        DepartementService $service
    ): RedirectResponse {

        $data = $request->validate([
            'est_actif' => [
                'required',
                'boolean',
            ],
        ]);

        $result = $service->changeStatus(
            $departement,
            (bool) $data['est_actif']
        );

        return $this->redirectAfterSave($result);
    }


    /*
    |--------------------------------------------------------------------------
    | SUPPRESSION
    |--------------------------------------------------------------------------
    */

    public function destroy(
        int $departement,
        DepartementService $service
    ): RedirectResponse {

        $result = $service->delete(
            $departement
        );

        return $this->redirectAfterSave($result);
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATION FRONTEND
    |--------------------------------------------------------------------------
    */

    private function validated(
        Request $request
    ): array {

        return $request->validate([
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
        ], [
            'code.required' =>
                'Le code du département est obligatoire.',

            'code.max' =>
                'Le code ne doit pas dépasser 20 caractères.',

            'libelle.required' =>
                'Le libellé du département est obligatoire.',

            'libelle.max' =>
                'Le libellé ne doit pas dépasser 100 caractères.',

            'region_id.required' =>
                'La région est obligatoire.',

            'region_id.integer' =>
                'La région sélectionnée est invalide.',
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | REDIRECTION APRÈS OPÉRATION
    |--------------------------------------------------------------------------
    */

    private function redirectAfterSave(
        array $result
    ): RedirectResponse {

        $redirect = redirect()
            ->route('parametres.departements.index');

        if ($result['success']) {

            return $redirect->with(
                'success',
                $result['message']
            );
        }

        return $redirect
            ->withInput()
            ->withErrors(
                $result['errors'] ?? []
            )
            ->with(
                'error',
                $result['message']
            );
    }
}