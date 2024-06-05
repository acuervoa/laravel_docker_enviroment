<?php

use App\Http\Controllers\NotificationController;

Route::middleware('auth')->group(function () {
    Route::get('/notify-new-videos', [NotificationController::class, 'notifyNewVideos']);
});

