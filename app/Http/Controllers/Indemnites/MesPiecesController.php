<?php

namespace App\Http\Controllers\Indemnites;

use App\Http\Controllers\Controller;
use App\Services\Api\Indemnites\PieceJustificativeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Espace de l'enseignant : dépôt et suivi de SES pièces justificatives.
 * La consultation des bulletins de paie relève d'un autre module.
 */
class MesPiecesController extends Controller
{
    public function __construct(private readonly PieceJustificativeService $pieces) {}

    public function index(): View
    {
        $resultat = $this->pieces->mesPieces();

        return view('pages.indemnites.mes-pieces', [
            'convocations' => $resultat['success'] ? ($resultat['data'] ?? []) : [],
            'erreur' => $resultat['success'] ? null : ($resultat['message'] ?? 'Impossible de charger vos pièces.'),
        ]);
    }

    public function deposer(Request $request): RedirectResponse
    {
        $request->validate([
            'convocation_id' => ['required', 'integer'],
            'type' => ['required', 'string'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:1024'],
        ], [
            'document.required' => 'Choisissez un fichier.',
            'document.max' => 'Le fichier ne doit pas dépasser 1 Mo.',
            'document.mimes' => 'Formats acceptés : PDF, JPG, PNG.',
        ]);

        $resultat = $this->pieces->deposerMaPiece(
            $request->only(['convocation_id', 'type']),
            $request->file('document')
        );

        if (! $resultat['success']) {
            $erreurs = collect($resultat['errors'] ?? [])->flatten()->first();

            return back()->with('error', $erreurs ?: ($resultat['message'] ?? 'Échec du dépôt de la pièce.'));
        }

        return back()->with('success', 'Pièce déposée avec succès.');
    }

    public function telecharger(string $id): StreamedResponse
    {
        $reponse = $this->pieces->telechargerMaPiece($id);

        abort_unless($reponse->successful(), 404, 'Pièce introuvable.');

        return response()->streamDownload(function () use ($reponse) {
            echo $reponse->body();
        }, null, array_filter([
            'Content-Type' => $reponse->header('Content-Type') ?: 'application/octet-stream',
            'Content-Disposition' => $reponse->header('Content-Disposition'),
        ]));
    }
}
