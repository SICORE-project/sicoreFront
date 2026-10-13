<?php

use App\Http\Controllers\RecruitmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('sicore.auth')->prefix('personnel/recrutements')->name('recruitment.')->group(function () {
    Route::get('/', [RecruitmentController::class, 'index'])->name('index')->middleware('sicore.permission:recruitment.read');
    Route::get('/importer', [RecruitmentController::class, 'create'])->name('create')->middleware('sicore.permission:recruitment.import');
    Route::get('/modele', [RecruitmentController::class, 'template'])->name('template')->middleware('sicore.permission:recruitment.import');
    Route::post('/apercu', [RecruitmentController::class, 'preview'])->name('preview')->middleware('sicore.permission:recruitment.import');
    Route::post('/', [RecruitmentController::class, 'store'])->name('store')->middleware('sicore.permission:recruitment.import')->block(60, 10);
    Route::post('/annuler', [RecruitmentController::class, 'cancel'])->name('cancel')->middleware('sicore.permission:recruitment.import');
    Route::get('/documents/{event}', [RecruitmentController::class, 'document'])->whereNumber('event')->name('document')->middleware('sicore.permission:recruitment.read');
    Route::get('/{batch}', [RecruitmentController::class, 'show'])->whereNumber('batch')->name('show')->middleware('sicore.permission:recruitment.read');
    Route::post('/{batch}/os', [RecruitmentController::class, 'os'])->whereNumber('batch')->name('os')->middleware('sicore.permission:recruitment.import');
    Route::post('/{batch}/transmettre', [RecruitmentController::class, 'transmit'])->whereNumber('batch')->name('transmit')->middleware('sicore.permission:recruitment.transmit');
    Route::get('/{batch}/agents/{member}/prise-service', [RecruitmentController::class, 'serviceForm'])->whereNumber(['batch', 'member'])->name('service-form')->middleware('sicore.permission:recruitment.service');
    Route::post('/{batch}/agents/{member}/prise-service', [RecruitmentController::class, 'service'])->whereNumber(['batch', 'member'])->name('service')->middleware('sicore.permission:recruitment.service');
    Route::post('/{batch}/agents/{member}/carriere', [RecruitmentController::class, 'transition'])->whereNumber(['batch', 'member'])->name('transition')->middleware('sicore.permission:recruitment.transition');
});
