<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\YoutubeController;

Route::middleware('auth')->group(function () {
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::delete('/unsubscribe/{id}', [SubscriptionController::class, 'unsubscribe']);
    Route::get('/subscriptions', [SubscriptionController::class, 'index']);

	Route::resource('/videos', VideoController::class);
    Route::get('/notify-new-videos', [NotificationController::class, 'notifyNewVideos']);
});



Route::get('/youtube/authenticate', [YoutubeController::class,'authenticate'])->name('youtube.authenticate');
Route::get('/youtube/callback', [YoutubeController::class,'authenticate'])->name('youtube.callback');
Route::get('/youtube/channels', [YoutubeController::class,'getChannels'])->name('youtube.channels');
Route::get('/youtube/videos/{channelId}', [YoutubeController::class,'getVideos'])->name('youtube.videos');
Route::get('/youtube/all', [YoutubeController::class, 'getAllChannelsAndVideos'])->name('youtube.all');

Route::get('/', function () {
    return view('welcome');
});
