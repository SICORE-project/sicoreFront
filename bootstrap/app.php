<?php

use App\Http\Middleware\EnsureSicoreAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
| DÉMARRAGE DU FRONTEND LARAVEL
| Ce fichier relie le framework aux routes, commandes et middlewares.
| La logique métier appartient aux contrôleurs et services du dossier app/.
*/
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // URL visibles dans le navigateur.
        web: __DIR__.'/../routes/web.php',
        // Commandes artisan propres au frontend.
        commands: __DIR__.'/../routes/console.php',
        // Endpoint technique confirmant que le frontend répond.
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Le nom court sicore.auth est ensuite utilisé dans routes/web.php.
        $middleware->alias([
            'sicore.auth' => EnsureSicoreAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Réservé à une future personnalisation globale des erreurs.
    })->create();
