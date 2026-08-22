<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class EnseignantDisciplineService
{
    public function __construct(private readonly ApiClient $apiClient) {}

    public function getTeacher(string|int $id): array
    {
        try {
            $response = $this->apiClient->get("enseignants/{$id}");
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'data' => null];
        }

        return [
            'success' => $response->successful(),
            'message' => $response->json('message', 'Impossible de charger le dossier de l’enseignant.'),
            'data' => $response->json('data'),
        ];
    }

    public function associate(string|int $teacherId, array $data): array
    {
        try {
            $response = $this->apiClient->post("enseignants/{$teacherId}/disciplines", $data);
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Le service backend est momentanément inaccessible.', 'errors' => [], 'data' => null, 'audit' => null];
        }

        return [
            'success' => $response->successful(),
            'message' => $response->json('message', $response->successful() ? 'Discipline associée à l’enseignant.' : 'Impossible d’associer la discipline.'),
            'errors' => (array) $response->json('errors', []),
            'data' => $response->json('data'),
            'audit' => $response->json('audit'),
        ];
    }
}
