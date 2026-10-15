<?php

namespace App\Services\Api\Indemnites;

use App\Services\Api\ApiClient;

class EtatPaieIndemnitesService
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

    public function creer(array $donnees): array
    {
        return $this->wrap($this->api->post('etat-paie-indemnites', $donnees));
    }

    public function liste(array $filtres = []): array
    {
        return $this->wrap($this->api->get('etat-paie-indemnites', $filtres));
    }

    /** Changement de statut d'un état : 'valider', 'transmettre' ou 'archiver'. */
    public function changerStatut(int|string $id, string $action): array
    {
        return $this->wrap($this->api->post("etat-paie-indemnites/{$id}/{$action}"));
    }

    public function supprimer(int|string $id): array
    {
        return $this->wrap($this->api->delete("etat-paie-indemnites/{$id}"));
    }

    /** PDF d'un état enregistré (réponse HTTP brute, comme genererFichePdf()). */
    public function pdf(int|string $id)
    {
        return $this->api->get("etat-paie-indemnites/{$id}/pdf");
    }

    /**
     * Réponse HTTP brute (fichier PDF) — pas de wrap(), même principe que
     * FraisDeplacementService::telechargerPdf().
     */
    public function genererFichePdf(array $donnees)
    {
        return $this->api->post('etat-paie-indemnites/fiche-pdf', $donnees);
    }
}
