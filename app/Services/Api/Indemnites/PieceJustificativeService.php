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
        return $this->wrap($this->api->postMultipart(
            'pieces-justificatives/deposer-lot',
            $donnees,
            $this->fichiersMultipart($fichiers)
        ));
    }

    /**
     * Modification du dossier d'UN membre (modale "Modifier", meme
     * formulaire complet que deposerLot() — demande utilisatrice) : les
     * types absents de $fichiers restent inchanges cote back (voir
     * PieceJustificativesController::modifierLot()), pas besoin de
     * reteleverser les 5 a chaque fois.
     */
    public function modifierLot(array $donnees, array $fichiers): array
    {
        return $this->wrap($this->api->postMultipart(
            'pieces-justificatives/modifier-lot',
            $donnees,
            $this->fichiersMultipart($fichiers)
        ));
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

    public function valider(int|string $id): array
    {
        return $this->wrap($this->api->post("pieces-justificatives/{$id}/valider"));
    }

    public function rejeter(int|string $id, string $commentaireRejet): array
    {
        return $this->wrap($this->api->post("pieces-justificatives/{$id}/rejeter", [
            'commentaire_rejet' => $commentaireRejet,
        ]));
    }

    /**
     * $fichiers est indexé par type ('service_fait', 'ordre_mission', ...) ;
     * les entrées absentes/vides (aucun fichier choisi pour ce type) sont
     * simplement ignorées ici — utilisé par deposerLot()/modifierLot().
     */
    private function fichiersMultipart(array $fichiers): array
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

        return $fichiersMultipart;
    }
}
