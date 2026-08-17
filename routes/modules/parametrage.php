<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module Paramétrage
|--------------------------------------------------------------------------
*/

Route::middleware('sicore.auth')
    ->prefix('parametres')
    ->name('parametres.')
    ->group(function (): void {
        Route::view('/', 'pages.parametres.index')->name('index');
        Route::view('/ief', 'pages.parametres.ief')->name('ief');
    });
