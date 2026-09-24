<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\RegionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegionController extends Controller
{
    public function index(Request $request, RegionService $service): View|RedirectResponse
    {
        $result = $service->getAll(
            max(1, $request->integer('page', 1)),
            10,
            [
                'search' => $request->string('search')->trim()->toString(),
                'est_actif' => $request->has('est_actif')
                    && $request->input('est_actif') !== ''
                        ? $request->input('est_actif')
                        : null,
            ]
        );

        if ($result['unauthorized']) {
            $request->session()->forget([
                'access_token',
                'sicore_user',
            ]);

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('warning', $result['error']);
        }

        return view('pages.parametres.regions', $result);
    }

    public function store(Request $request, RegionService $service): RedirectResponse
    {
        $data = $this->validated($request);

        $result = $service->create($data);

        return $this->redirectAfterSave($result);
    }

    public function update(
        Request $request,
        int $region,
        RegionService $service
    ): RedirectResponse {
        $data = $this->validated($request);

        $result = $service->update($region, $data);

        return $this->redirectAfterSave($result);
    }

    public function changeStatus(
        Request $request,
        int $region,
        RegionService $service
    ): RedirectResponse {
        $data = $request->validate([
            'est_actif' => [
                'required',
                'boolean',
            ],
        ]);

        $result = $service->changeStatus(
            $region,
            (bool) $data['est_actif']
        );

        return $this->redirectAfterSave($result);
    }

    public function destroy(
        int $region,
        RegionService $service
    ): RedirectResponse {
        $result = $service->delete($region);

        return $this->redirectAfterSave($result);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'code' => [
                'required',
                'string',
                'max:10',
            ],

            'libelle' => [
                'required',
                'string',
                'max:50',
            ],

            'chef_lieu' => [
                'nullable',
                'string',
                'max:50',
            ],

            'superficie' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'population' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);
    }

    private function redirectAfterSave(array $result): RedirectResponse
    {
        $redirect = redirect()
            ->route('parametres.regions.index');

        if ($result['success']) {
            return $redirect->with(
                'success',
                $result['message']
            );
        }

        return $redirect
            ->withInput()
            ->withErrors($result['errors'] ?? [])
            ->with(
                'error',
                $result['message']
            );
    }
}