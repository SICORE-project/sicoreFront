<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function (): void {

    Route::get('/', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login.submit');

});


Route::middleware('sicore.auth')->group(function (): void {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    Route::view('/dashboard', 'pages.dashboard.index')
        ->name('dashboard');

});


require __DIR__.'/modules/administration.php';
require __DIR__.'/modules/parametrage.php';
require __DIR__.'/modules/paie.php';
require __DIR__.'/modules/indemnites.php';