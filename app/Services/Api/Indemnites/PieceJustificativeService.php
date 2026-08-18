<?php

namespace App\Services\Api\Indemnites;

use App\Services\Api\ApiClient;
use Illuminate\Http\UploadedFile;

class PieceJustificativeService
{
    public function __construct(
        protected ApiClient $api
    ) {}

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

    /**
     * Dépôt groupé des 5 pièces manuelles d'UN membre (le 6e document,
     * "dossier_convocation", est rattaché automatiquement côté back — voir
     * PieceJustificativesController::deposerLot()). $fichiers est indexé
     * par type ('service_fait', 'ordre_mission', 'rapport_mission',
     * 'bulletin_salaire', 'accuse_reception').
     */
    public function deposerLot(array $donnees, array $fichiers): array
    {
        $fichiersMultipart = [];

        foreach ($fichiers as $type => $fichier) {
            if (! $fichier instanceof UploadedFile) {
                continue;
            }

            $fichiersMultipart[] = [
                'name' => $type,
                'contents' => fopen($fichier->getRealPath(), 'r'),
                'filename' => $fichier->getClientOriginalName(),
            ];
        }

        return $this->wrap($this->api->postMultipart('pieces-justificatives/deposer-lot', $donnees, $fichiersMultipart));
    }

    /**
     * Réponse brute (pas de wrap() : corps binaire, pas du JSON) — relayée
     * telle quelle par le contrôleur, même principe que
     * ConvocationService::telechargerPdf().
     */
    public function telecharger(int|string $id)
    {
        return $this->api->get("pieces-justificatives/{$id}/download");
    }
}
