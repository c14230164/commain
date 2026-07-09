<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectExportImportController;

Route::get('/', [ProjectController::class, 'home'])->name('home');
Route::prefix('projects')->name('projects.')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('index');
    Route::post('/', [ProjectController::class, 'store'])->name('store');
    Route::post('/import', [ProjectExportImportController::class, 'import'])->name('import');
    Route::post('/merge', [ProjectExportImportController::class, 'merge'])->name('merge');
    Route::get('/{project}/export', [ProjectExportImportController::class, 'export'])->name('export');
    Route::get('/{project}/setup', [ProjectController::class, 'setup'])->name('setup');
    Route::get('/{project}/review', [ProjectController::class, 'review'])->name('review');
    Route::post('/{project}/questions', [ProjectController::class, 'storeQuestions'])->name('questions.store');
    
    // Core Castor Features
    Route::post('/{project}/respondent', [ProjectController::class, 'storeRespondent'])->name('respondent.store');
    Route::get('/{project}/summary', [ProjectController::class, 'getSummary'])->name('summary');
    Route::get('/{project}/respondents', [ProjectController::class, 'getRespondents'])->name('respondents.list');
    Route::get('/{project}/respondent/{respondent}', [ProjectController::class, 'getRespondentFull'])->name('respondent.full');
    Route::delete('/{project}/respondent/{respondent}', [ProjectController::class, 'deleteRespondent'])->name('respondent.delete');
    Route::get('/questions/{question}/suggestions', [ProjectController::class, 'getSuggestions'])->name('questions.suggestions');
    Route::get('/variables/{variable}/respondents', [ProjectController::class, 'getVariableRespondents'])->name('variables.respondents');
    Route::post('/variables/{variable}/rename', [ProjectController::class, 'renameVariable'])->name('variables.rename');
    Route::delete('/{project}/delete', [ProjectController::class, 'destroy'])->name('destroy');
});
