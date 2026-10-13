<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\StatutEnseignantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatutEnseignantController extends Controller
{
    public function index(Request $request, StatutEnseignantService $service): View|RedirectResponse
    {
        $result = $service->getAll(['page' => max(1, $request->integer('page', 1)), 'per_page' => 10, 'search' => $request->string('search')->trim()->toString()]);
        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            return redirect()->route('login')->with('warning', $result['error']);
        }
        return view('pages.parametres.statuts-enseignant', $result);
    }

    public function store(Request $request, StatutEnseignantService $service): RedirectResponse { return $this->redirect($service->create($this->validated($request))); }
    public function update(Request $request, int $corps, StatutEnseignantService $service): RedirectResponse { return $this->redirect($service->update($corps, $this->validated($request))); }
    public function destroy(int $corps, StatutEnseignantService $service): RedirectResponse { return $this->redirect($service->delete($corps)); }

    private function validated(Request $request): array
    {
        return $request->validate(['libelle' => ['required', 'string', 'max:50']]);
    }

    private function redirect(array $result): RedirectResponse
    {
        $redirect = redirect()->route('parametres.statuts-enseignant.index');
        return $result['success'] ? $redirect->with('success', $result['message']) : $redirect->withInput()->withErrors($result['errors'])->with('error', $result['message']);
    }
}
