<?php

use App\Http\Controllers\VideoController;

Route::middleware('auth')->group(function () {
    Route::get('/videos/search', [VideoController::class, 'search']);
    Route::get('/videos', [VideoController::class, 'index']);
    Route::get('/videos/{id}', [VideoController::class, 'show']);
    Route::post('/videos', [VideoController::class, 'store']);
    Route::put('/videos/{id}', [VideoController::class, 'update']);
    Route::delete('/videos/{id}', [VideoController::class, 'destroy']);

    Route::post('/videos/{videoId}/like', [VideoController::class, 'likeVideo']);
    Route::post('/videos/{videoId}/dislike', [VideoController::class, 'dislikeVideo']);

});

