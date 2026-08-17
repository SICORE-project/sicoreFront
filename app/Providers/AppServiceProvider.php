<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
    }
}
