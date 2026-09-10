<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\RubriquePaieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RubriquePaieController extends Controller
{
    public function index(Request $request, RubriquePaieService $service): View|RedirectResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:gain,retenue'],
            'periodicite' => ['nullable', 'in:mensuelle,ponctuelle,annuelle'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $result = $service->paginate($filters);
        if ($result['unauthorized']) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', $result['error']);
        }

        $canManage = in_array($request->session()->get('sicore_user.role_slug'), ['admin', 'super_admin'], true);

        return view('pages.parametres.rubriques-paie', $result + [
            'filters' => $filters,
            'canManage' => $canManage,
        ]);
    }

    public function store(Request $request, RubriquePaieService $service): RedirectResponse
    {
        $result = $service->create($request->validate($this->rules()));

        if ($result['success']) {
            return redirect()->route('parametres.rubriques-paie.index')->with('success', $result['message']);
        }

        return back()->withInput()
            ->with('rubrique_create_form_open', true)
            ->withErrors($result['errors'] ?: ['api' => $result['message']]);
    }

    public function update(Request $request, string $rubrique, RubriquePaieService $service): RedirectResponse
    {
        $data = $request->validateWithBag('updateRubrique', $this->rules());
        $result = $service->update($rubrique, $data);

        if ($result['success']) {
            return redirect()->route('parametres.rubriques-paie.index')->with('success', $result['message']);
        }

        return back()->withInput()
            ->with('rubrique_update_form_open', true)
            ->with('rubrique_update_id', $rubrique)
            ->withErrors($result['errors'] ?: ['api' => $result['message']], 'updateRubrique');
    }

    public function destroy(string $rubrique, RubriquePaieService $service): RedirectResponse
    {
        $result = $service->delete($rubrique);

        return redirect()->route('parametres.rubriques-paie.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    private function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20'],
            'libelle' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:gain,retenue'],
            'periodicite' => ['required', 'in:mensuelle,ponctuelle,annuelle'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
