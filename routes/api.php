<?php

use App\Http\Controllers\ProgressController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/* -----------------------------------------------------------------------
 | AJAX API Endpoints (auth:sanctum or session-based via SPA cookie)
 | --------------------------------------------------------------------- */

Route::middleware('auth:sanctum')->group(function () {

    // Progress tracking — called every ~10 seconds from video player
    Route::post('/lessons/{lesson}/progress', [ProgressController::class, 'update'])
        ->name('api.progress.update');
});
