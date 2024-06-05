<?php

use App\Http\Controllers\SubscriptionController;

Route::middleware('auth')->group(function () {
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::delete('/unsubscribe/{id}', [SubscriptionController::class, 'unsubscribe']);
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);
});

