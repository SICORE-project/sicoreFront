<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSicorePermission;
use App\Services\Parametrage\DisciplineService;
use App\Services\Parametrage\EnseignantDisciplineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnseignantDisciplineController extends Controller
{
    public function show(Request $request, string $enseignant, EnseignantDisciplineService $service, DisciplineService $disciplines): View|RedirectResponse
    {
        $result = $service->getTeacher($enseignant);
        if (! $result['success'] || ! is_array($result['data'])) {
            return redirect()->route('enseignants.index')->with('error', $result['message']);
        }

        $teacher = $result['data'];
        $associated = collect(data_get($teacher, 'disciplines', []));
        $associatedIds = $associated->map(fn ($item) => (string) data_get($item, 'id', data_get($item, 'uuid')))->all();
        $available = collect($disciplines->getActiveForSelection())
            ->reject(fn ($item) => in_array((string) data_get($item, 'id', data_get($item, 'uuid')), $associatedIds, true))
            ->values()->all();

        return view('pages.enseignants.show', [
            'teacher' => $teacher,
            'associatedDisciplines' => $associated,
            'availableDisciplines' => $available,
            'canAssociateDiscipline' => app(EnsureSicorePermission::class)->allows($request, 'enseignants.disciplines.associer'),
        ]);
    }

    public function store(Request $request, string $enseignant, EnseignantDisciplineService $service): RedirectResponse
    {
        $data = $request->validateWithBag('associateDiscipline', [
            'discipline_id' => ['required', 'integer', 'min:1'],
            'est_principale' => ['nullable', 'boolean'],
        ]);
        $data['est_principale'] = $request->boolean('est_principale');
        $result = $service->associate($enseignant, $data);
        if ($result['success']) {
            return redirect()->route('enseignants.show', $enseignant)->with('success', $result['message']);
        }

        return back()->withInput()
            ->with('discipline_association_form_open', true)
            ->withErrors($result['errors'] ?: ['api' => $result['message']], 'associateDiscipline');
    }
}
