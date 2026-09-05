<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use App\Services\Parametrage\CategorieService;
use App\Services\Parametrage\DiplomeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class DiplomesController extends Controller
{
    public function index(Request $request, CategorieService $categories, DiplomeService $diplomeService): View
    {
        $params = array_filter([
            'salaire_min' => $request->filled('salaire_min') ? $request->input('salaire_min') : null,
            'salaire_max' => $request->filled('salaire_max') ? $request->input('salaire_max') : null,
            'page' => $request->integer('page', 1),
            'per_page' => in_array($request->integer('per_page', 10), [10, 25, 50, 100], true) ? $request->integer('per_page', 10) : 10,
            'libelle' => $request->string('libelle')->trim()->value() ?: null,
            'categorie_id' => $request->integer('categorie_id') ?: null,
        ], static fn ($value) => $value !== null);

        $diplomes = [];
        $meta = null;
        $error = null;

        try {
            $response = Http::acceptJson()
                ->withToken(session('access_token'))
                ->timeout(10)
                ->get(config('services.backend.url').'/diplomes', $params);

            if ($response->successful()) {
                $diplomes = $response->json('data', []);
                $meta = $response->json('meta');
            } else {
                $error = $response->json('message') ?? 'Impossible de récupérer les diplômes.';
            }
        } catch (\Throwable) {
            $error = 'Le service des diplômes est indisponible pour le moment.';
        }

        $diplomaOptions = $diplomeService->options();
        $categoryOptions = $categories->getAll(['per_page' => 100])['items'];

        return view('pages.parametres.diplomes', compact('diplomes', 'meta', 'error', 'categoryOptions', 'diplomaOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        try {
            $response = Http::acceptJson()
                ->withToken(session('access_token'))
                ->timeout(10)
                ->post(config('services.backend.url').'/diplomes', $data);

            if ($response->successful()) {
                return redirect()->route('parametres.diplomes.index')
                    ->with('success', 'Diplôme ajouté avec succès.');
            }

            return back()->withInput()->withErrors(
                $response->json('errors') ?? ['diplome' => $response->json('message') ?? 'Impossible d’ajouter le diplôme.']
            );
        } catch (\Throwable) {
            return back()->withInput()->withErrors([
                'diplome' => 'Le service des diplômes est indisponible pour le moment.',
            ]);
        }
    }

    public function update(Request $request, int $diplome): RedirectResponse
    {
        $data = $this->validatedData($request);

        try {
            $response = Http::acceptJson()
                ->withToken(session('access_token'))
                ->timeout(10)
                ->put(config('services.backend.url').'/diplomes/'.$diplome, $data);

            if ($response->successful()) {
                return redirect()->route('parametres.diplomes.index')
                    ->with('success', 'Diplôme modifié avec succès.');
            }

            return back()->withInput()->withErrors(
                $response->json('errors') ?? ['diplome' => $response->json('message') ?? 'Impossible de modifier le diplôme.']
            );
        } catch (\Throwable) {
            return back()->withInput()->withErrors([
                'diplome' => 'Le service des diplômes est indisponible pour le moment.',
            ]);
        }
    }

    public function destroy(int $diplome): RedirectResponse
    {
        try {
            $response = Http::acceptJson()
                ->withToken(session('access_token'))
                ->timeout(10)
                ->delete(config('services.backend.url').'/diplomes/'.$diplome);

            if ($response->successful()) {
                return redirect()->route('parametres.diplomes.index')
                    ->with('success', 'Diplôme supprimé avec succès.');
            }

            return back()->with('error', $response->json('message') ?? 'Impossible de supprimer le diplôme.');
        } catch (\Throwable) {
            return back()->with('error', 'Le service des diplômes est indisponible pour le moment.');
        }
    }

    private function validatedData(Request $request): array
    {
        $request->merge(['libelle' => mb_strtoupper(trim((string) $request->input('libelle')), 'UTF-8')]);
        return $request->validate([
            'libelle' => ['required', 'string', 'max:100'],
            'categorie_id' => ['required', 'integer', 'min:1'],
            'salaire_brut' => ['required', 'numeric', 'min:0'],
        ]);
    }
}
