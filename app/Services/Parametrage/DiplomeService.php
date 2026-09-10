<?php

namespace App\Services\Parametrage;

use App\Services\Api\ApiClient;
use Illuminate\Http\Client\ConnectionException;

class DiplomeService
{
    public function __construct(private readonly ApiClient $apiClient) {}

    public function options(): array
    {
        $options = [];
        $page = 1;
        do {
            try {
                $response = $this->apiClient->get('diplomes', ['per_page' => 100, 'page' => $page]);
            } catch (ConnectionException) {
                return [];
            }

            if (! $response->successful()) {
                return [];
            }

            $items = $response->json('data.data', $response->json('data', []));
            if (! is_array($items)) {
                return [];
            }
            $options = array_merge($options, $items);
            $lastPage = (int) $response->json('meta.last_page', $response->json('data.last_page', 1));
            $page++;
        } while ($page <= $lastPage);

        return $options;
    }
}
