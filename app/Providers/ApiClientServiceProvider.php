<?php

namespace App\Providers;

use App\Contracts\SicoreApiClientInterface;
use App\Services\SicoreApi;
use Illuminate\Support\ServiceProvider;

/**
 * Enregistre le client chargé de communiquer avec le backend SICORE.
 *
 * Ce provider applique la logique du TP : les contrôleurs demandent une
 * interface et le conteneur Laravel leur fournit automatiquement SicoreApi.
 * Le singleton garantit qu'une seule configuration du client est utilisée
 * pendant toute la requête HTTP du frontend.
 */
class ApiClientServiceProvider extends ServiceProvider
{
    /** Déclare les dépendances disponibles dans le conteneur Laravel. */
    public function register(): void
    {
        $this->app->singleton(
            SicoreApiClientInterface::class,
            SicoreApi::class
        );
    }
}
