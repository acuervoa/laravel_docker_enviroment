<?php

use App\Http\Controllers\YoutubeController;

Route::middleware(['auth','youtube.quota'])->group(function () {
    Route::get('/youtube/authenticate', [YoutubeController::class, 'authenticate'])->name('youtube.authenticate');
    Route::get('/youtube/callback', [YoutubeController::class, 'authenticate'])->name('youtube.callback');
    Route::get('/youtube/channels', [YoutubeController::class, 'getChannels'])->name('youtube.channels');
    Route::get('/youtube/videos/{channelId}', [YoutubeController::class, 'getChannelVideos'])->name('youtube.videos');
    Route::get('/youtube/save-channels', [YoutubeController::class, 'saveChannelsToDatabase']);
    Route::get('/youtube/save-videos/{channelId}', [YoutubeController::class, 'saveVideosToDatabase']);

});

