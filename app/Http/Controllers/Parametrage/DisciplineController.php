<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSicorePermission;
use App\Services\Parametrage\DisciplineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisciplineController extends Controller
{
    public function index(Request $request, DisciplineService $service): View|RedirectResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'statut' => ['nullable', 'in:actif,inactif'],
            'sort' => ['nullable', 'in:code,libelle,description,statut'],
            'direction' => ['nullable', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $result = $service->paginate($filters);
        if ($result['unauthorized']) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('warning', $result['error']);
        }

        return view('pages.parametres.disciplines', $result + [
            'filters' => $filters,
            'canCreate' => app(EnsureSicorePermission::class)->allows($request, 'parametrage.disciplines.creer'),
        ]);
    }

    public function store(Request $request, DisciplineService $service): RedirectResponse
    {
        $request->mergeIfMissing(['statut' => 'actif']);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9]+(?:[-_][A-Z0-9]+)*$/'],
            'libelle' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'statut' => ['required', 'in:actif,inactif'],
        ]);
        $result = $service->create($data);
        if ($result['success']) {
            return redirect()->route('parametres.disciplines.index')->with('success', $result['message']);
        }

        return back()->withInput()
            ->with('discipline_create_form_open', true)
            ->withErrors($result['errors'] ?: ['api' => $result['message']]);
    }
}
