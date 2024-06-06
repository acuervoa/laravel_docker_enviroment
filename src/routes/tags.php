<?php

use App\Http\Controllers\TagController;

Route::post('/tags', [TagController::class, 'addTag']);
Route::post('/videos/{videoId}/tags', [TagController::class, 'attachTagToVideo']);
Route::post('/channels/{channelId}/tags', [TagController::class, 'attachTagToChannel']);
Route::post('/lists/{listId}/tags', [TagController::class, 'attachTagToList']);

