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
            $errors = $response->json('errors', []);
            $fieldErrors = array_values(array_filter($errors, 'is_array'));

            return [
                'success' => false,
                'message' => $fieldErrors[0][0]
                    ?? $response->json('message')
                    ?? 'La connexion a échoué. Veuillez vérifier vos identifiants.',
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