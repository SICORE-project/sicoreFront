<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module Administration
|--------------------------------------------------------------------------
*/

Route::middleware('sicore.auth')->group(function (): void {
    Route::view('/enseignants', 'pages.enseignants.index')->name('enseignants.index');
    Route::view('/enseignants/nouveau', 'pages.enseignants.create')->name('enseignants.create');

    Route::view('/utilisateurs', 'pages.administration.utilisateurs')->name('utilisateurs.index');
    Route::view('/utilisateurs/profils-roles', 'pages.administration.profils-roles')->name('utilisateurs.profils-roles');
    Route::view('/utilisateurs/permissions', 'pages.administration.permissions')->name('utilisateurs.permissions');
});
