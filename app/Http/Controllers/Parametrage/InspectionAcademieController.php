<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\InspectionAcademieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InspectionAcademieController extends Controller
{
    public function create(InspectionAcademieService $service): View
    {
        return view('pages.parametres.ia-create', ['regions' => $service->regions()]);
    }

    public function index(Request $request, InspectionAcademieService $service): View|RedirectResponse
    {
        $result = $service->getAll(max(1, $request->integer('page', 1)), 10, [
            'search' => $request->string('search')->trim()->toString(),
            'region_id' => $request->integer('region_id') ?: null,
        ]);
        $result['regions'] = $service->regions();

        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', $result['error']);
        }

        $result['usingDemoData'] = false;
        if ($result['error'] && empty($result['items'])) {
            $result['items'] = [
                ['id' => 1, 'code' => 'IA-DKR', 'libelle' => 'Inspection d’académie de Dakar', 'region' => ['nom' => 'Dakar']],
                ['id' => 2, 'code' => 'IA-THS', 'libelle' => 'Inspection d’académie de Thiès', 'region' => ['nom' => 'Thiès']],
                ['id' => 3, 'code' => 'IA-SLG', 'libelle' => 'Inspection d’académie de Saint-Louis', 'region' => ['nom' => 'Saint-Louis']],
            ];
            $result['pagination'] = ['current_page' => 1, 'last_page' => 1, 'total' => count($result['items']), 'per_page' => 10];
            $result['usingDemoData'] = true;
        }

        $items = collect($result['items']);
        $result['regionCount'] = $items->map(fn (array $ia) => data_get($ia, 'region.libelle', data_get($ia, 'region.nom', data_get($ia, 'region'))))
            ->filter()->unique()->count();

        return view('pages.parametres.ia-index', $result);
    }

    public function store(Request $request, InspectionAcademieService $service): RedirectResponse
    {
        $result = $service->create($this->validated($request));

        return $this->redirectAfterSave($result);
    }

    public function update(Request $request, int $ia, InspectionAcademieService $service): RedirectResponse
    {
        $result = $service->update($ia, $this->validated($request));

        return $this->redirectAfterSave($result);
    }

    public function destroy(int $ia, InspectionAcademieService $service): RedirectResponse
    {
        return $this->redirectAfterSave($service->delete($ia));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'libelle' => ['required', 'string', 'max:200'],
            'region_id' => ['required', 'integer'],
        ]);
    }

    private function redirectAfterSave(array $result): RedirectResponse
    {
        $redirect = redirect()->route('parametres.ia.index');

        if ($result['success']) {
            return $redirect->with('success', $result['message']);
        }

        return $redirect->withInput()->withErrors($result['errors'])->with('error', $result['message']);
    }
}
