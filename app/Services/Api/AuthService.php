<?php

namespace App\Services\Api;

class AuthService
{
    public function __construct(
        protected ApiClient $api
    ) {}

    public function login(array $credentials): array
    {
        $response = $this->api->post('login', $credentials);

        if (! $response->successful()) {
            return [
                'success' => false,
                'message' => $response->json('message') ?? 'Erreur de connexion.',
            ];
        }

        return [
            'success' => true,
            'data' => $response->json(),
        ];
    }

    public function logout()
    {
        return $this->api->post('logout');
    }

    public function me()
    {
        return $this->api->get('me');
    }
}