<?php

use App\Http\Controllers\RepositoryAnalysisController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RepositoryAnalysisController::class, 'index'])->name('repository-analyses.index');
Route::post('/analyses', [RepositoryAnalysisController::class, 'store'])->name('repository-analyses.store');
Route::get('/analyses/{repositoryAnalysis}', [RepositoryAnalysisController::class, 'show'])->name('repository-analyses.show');
Route::get('/genome', [RepositoryAnalysisController::class, 'genome'])->name('repository-analyses.genome');

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
