<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\GradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request, GradeService $service): View|RedirectResponse
    {
        $result = $service->getAll([
            'page' => max(1, $request->integer('page', 1)),
            'per_page' => 10,
            'search' => $request->string('search')->trim()->toString(),
        ]);
        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            return redirect()->route('login')->with('warning', $result['error']);
        }

        return view('pages.parametres.grades', $result);
    }

    public function store(Request $request, GradeService $service): RedirectResponse { return $this->redirect($service->create($this->validated($request))); }
    public function update(Request $request, int $grade, GradeService $service): RedirectResponse { return $this->redirect($service->update($grade, $this->validated($request))); }
    public function destroy(int $grade, GradeService $service): RedirectResponse { return $this->redirect($service->delete($grade)); }

    private function validated(Request $request): array
    {
        return $request->validate([
            'libelle' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function redirect(array $result): RedirectResponse
    {
        $redirect = redirect()->route('parametres.grades.index');
        return $result['success']
            ? $redirect->with('success', $result['message'])
            : $redirect->withInput()->withErrors($result['errors'])->with('error', $result['message']);
    }
}
