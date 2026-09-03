<?php

return [
    'api' => [
        'url' => env('API_BASE_URL', 'http://127.0.0.1:8000/api'),
        'timeout' => env('API_TIMEOUT', 10),
        'connect_timeout' => env('API_CONNECT_TIMEOUT', 3),
    ],

    /*
     * Authentification locale temporaire du frontend.
     * Elle permet de tester toutes les interfaces Laravel sans base de données
     * et sans backend. L'authentification API sera ajoutée dans une étape future.
     */
    'test' => [
        'email' => env('SICORE_TEST_EMAIL', 'admin@sicore.sn'),
        'password' => env('SICORE_TEST_PASSWORD', 'Sicore@2026'),
    ],
];
