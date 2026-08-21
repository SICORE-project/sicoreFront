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
                'message' => match ($response->status()) {
                    401 => 'Connexion refusée : l’adresse e-mail ou le mot de passe est incorrect.',
                    403 => 'Votre compte est reconnu, mais il n’est pas autorisé à se connecter à SICORE. Vérifiez que le compte est actif et qu’un rôle lui est attribué, puis contactez un administrateur.',
                    429 => 'Trop de tentatives de connexion. Patientez quelques instants avant de réessayer.',
                    default => $response->json('message') ?? 'Le service d’authentification SICORE ne répond pas. Réessayez plus tard.',
                },
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
