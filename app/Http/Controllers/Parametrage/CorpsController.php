<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\CorpsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Controllers\Parametrage\Concerns\SupprimeEnMasse;

class CorpsController extends Controller
{
    use SupprimeEnMasse;

    public function index(Request $request, CorpsService $service): View|RedirectResponse
    {
        $result = $service->getAll(['page' => max(1, $request->integer('page', 1)), 'per_page' => 10, 'search' => $request->string('search')->trim()->toString()]);
        if ($result['unauthorized']) {
            $request->session()->forget(['access_token', 'sicore_user']);
            return redirect()->route('login')->with('warning', $result['error']);
        }
        return view('pages.parametres.corps', $result);
    }

    public function store(Request $request, CorpsService $service): RedirectResponse { return $this->redirect($service->create($this->validated($request))); }
    public function update(Request $request, int $corps, CorpsService $service): RedirectResponse { return $this->redirect($service->update($corps, $this->validated($request))); }
    public function destroy(int $corps, CorpsService $service): RedirectResponse { return $this->redirect($service->delete($corps)); }

    private function validated(Request $request): array
    {
        return $request->validate(['code' => ['required', 'string', 'max:20'], 'libelle' => ['required', 'string', 'max:100']]);
    }

    private function redirect(array $result): RedirectResponse
    {
        $redirect = redirect()->route('parametres.corps.index');
        return $result['success'] ? $redirect->with('success', $result['message']) : $redirect->withInput()->withErrors($result['errors'])->with('error', $result['message']);
    }

    public function destroyMultiple(Request $request, CorpsService $service): RedirectResponse
    {
        return $this->supprimerSelection($request, fn (int $id) => $service->delete($id), 'corps', 'parametres.corps.index', false);
    }
}
