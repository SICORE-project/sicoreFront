<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
<<<<<<< HEAD
use App\Services\Api\ApiClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // singleton() garantit qu'une seule instance d'ApiClient
        // est créée pour toute la durée de vie de la requête
        $this->app->singleton(ApiClient::class, function () {
             return new ApiClient();
     });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
=======

/**
 * Point d'extension général du frontend Laravel.
 *
 * Ce provider est chargé depuis bootstrap/providers.php. Aucune liaison
 * personnalisée n'est nécessaire actuellement, car Laravel sait construire
 * automatiquement les services et contrôleurs du projet.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services dans le conteneur d'injection de dépendances.
     */
    public function register(): void
    {
        // Réservé aux futures liaisons d'interfaces ou services partagés.
    }

    /**
     * Exécute les initialisations après le chargement des services.
     */
    public function boot(): void
    {
        // Aucune initialisation globale supplémentaire pour le moment.
>>>>>>> origin/module-paie
    }
}
