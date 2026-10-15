<?php

use Illuminate\Support\Facades\Route;

Route::middleware('sicore.auth')
    ->prefix('personnel')
    ->name('personnel.')
    ->group(function (): void {
        $pages = [
            'reclassement' => ['Reclassement', 'fa-solid fa-ranking-star'],
            'impot-sur-le-revenu' => ['Impôt sur le revenu', 'fa-solid fa-file-invoice-dollar'],
            'validation-enseignants-transferes' => ['Validation enseignants transférés', 'fa-solid fa-user-check'],
            'heures-supplementaires' => ['Heures supplémentaires', 'fa-solid fa-clock'],
            'principaux-interimaires' => ['Principaux intérimaires', 'fa-solid fa-user-tie'],
        ];

        foreach ($pages as $path => [$title, $icon]) {
            Route::view('/'.$path, 'pages.personnel.index', compact('title', 'icon'))->name($path);
        }
    });
