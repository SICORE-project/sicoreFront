<?php

use App\Http\Controllers\Parametrage\AnneeAcademiqueController;
use App\Http\Controllers\Parametrage\CategorieController;
use App\Http\Controllers\Parametrage\CorpsController;
use App\Http\Controllers\Parametrage\DiplomesController;
use App\Http\Controllers\Parametrage\SpecialiteController;
use App\Http\Controllers\Parametrage\EnseignantSpecialiteController;
use App\Http\Controllers\Parametrage\EnseignantController;
use App\Http\Controllers\Parametrage\GradeController;
use App\Http\Controllers\Parametrage\IefController;
use App\Http\Controllers\Parametrage\InspectionAcademieController;
use App\Http\Controllers\Parametrage\InstitutionFinanciereController;
use App\Http\Controllers\Parametrage\LieuServiceController;
use App\Http\Controllers\Parametrage\PeriodePaieController;
use App\Http\Controllers\Parametrage\RubriquePaieController;
use App\Http\Controllers\Parametrage\SyndicatController;
use App\Http\Controllers\Parametrage\RegionController;
use App\Http\Controllers\Parametrage\DepartementController;
use App\Http\Controllers\Parametrage\CommuneController;
use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\Admin\LieuServiceController;

/*
|--------------------------------------------------------------------------
| Module Paramétrage
|--------------------------------------------------------------------------
*/

Route::middleware('sicore.auth')
    ->prefix('parametrage')
    ->group(function (): void {

        /*
        |--------------------------------------------------------------------------
        | Gestion des enseignants
        |--------------------------------------------------------------------------
        */

        Route::get('/enseignants', [EnseignantController::class, 'index'])->name('enseignants.index');
        Route::get('/enseignants/iefs', [EnseignantController::class, 'ieFs'])->name('enseignants.iefs');
        Route::get('/enseignants/etablissements', [EnseignantController::class, 'etablissements'])->name('enseignants.etablissements');
        Route::get('/enseignants/nouveau', [EnseignantController::class, 'create'])->name('enseignants.create');
        Route::post('/enseignants', [EnseignantController::class, 'store'])->name('enseignants.store');
        Route::post('/enseignants/referentiels', [EnseignantController::class, 'storeReferentiel'])->name('enseignants.referentiels.store');
        Route::get('/enseignants/{enseignant}/modifier', [EnseignantController::class, 'edit'])->whereNumber('enseignant')->name('enseignants.edit');
        Route::put('/enseignants/{enseignant}', [EnseignantController::class, 'update'])->whereNumber('enseignant')->name('enseignants.update');
        Route::delete('/enseignants/{enseignant}', [EnseignantController::class, 'destroy'])->whereNumber('enseignant')->name('enseignants.destroy');
        Route::get('/enseignants/{enseignant}', [EnseignantSpecialiteController::class, 'show'])->whereNumber('enseignant')
            ->name('enseignants.show');
        Route::post('/enseignants/{enseignant}/disciplines', [EnseignantSpecialiteController::class, 'store'])
            ->middleware('sicore.permission:enseignants.disciplines.associer')
            ->name('enseignants.disciplines.store');

        /*
        |--------------------------------------------------------------------------
        | Paramètres
        |--------------------------------------------------------------------------
        */

        Route::get('/parametres/corps', [CorpsController::class, 'index'])->name('parametres.corps.index');
        Route::post('/parametres/corps', [CorpsController::class, 'store'])->name('parametres.corps.store');
        Route::put('/parametres/corps/{corps}', [CorpsController::class, 'update'])->whereNumber('corps')->name('parametres.corps.update');
        Route::delete('/parametres/corps/{corps}', [CorpsController::class, 'destroy'])->whereNumber('corps')->name('parametres.corps.destroy');
        Route::get('/parametres/grades', [GradeController::class, 'index'])->name('parametres.grades.index');
        Route::post('/parametres/grades', [GradeController::class, 'store'])->name('parametres.grades.store');
        Route::put('/parametres/grades/{grade}', [GradeController::class, 'update'])->whereNumber('grade')->name('parametres.grades.update');
        Route::delete('/parametres/grades/{grade}', [GradeController::class, 'destroy'])->whereNumber('grade')->name('parametres.grades.destroy');

        Route::get('/parametres/categories', [CategorieController::class, 'index'])->name('parametres.categories.index');
        Route::post('/parametres/categories', [CategorieController::class, 'store'])->name('parametres.categories.store');
        Route::put('/parametres/categories/{category}', [CategorieController::class, 'update'])->whereNumber('category')->name('parametres.categories.update');
        Route::delete('/parametres/categories/{category}', [CategorieController::class, 'destroy'])->whereNumber('category')->name('parametres.categories.destroy');

        Route::get('/parametres/ief', [IefController::class, 'index'])
            ->name('parametres.ief.index');
        Route::post('/parametres/ief', [IefController::class, 'store'])
            ->name('parametres.ief.store');
        Route::put('/parametres/ief/{ief}', [IefController::class, 'update'])
            ->whereNumber('ief')->name('parametres.ief.update');
        Route::delete('/parametres/ief/{ief}', [IefController::class, 'destroy'])
            ->whereNumber('ief')->name('parametres.ief.destroy');

        Route::get('/parametres/disciplines', [SpecialiteController::class, 'index'])
            ->name('parametres.disciplines.index');
        Route::post('/parametres/disciplines', [SpecialiteController::class, 'store'])
            ->name('parametres.disciplines.store');
        Route::put('/parametres/disciplines/{discipline}', [SpecialiteController::class, 'update'])
            ->whereNumber('discipline')->name('parametres.disciplines.update');
        Route::patch('/parametres/disciplines/{discipline}/statut', [SpecialiteController::class, 'updateStatus'])
            ->whereNumber('discipline')->name('parametres.disciplines.status');
        Route::delete('/parametres/disciplines/{discipline}', [SpecialiteController::class, 'destroy'])
            ->whereNumber('discipline')->name('parametres.disciplines.destroy');

        Route::get('/parametres/annees-academiques', [AnneeAcademiqueController::class, 'index'])
            ->name('parametres.annees-academiques.index');
        Route::post('/parametres/annees-academiques', [AnneeAcademiqueController::class, 'store'])
            ->name('parametres.annees-academiques.store');
        Route::put('/parametres/annees-academiques/{annee}', [AnneeAcademiqueController::class, 'update'])
            ->whereNumber('annee')->name('parametres.annees-academiques.update');
        Route::patch('/parametres/annees-academiques/{annee}/activer', [AnneeAcademiqueController::class, 'activate'])
            ->whereNumber('annee')->name('parametres.annees-academiques.activate');
        Route::patch('/parametres/annees-academiques/{annee}/desactiver', [AnneeAcademiqueController::class, 'deactivate'])
            ->whereNumber('annee')->name('parametres.annees-academiques.deactivate');
        Route::patch('/parametres/annees-academiques/{annee}/cloturer', [AnneeAcademiqueController::class, 'close'])
            ->whereNumber('annee')->name('parametres.annees-academiques.close');
        Route::delete('/parametres/annees-academiques/{annee}', [AnneeAcademiqueController::class, 'destroy'])
            ->whereNumber('annee')->name('parametres.annees-academiques.destroy');

        Route::get('/parametres/rubriques-paie', [RubriquePaieController::class, 'index'])
            ->name('parametres.rubriques-paie.index');
        Route::post('/parametres/rubriques-paie', [RubriquePaieController::class, 'store'])
            ->name('parametres.rubriques-paie.store');
        Route::put('/parametres/rubriques-paie/{rubrique}', [RubriquePaieController::class, 'update'])
            ->whereNumber('rubrique')->name('parametres.rubriques-paie.update');
        Route::delete('/parametres/rubriques-paie/{rubrique}', [RubriquePaieController::class, 'destroy'])
            ->whereNumber('rubrique')->name('parametres.rubriques-paie.destroy');

        Route::get('/parametres/periodes-paie', [PeriodePaieController::class, 'index'])
            ->name('parametres.periodes-paie.index');
        Route::post('/parametres/periodes-paie', [PeriodePaieController::class, 'store'])
            ->name('parametres.periodes-paie.store');
        Route::put('/parametres/periodes-paie/{periode}', [PeriodePaieController::class, 'update'])
            ->whereNumber('periode')->name('parametres.periodes-paie.update');
        Route::delete('/parametres/periodes-paie/{periode}', [PeriodePaieController::class, 'destroy'])
            ->whereNumber('periode')->name('parametres.periodes-paie.destroy');

        Route::get('/parametres/ia', [InspectionAcademieController::class, 'index'])->name('parametres.ia.index');
        Route::post('/parametres/ia', [InspectionAcademieController::class, 'store']) ->name('parametres.ia.store');
        Route::put('/parametres/ia/{ia}', [InspectionAcademieController::class, 'update'])->whereNumber('ia')->name('parametres.ia.update');
        Route::delete('/parametres/ia/{ia}', [InspectionAcademieController::class, 'destroy'])->whereNumber('ia')->name('parametres.ia.destroy');
        Route::get('/parametres/ia/nouvelle', [InspectionAcademieController::class, 'create'])->name('parametres.ia.create');

        
        Route::get('/parametres/regions', [RegionController::class, 'index'])->name('parametres.regions.index');
        Route::post('/parametres/regions', [RegionController::class, 'store'])->name('parametres.regions.store');
        Route::put('/parametres/regions/{region}', [RegionController::class, 'update'])->whereNumber('region')->name('parametres.regions.update');
        Route::patch('/parametres/regions/{region}/statut', [RegionController::class, 'changeStatus'])->whereNumber('region')->name('parametres.regions.status');
        Route::delete('/parametres/regions/{region}', [RegionController::class, 'destroy'])->whereNumber('region')->name('parametres.regions.destroy');

        Route::get('/parametres/departements',[DepartementController::class, 'index'])->name('parametres.departements.index');
        Route::post('/parametres/departements',[DepartementController::class, 'store'])->name('parametres.departements.store');
        Route::put('/parametres/departements/{departement}',[DepartementController::class, 'update'])->whereNumber('departement')->name('parametres.departements.update');
        Route::patch('/parametres/departements/{departement}/statut',[DepartementController::class, 'changeStatus'])->whereNumber('departement')->name('parametres.departements.status');
        Route::delete('/parametres/departements/{departement}',[DepartementController::class, 'destroy'])->whereNumber('departement')->name('parametres.departements.destroy');

        Route::get('/parametres/communes',[CommuneController::class, 'index'])->name('parametres.communes.index');  
        Route::post('/parametres/communes',[CommuneController::class, 'store'])->name('parametres.communes.store');
        Route::put('/parametres/communes/{commune}',[CommuneController::class, 'update'])->whereNumber('commune')->name('parametres.communes.update');
        Route::patch('/parametres/communes/{commune}/statut',[CommuneController::class, 'changeStatus'])->whereNumber('commune')->name('parametres.communes.status');
        Route::delete('/parametres/communes/{commune}',[CommuneController::class, 'destroy'])->whereNumber('commune')->name('parametres.communes.destroy');

        
        Route::get('/parametres/etablissements', [LieuServiceController::class, 'index'])
            ->name('parametres.lieux-service.index');
        Route::post('/parametres/etablissements', [LieuServiceController::class, 'store'])
            ->name('parametres.lieux-service.store');
        Route::put('/parametres/etablissements/{lieu}', [LieuServiceController::class, 'update'])
            ->name('parametres.lieux-service.update');
        Route::delete('/parametres/etablissements/{lieu}', [LieuServiceController::class, 'destroy'])
            ->whereNumber('lieu')->name('parametres.lieux-service.destroy');
        Route::patch('/parametres/etablissements/{lieu}/statut', [LieuServiceController::class, 'updateStatus'])
            ->name('parametres.lieux-service.status');
        Route::post('/parametres/etablissements/{lieu}/affectations', [LieuServiceController::class, 'storeAssignment'])
            ->name('parametres.lieux-service.affectations.store');

        // Compatibilité avec les anciennes URL.
        Route::get('/parametres/lieux-service', [LieuServiceController::class, 'index']);
        Route::post('/parametres/lieux-service', [LieuServiceController::class, 'store']);
        Route::put('/parametres/lieux-service/{lieu}', [LieuServiceController::class, 'update']);
        Route::delete('/parametres/lieux-service/{lieu}', [LieuServiceController::class, 'destroy'])->whereNumber('lieu');
        Route::patch('/parametres/lieux-service/{lieu}/statut', [LieuServiceController::class, 'updateStatus']);
        Route::post('/parametres/lieux-service/{lieu}/affectations', [LieuServiceController::class, 'storeAssignment']);

        Route::get('/parametres/institutions-financieres', [InstitutionFinanciereController::class, 'index'])
            ->name('parametres.institutions-financieres');
        Route::post('/parametres/institutions-financieres', [InstitutionFinanciereController::class, 'store'])
            ->name('parametres.institutions-financieres.store');
        Route::put('/parametres/institutions-financieres/{institution}', [InstitutionFinanciereController::class, 'update'])
            ->name('parametres.institutions-financieres.update');
        Route::patch('/parametres/institutions-financieres/{institution}/statut', [InstitutionFinanciereController::class, 'updateStatus'])
            ->name('parametres.institutions-financieres.status');
        Route::delete('/parametres/institutions-financieres/{institution}', [InstitutionFinanciereController::class, 'destroy'])
            ->name('parametres.institutions-financieres.destroy');
        Route::post('/parametres/comptes-bancaires-enseignants', [InstitutionFinanciereController::class, 'storeTeacherBankAccount'])
            ->name('parametres.comptes-bancaires-enseignants.store');

        Route::middleware('diplomes.manage')->group(function (): void {
            Route::get('/parametres/diplomes', [DiplomesController::class, 'index'])->name('parametres.diplomes.index');
            Route::post('/parametres/diplomes', [DiplomesController::class, 'store'])->name('parametres.diplomes.store');
            Route::put('/parametres/diplomes/{diplome}', [DiplomesController::class, 'update'])->name('parametres.diplomes.update');
            Route::delete('/parametres/diplomes/{diplome}', [DiplomesController::class, 'destroy'])->name('parametres.diplomes.destroy');
        });
        Route::post('/parametres/syndicats', [SyndicatController::class, 'store'])->middleware('sicore.permission:parametrage.syndicats.manage')->name('parametres.syndicats.store');
        Route::get('/parametres/syndicats', [SyndicatController::class, 'index'])->middleware('sicore.permission:parametrage.syndicats.read')->name('parametres.syndicats.index');
        Route::get('/parametres/syndicats/verifier-unicite', [SyndicatController::class, 'checkUniqueness'])->middleware('sicore.permission:parametrage.syndicats.manage')->name('parametres.syndicats.check-uniqueness');
        Route::get('/parametres/syndicats/options-association', [SyndicatController::class, 'associationOptions'])->name('parametres.syndicats.association-options');
        Route::put('/parametres/syndicats/{id}', [SyndicatController::class, 'update'])->whereNumber('id')->middleware('sicore.permission:parametrage.syndicats.manage')->name('parametres.syndicats.update');
        Route::delete('/parametres/syndicats/{id}', [SyndicatController::class, 'destroy'])->whereNumber('id')->middleware('sicore.permission:parametrage.syndicats.manage')->name('parametres.syndicats.destroy');

    });
