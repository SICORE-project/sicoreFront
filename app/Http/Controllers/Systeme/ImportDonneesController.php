<?php

namespace App\Http\Controllers\Systeme;

use App\Http\Controllers\Controller;
use App\Services\Api\ImportDonneesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Import des données de base (profil Admin System). */
class ImportDonneesController extends Controller
{
    public function __construct(private readonly ImportDonneesService $import) {}

    public function index(Request $request): View
    {
        return view('pages.systeme.import-donnees', [
            'referentiels' => $this->import->referentiels(),
            'entite' => $request->query('entite'),
        ]);
    }

    public function traiter(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'entite' => ['required', 'string'],
            'mode' => ['required', 'in:apercu,importer'],
            'fichier' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], [
            'fichier.required' => 'Choisissez un fichier CSV.',
            'fichier.mimes' => 'Le fichier doit être au format CSV.',
            'fichier.max' => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        $resultat = $this->import->traiter($data['entite'], $request->file('fichier'), $data['mode']);

        return redirect()
            ->route('systeme.import', ['entite' => $data['entite']])
            ->with($resultat['success'] ? 'success' : 'error', $resultat['message'])
            ->with('rapport_import', $resultat['rapport'] ? $resultat['rapport'] + ['mode' => $data['mode']] : null);
    }

    public function modele(string $entite): StreamedResponse
    {
        $reponse = $this->import->modele($entite);

        abort_unless($reponse->successful(), 404, 'Modèle introuvable.');

        return response()->streamDownload(function () use ($reponse) {
            echo $reponse->body();
        }, "modele-{$entite}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
