<?php

use App\Http\Controllers\PayrollController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module Paie et crédits
|--------------------------------------------------------------------------
*/

Route::middleware('sicore.auth')->group(function (): void {
    Route::prefix('paie')->name('paie.')->group(function (): void {
        $pages = [
            'etats-presence' => 'paie-etats-presence',
            'avance-tabaski' => 'paie-avance-tabaski',
            'retenue-tabaski' => 'paie-retenue-tabaski',
            'retenues-rappel' => 'paie-retenues-rappel',
            'exemptions' => 'paie-exemptions',
            'travaux-periodiques' => 'paie-travaux-periodiques',
            'recap-banque' => 'paie-recap-banque',
            'cotisations-sociales' => 'paie-cotisations-sociales',
            'etat-salaires' => 'paie-etat-salaires',
            'elements-saisie-dashboard' => 'paie-elements-saisie-dashboard',
            'generee-ief' => 'paie-generee-ief',
            'fermeture-periode' => 'paie-fermeture-periode',
            'edition-salaires-banque' => 'paie-edition-salaires-banque',
            'bulletins' => 'paie-bulletins',
            'effectifs-corps' => 'paie-effectifs-corps',
            'non-generee' => 'paie-non-generee',
            'sommes-percues' => 'paie-sommes-percues',
        ];

        foreach ($pages as $path => $slug) {
            Route::get('/'.$path, [PayrollController::class, 'show'])
                ->defaults('slug', $slug)
                ->name($path);
        }

        Route::post('/actions/{action}', [PayrollController::class, 'action'])
            ->whereIn('action', array_keys(config('payroll-forms', [])))
            ->middleware('throttle:30,1')
            ->name('action');

        Route::get('/export/{slug}', [PayrollController::class, 'export'])
            ->whereIn('slug', array_values($pages))
            ->middleware('throttle:20,1')
            ->name('export');

        Route::get('/bulletins/{payslip}', [PayrollController::class, 'payslip'])
            ->whereNumber('payslip')
            ->name('payslip');
    });

    Route::prefix('credits')->name('credits.')->group(function (): void {
        Route::view('/delegation', 'pages.credits.delegation')->name('delegation');
        Route::view('/edition-delegations', 'pages.credits.edition-delegations')->name('edition-delegations');
        Route::view('/edition-engagements', 'pages.credits.edition-engagements')->name('edition-engagements');
    });
});
