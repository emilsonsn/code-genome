<?php

use App\Http\Controllers\RankingController;
use App\Http\Controllers\RepositoryAnalysisController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RepositoryAnalysisController::class, 'index'])->name('repository-analyses.index');
Route::post('/analyses', [RepositoryAnalysisController::class, 'store'])
    ->middleware('throttle:repository-analysis')
    ->name('repository-analyses.store');
Route::get('/analyses/{repositoryAnalysis}', [RepositoryAnalysisController::class, 'show'])->name('repository-analyses.show');
Route::get('/genome', [RepositoryAnalysisController::class, 'genome'])->name('repository-analyses.genome');
Route::get('/ranking', [RankingController::class, 'index'])->name('repository-analyses.ranking');

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
