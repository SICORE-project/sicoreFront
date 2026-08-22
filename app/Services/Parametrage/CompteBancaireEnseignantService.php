<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class CompteBancaireEnseignantService
{
    public function __construct(protected ApiClient $apiClient)
    {
    }

    public function getTeachers(): array
    {
        try {
            $response = $this->apiClient->get('enseignants', ['per_page' => 100]);
        } catch (ConnectionException) {
            return [];
        }

        if (! $response->successful()) {
            return [];
        }

        $payload = $response->json();
        $items = data_get($payload, 'data.data', data_get($payload, 'data', data_get($payload, 'enseignants', [])));

        return is_array($items) ? $items : [];
    }

    public function create(int|string $teacherId, array $data): array
    {
        try {
            $response = $this->apiClient->post("enseignants/{$teacherId}/comptes-bancaires", $data);
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => []];
        }

        if ($response->successful()) {
            return [
                'success' => true,
                'message' => $response->json('message', 'Compte bancaire associé à l’enseignant avec succès.'),
                'data' => $response->json('data'),
            ];
        }

        return [
            'success' => false,
            'unauthorized' => $response->unauthorized(),
            'message' => $response->json('message', 'Impossible d’associer ce compte bancaire à l’enseignant.'),
            'errors' => $response->json('errors', []),
        ];
    }
}