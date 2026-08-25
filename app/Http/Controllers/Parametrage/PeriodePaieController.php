<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\PeriodePaieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeriodePaieController extends Controller
{
    public function index(Request $request, PeriodePaieService $service): View|RedirectResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $result = $service->paginate($filters);

        if ($result['unauthorized']) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', $result['error']);
        }

        return view('pages.parametres.periodes-paie', $result + [
            'filters' => $filters,
            'canManage' => in_array($request->session()->get('sicore_user.role_slug'), ['admin', 'super_admin'], true),
        ]);
    }

    public function store(Request $request, PeriodePaieService $service): RedirectResponse
    {
        $result = $service->create($request->validate($this->rules()));

        if ($result['success']) {
            return redirect()->route('parametres.periodes-paie.index')->with('success', $result['message']);
        }

        return back()->withInput()->with('periode_create_form_open', true)
            ->withErrors($result['errors'] ?: ['api' => $result['message']]);
    }

    public function update(Request $request, string $periode, PeriodePaieService $service): RedirectResponse
    {
        $result = $service->update($periode, $request->validateWithBag('updatePeriode', $this->rules()));

        if ($result['success']) {
            return redirect()->route('parametres.periodes-paie.index')->with('success', $result['message']);
        }

        return back()->withInput()->with('periode_update_form_open', true)
            ->with('periode_update_id', $periode)
            ->withErrors($result['errors'] ?: ['api' => $result['message']], 'updatePeriode');
    }

    public function destroy(string $periode, PeriodePaieService $service): RedirectResponse
    {
        $result = $service->delete($periode);

        return redirect()->route('parametres.periodes-paie.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    private function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9]+(?:[-_][A-Z0-9]+)*$/'],
            'libelle' => ['required', 'string', 'max:100'],
        ];
    }
}
