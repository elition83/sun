<?php


use App\Http\Controllers\Api\Post_tagController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    //return view('welcome');
});

Route::apiResource('categorys', CategoryController::class);
Route::apiResource('profiles', ProfileController::class);
Route::apiResource('posts', PostController::class);
Route::apiResource('comments', CommentController::class);
Route::apiResource('roles', RoleController::class);


