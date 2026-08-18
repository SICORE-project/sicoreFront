<?php

namespace App\Services\Api\Indemnites;

use App\Services\Api\ApiClient;
use Illuminate\Http\UploadedFile;

class FraisDeplacementService
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
        return $this->wrap($this->api->get('frais-deplacement', $filtres));
    }

    /**
     * Bénéficiaires (dossier complet / incomplet) d'UNE convocation — voir
     * FraisDeplacementController::beneficiairesEligibles() côté back.
     */
    public function beneficiairesEligibles(int|string $convocationId): array
    {
        return $this->wrap($this->api->get('frais-deplacement/beneficiaires-eligibles', [
            'convocation_id' => $convocationId,
        ]));
    }

    public function trouver(int|string $id): array
    {
        return $this->wrap($this->api->get("frais-deplacement/{$id}"));
    }

    /**
     * $fichier est la feuille de déplacement scannée/remplie, optionnelle
     * à la création (peut être déposée après coup via deposerJustificatif()).
     */
    public function creer(array $donnees, ?UploadedFile $fichier = null): array
    {
        if (! $fichier) {
            return $this->wrap($this->api->post('frais-deplacement', $donnees));
        }

        return $this->wrap($this->api->postMultipart('frais-deplacement', $donnees, [
            [
                'name' => 'fichier',
                'contents' => fopen($fichier->getRealPath(), 'r'),
                'filename' => $fichier->getClientOriginalName(),
            ],
        ]));
    }

    public function mettreAJour(int|string $id, array $donnees): array
    {
        return $this->wrap($this->api->put("frais-deplacement/{$id}", $donnees));
    }

    public function supprimer(int|string $id): array
    {
        return $this->wrap($this->api->delete("frais-deplacement/{$id}"));
    }

    public function justificatifs(int|string $id): array
    {
        return $this->wrap($this->api->get("frais-deplacement/{$id}/justificatifs"));
    }

    public function deposerJustificatif(int|string $id, UploadedFile $fichier, ?string $commentaire = null): array
    {
        return $this->wrap($this->api->postMultipart("frais-deplacement/{$id}/justificatifs", array_filter([
            'commentaire' => $commentaire,
        ]), [
            [
                'name' => 'fichier',
                'contents' => fopen($fichier->getRealPath(), 'r'),
                'filename' => $fichier->getClientOriginalName(),
            ],
        ]));
    }

    public function valider(int|string $id): array
    {
        return $this->wrap($this->api->post("frais-deplacement/{$id}/valider"));
    }

    public function rejeter(int|string $id, string $motifRejet): array
    {
        return $this->wrap($this->api->post("frais-deplacement/{$id}/rejeter", ['motif_rejet' => $motifRejet]));
    }
}
