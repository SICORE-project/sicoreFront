<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\CategorieService;
use App\Services\Parametrage\CorpsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategorieController extends Controller
{
    public function index(Request $request, CategorieService $service, CorpsService $corpsService): View|RedirectResponse
    {
        $result = $service->getAll(['page' => max(1, $request->integer('page', 1)), 'per_page' => 10, 'search' => $request->string('search')->trim()->toString()]);
        if ($result['unauthorized']) { $request->session()->forget(['access_token', 'sicore_user']); return redirect()->route('login')->with('warning', $result['error']); }
        $result['corpsOptions'] = $corpsService->getAll(['per_page' => 100])['items'];
        $contractuel = collect($result['corpsOptions'])->first(fn ($corps) =>
            strtolower(trim((string) data_get($corps, 'code'))) === 'contractuel'
            || strtolower(trim((string) data_get($corps, 'libelle'))) === 'contractuel'
        );
        $result['defaultCorpsId'] = data_get($contractuel, 'id', '');
        return view('pages.parametres.categories', $result);
    }

    public function store(Request $request, CategorieService $service): RedirectResponse { return $this->redirect($service->create($this->validated($request))); }
    public function update(Request $request, int $category, CategorieService $service): RedirectResponse { return $this->redirect($service->update($category, $this->validated($request))); }
    public function destroy(int $category, CategorieService $service): RedirectResponse { return $this->redirect($service->delete($category)); }

    private function validated(Request $request): array
    {
        return $request->validate(['libelle' => ['required','string','max:255'], 'corps_id' => ['required','integer']]);
    }

    private function redirect(array $result): RedirectResponse
    {
        $redirect = redirect()->route('parametres.categories.index');
        return $result['success'] ? $redirect->with('success', $result['message']) : $redirect->withInput()->withErrors($result['errors'])->with('error', $result['message']);
    }
}
