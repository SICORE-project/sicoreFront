<?php

namespace App\Services\Api\Indemnites;

use App\Services\Api\ApiClient;

class PieceJustificativeService
{
    public function __construct(
        protected ApiClient $api
    ) {}

    /**
     * Enveloppe uniforme autour des reponses Http::response() de l'ApiClient
     * — meme principe que ConvocationService::wrap().
     */
    protected function wrap($response): array
    {
        if (! $response->successful()) {
            return [
                'success' => false,
                'status' => $response->status(),
                'message' => $response->json('message') ?? 'Le service SICORE Backend est injoignable.',
                'errors' => $response->json('errors'),
                'data' => null,
            ];
        }

        return [
            'success' => true,
            'status' => $response->status(),
            'message' => $response->json('message'),
            'data' => $response->json('data'),
        ];
    }

    public function liste(array $filtres = []): array
    {
        return $this->wrap($this->api->get('pieces-justificatives', $filtres));
    }

    public function trouver(int|string $id): array
    {
        return $this->wrap($this->api->get("pieces-justificatives/{$id}"));
    }

    /**
     * Depot groupe des pieces d'un membre (modale "Ajouter une pièce" —
     * voir PiecesJustificativesController::deposerPieces()). $fichiers suit
     * la meme convention que ConvocationService::importer() : un tableau de
     * ['name' => ..., 'contents' => resource, 'filename' => ...].
     */
    public function deposerLot(array $donnees, array $fichiers): array
    {
        return $this->wrap($this->api->postMultipart('pieces-justificatives/deposer-lot', $donnees, $fichiers));
    }

    /**
     * Reponse HTTP BRUTE (pas wrap() : le corps n'est pas du JSON mais le
     * contenu binaire du document) — le contrôleur relaie ce corps et les
     * en-têtes reçus tels quels au navigateur, meme principe que
     * ConvocationService::telechargerPdf().
     */
    public function telecharger(int|string $id): \Illuminate\Http\Client\Response
    {
        return $this->api->get("pieces-justificatives/{$id}/download");
    }
}
