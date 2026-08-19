<?php

return [
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
