<?php

namespace App\Services\Api;

use Illuminate\Http\UploadedFile;

/** Import des données de base (API `systeme/import`). */
class ImportDonneesService
{
    public function __construct(protected ApiClient $api) {}

    /** Référentiels importables et leurs colonnes. */
    public function referentiels(): array
    {
        $reponse = $this->api->get('systeme/import');

        return $reponse->successful() ? ($reponse->json('data') ?? []) : [];
    }

    /** Réponse brute (fichier CSV), relayée telle quelle par le contrôleur. */
    public function modele(string $entite)
    {
        return $this->api->get("systeme/import/{$entite}/modele");
    }

    /**
     * Contrôle ou importe le fichier. Le rapport (lignes en erreur comprises) est renvoyé
     * même quand l'API répond 422.
     *
     * @return array{success: bool, message: string, rapport: ?array}
     */
    public function traiter(string $entite, UploadedFile $fichier, string $mode): array
    {
        $reponse = $this->api->postMultipart("systeme/import/{$entite}", ['mode' => $mode], [[
            'name' => 'fichier',
            'contents' => fopen($fichier->getRealPath(), 'r'),
            'filename' => $fichier->getClientOriginalName(),
        ]]);

        $erreurFormulaire = collect($reponse->json('errors') ?? [])->flatten()->first();

        return [
            'success' => $reponse->successful(),
            'message' => $erreurFormulaire ?: ($reponse->json('message') ?? 'Le service SICORE Backend est injoignable.'),
            'rapport' => $reponse->json('data'),
        ];
    }
}
