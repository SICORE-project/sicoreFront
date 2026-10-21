<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\IefService;
use App\Services\Parametrage\InspectionAcademieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IefController extends Controller
{
    public function index(Request $request, IefService $service, InspectionAcademieService $ias): View|RedirectResponse
    {
        $result = $service->getAll([
            'page' => max(1, $request->integer('page', 1)),
            'per_page' => 10,
            'search' => $request->string('search')->trim()->toString(),
            'ia_id' => $request->integer('ia_id') ?: null,
            'sort_by' => 'created_at',
            'sort_direction' => 'desc',
        ]);

        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);

            return redirect()->route('login')->with('warning', $result['error']);
        }

        $result['ias'] = $ias->getAll(1, 100)['items'];

        return view('pages.parametres.ief', $result);
    }

    public function store(Request $request, IefService $service): RedirectResponse
    {
        return $this->redirect($service->create($this->validated($request)));
    }

    public function update(Request $request, int $ief, IefService $service): RedirectResponse
    {
        return $this->redirect($service->update($ief, $this->validated($request)));
    }

    public function destroy(int $ief, IefService $service): RedirectResponse
    {
        return $this->redirect($service->delete($ief));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'libelle' => ['required', 'string', 'max:100'],
            'ia_id' => ['required', 'integer'],
        ]);
    }

    private function redirect(array $result): RedirectResponse
    {
        $redirect = redirect()->route('parametres.ief.index');

        return $result['success']
            ? $redirect->with('success', $result['message'])
            : $redirect->withInput()->withErrors($result['errors'])->with('error', $result['message']);
    }
}
