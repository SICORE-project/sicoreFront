<?php

use App\Http\Controllers\CreditEditionController;
use App\Http\Controllers\Paie\EtatSalaire\EtatSalaireController;
use App\Http\Controllers\PayrollController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Module Paie et crédits
|--------------------------------------------------------------------------
*/

Route::middleware(['sicore.auth', 'sicore.module:paie.bulletins.read'])->group(function (): void {
    Route::prefix('paie')->name('paie.')->group(function (): void {
        $pages = [
            'edition' => 'paie-edition',
            'etats-presence' => 'paie-etats-presence',
            'avance-tabaski' => 'paie-avance-tabaski',
            'retenue-tabaski' => 'paie-retenue-tabaski',
            'retenues-rappel' => 'paie-retenues-rappel',
            'exemptions' => 'paie-exemptions',
            'travaux-periodiques' => 'paie-travaux-periodiques',
            'delegation-credits' => 'paie-delegation-credits',
'edition-delegation' => 'paie-edition-delegation',
'edition-engagement' => 'paie-edition-engagement',
'projection' => 'paie-projection',
            'recap-banque' => 'paie-recap-banque',
            'cotisations-sociales' => 'paie-cotisations-sociales',
            'elements-saisie-dashboard' => 'paie-elements-saisie-dashboard',
            'generee-ief' => 'paie-generee-ief',
            'fermeture-periode' => 'paie-fermeture-periode',
            'edition-salaires-banque' => 'paie-edition-salaires-banque',
            'effectifs-corps' => 'paie-effectifs-corps',
            'non-generee' => 'paie-non-generee',
            'sommes-percues' => 'paie-sommes-percues',
            'edition-enseignants' => 'paie-edition-enseignants',
            'prime-scolaire' => 'paie-prime-scolaire',
            'reliquats' => 'paie-reliquats',
            'double-flux' => 'paie-double-flux',
            'directeurs-interim' => 'paie-directeurs-interim',
            'cumul-enseignants-ief' => 'paie-cumul-enseignants-ief',
            'recap-elements-corps' => 'paie-recap-elements-corps',
            'edition-fonctionnaires' => 'paie-edition-fonctionnaires',
            'mutuelles-sante' => 'paie-mutuelles-sante',
            'situation-affectations' => 'paie-situation-affectations',
            'montants-engages-banque' => 'paie-montants-engages-banque',
            'heures-supplementaires-interim' => 'paie-heures-supplementaires-interim',
            'edition' => 'paie-edition',
        ];

        // État des salaires : contrôleur dédié (filtres propres), export déclaré avant la route générique.
        Route::get('/etat-salaires', [EtatSalaireController::class, 'index'])->name('etat-salaires');
        Route::get('/export/paie-etat-salaires', [EtatSalaireController::class, 'download'])
            ->middleware('throttle:20,1')
            ->name('etat-salaires.export');
        Route::get('/bulletins', [\App\Http\Controllers\PayrollBulletinController::class, 'index'])->name('bulletins');

        foreach ($pages as $path => $slug) {
            Route::get('/'.$path, [PayrollController::class, 'show'])
                ->defaults('slug', $slug)
                ->name($path);
        }




        /*
        |--------------------------------------------------------------------------
        | Délégations de crédits - CRUD
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/delegation-credits',
            [PayrollController::class, 'storeDelegationCredit']
        )
            ->middleware('throttle:30,1')
            ->name('delegation-credits.store');

        Route::put(
            '/delegation-credits/{delegation}',
            [PayrollController::class, 'updateDelegationCredit']
        )
            ->whereNumber('delegation')
            ->middleware('throttle:30,1')
            ->name('delegation-credits.update');

        Route::delete(
            '/delegation-credits/{delegation}',
            [PayrollController::class, 'destroyDelegationCredit']
        )
            ->whereNumber('delegation')
            ->middleware('throttle:30,1')
            ->name('delegation-credits.destroy');



            
/*
|--------------------------------------------------------------------------
| Ventilations des délégations de crédits
|--------------------------------------------------------------------------
*/

Route::get(
    'delegation-credits/{delegation}/ventilations',
    [PayrollController::class, 'showDelegationVentilations']
)->whereNumber('delegation')
 ->name('delegation-credits.ventilations');

Route::post(
    'delegation-credits/{delegation}/ventilations',
    [PayrollController::class, 'storeDelegationVentilation']
)->whereNumber('delegation')
 ->name('delegation-credits.ventilations.store');

Route::put(
    'delegation-credits/{delegation}/ventilations/{ventilation}',
    [PayrollController::class, 'updateDelegationVentilation']
)->whereNumber('delegation')
 ->whereNumber('ventilation')
 ->name('delegation-credits.ventilations.update');

Route::delete(
    'delegation-credits/{delegation}/ventilations/{ventilation}',
    [PayrollController::class, 'destroyDelegationVentilation']
)->whereNumber('delegation')
 ->whereNumber('ventilation')
 ->name('delegation-credits.ventilations.destroy');


        Route::post('/actions/{action}', [PayrollController::class, 'action'])
            ->whereIn('action', array_keys(config('payroll-forms', [])))
            ->middleware('throttle:30,1')
            ->name('action');

        Route::get('/export/{slug}', [PayrollController::class, 'export'])
            ->whereIn('slug', [...array_values($pages), 'paie-etat-salaires'])
            ->middleware('throttle:20,1')
            ->name('export');

        Route::get('/bulletins/{payslip}', [PayrollController::class, 'payslip'])
            ->whereNumber('payslip')
            ->name('payslip');
    });

  

    
});
