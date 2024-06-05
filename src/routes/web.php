<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return view('welcome');
});

Auth::routes();

require __DIR__.'/subscription.php';
require __DIR__.'/video.php';
require __DIR__.'/notification.php';
require __DIR__.'/youtube.php';

