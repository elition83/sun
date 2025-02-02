<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    return "hello Test";
});
Route::get('posts',[PostController::class, 'index']);