<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
 * COMMANDES ARTISAN DU FRONTEND
 * Ce fichier permet de déclarer de petites commandes exécutées en terminal.
 * La commande "inspire" ci-dessous est l'exemple standard fourni par Laravel.
 */
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
