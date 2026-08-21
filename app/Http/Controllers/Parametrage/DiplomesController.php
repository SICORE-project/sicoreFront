<?php

namespace App\Http\Controllers\Parametrage;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class DiplomesController extends Controller
{
    public function index(Request $request): View
    {
        $params = array_filter([
            'search' => $request->string('search')->trim()->value() ?: null,
            'type' => $request->string('type')->trim()->value() ?: null,
            'page' => $request->integer('page', 1),
            'per_page' => 15,
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

        return view('pages.parametres.diplomes', compact('diplomes', 'meta', 'error'));
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
        return $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'libelle' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'in:academique,professionnel'],
            'date_obteention' => ['required', 'date'],
        ]);
    }
}
