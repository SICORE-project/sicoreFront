<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Artisan::command('recruitment:prune-previews', function () {
    $disk = Storage::disk('local');
    $count = 0;
    foreach ($disk->files('recruitment-previews') as $path) {
        if ($disk->lastModified($path) < now()->subDay()->timestamp) {
            if ($disk->delete($path)) $count++;
        }
    }
    $this->info($count.' aperçu(s) expiré(s) supprimé(s).');
})->purpose('Supprimer les fichiers CSV temporaires abandonnés depuis plus de 24 heures');

Schedule::command('recruitment:prune-previews')->daily()->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
