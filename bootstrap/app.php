<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureSicoreAuthenticated;
use App\Http\Middleware\EnsureDiplomeManagementAuthorized;
use App\Http\Middleware\EnsureSicorePermission;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Reject matricule whitespace instead of silently removing it before validation.
        $middleware->trimStrings(except: ['matricule', 'indice', 'rows.*.data.matricule', 'rows.*.data.indice']);
        $middleware->alias([
            'sicore.auth' => EnsureSicoreAuthenticated::class,
            'diplomes.manage' => EnsureDiplomeManagementAuthorized::class,
            'sicore.permission' => EnsureSicorePermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
