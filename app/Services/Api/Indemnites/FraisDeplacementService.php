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
     * Réponse HTTP brute (fichier PDF) — pas de wrap(), même principe que
     * ConvocationService::telechargerPdf().
     */
    public function telechargerPdf(int|string $id)
    {
        return $this->api->get("frais-deplacement/{$id}/download");
    }

    /**
     * $fichierRecto / $fichierVerso sont les 2 faces de la feuille de
     * déplacement scannée/remplie — le document papier est RECTO-VERSO,
     * chaque face est optionnelle à la création (peuvent aussi être
     * déposées après coup via deposerJustificatif()).
     */
    public function creer(array $donnees, ?UploadedFile $fichierRecto = null, ?UploadedFile $fichierVerso = null): array
    {
        $fichiers = [];

        if ($fichierRecto) {
            $fichiers[] = [
                'name' => 'fichier_recto',
                'contents' => fopen($fichierRecto->getRealPath(), 'r'),
                'filename' => $fichierRecto->getClientOriginalName(),
            ];
        }

        if ($fichierVerso) {
            $fichiers[] = [
                'name' => 'fichier_verso',
                'contents' => fopen($fichierVerso->getRealPath(), 'r'),
                'filename' => $fichierVerso->getClientOriginalName(),
            ];
        }

        if (empty($fichiers)) {
            return $this->wrap($this->api->post('frais-deplacement', $donnees));
        }

        return $this->wrap($this->api->postMultipart('frais-deplacement', $donnees, $fichiers));
    }

    public function mettreAJour(int|string $id, array $donnees): array
    {
        return $this->wrap($this->api->put("frais-deplacement/{$id}", $donnees));
    }

    /**
     * Étape "Calcul" (fonctionnaires dont la fiche reste en "brouillon" à
     * la création — voir FraisDeplacementController::store() côté back,
     * qui n'a pas de barème par indice) — $lignes est un tableau de
     * ['type_frais' => ..., 'quantite' => ..., 'taux_unitaire' => ...,
     * 'description' => ...], voir FraisDeplacementController::calculer()
     * côté back pour le calcul (Nombre x Taux, plafonné si un barème est
     * précisé) et CalculerFraisDeplacementRequest pour la validation.
     */
    public function calculer(int|string $id, array $lignes): array
    {
        return $this->wrap($this->api->post("frais-deplacement/{$id}/calculer", [
            'lignes' => $lignes,
        ]));
    }

    public function supprimer(int|string $id): array
    {
        return $this->wrap($this->api->delete("frais-deplacement/{$id}"));
    }

    public function justificatifs(int|string $id): array
    {
        return $this->wrap($this->api->get("frais-deplacement/{$id}/justificatifs"));
    }

    /**
     * Réponse HTTP brute (fichier) — pas de wrap() ici, contrairement aux
     * autres méthodes de ce service : voir PieceJustificativeService::
     * telecharger() côté "Pièces justificatives", même principe.
     */
    public function telechargerJustificatif(int|string $id, int|string $justificatifId)
    {
        return $this->api->get("frais-deplacement/{$id}/justificatifs/{$justificatifId}/download");
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

    public function supprimerJustificatif(int|string $id, int|string $justificatifId): array
    {
        return $this->wrap($this->api->delete("frais-deplacement/{$id}/justificatifs/{$justificatifId}"));
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
