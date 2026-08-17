<?php

/*
| PARAMÈTRES DE COMMUNICATION AVEC LE BACKEND
| Utilisés par app/Services/SicoreApi.php. Les valeurs propres à chaque serveur
| doivent être définies dans .env, qui n'est jamais versionné.
*/
return [
    'api' => [
        // Adresse de base du projet sicoreBack.
        'url' => env('SICORE_API_URL', 'http://127.0.0.1:8000'),
        // Délais maximal de requête et de connexion initiale.
        'timeout' => (int) env('SICORE_API_TIMEOUT', 10),
        'connect_timeout' => (int) env('SICORE_API_CONNECT_TIMEOUT', 3),
        // Durée de la session frontend associée au jeton API.
        'token_lifetime' => (int) env('SICORE_TOKEN_LIFETIME', 120),
    ],
    // Compte de recette initial uniquement ; à remplacer en production.
    'bootstrap' => [
        'email' => env('SICORE_BOOTSTRAP_EMAIL', 'admin@sicore.sn'),
        'password' => env('SICORE_BOOTSTRAP_PASSWORD', 'Sicore@2026'),
    ],
];
