<?php

use App\Providers\AppServiceProvider;
use App\Providers\ApiClientServiceProvider;

/*
 * Liste des providers applicatifs chargés au démarrage du frontend.
 * AppServiceProvider.php est le point prévu pour les futures initialisations.
 */
return [
    AppServiceProvider::class,
    // Fournit l'implémentation du contrat utilisé pour joindre sicoreBack.
    ApiClientServiceProvider::class,
];
