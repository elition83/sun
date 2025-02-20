<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\RoleController;
 

Route::get('/', function () {
    return view('welcome');
});

//Profile
Route::group(['prefix' => 'profiles'], function() {
    Route::get('',[ProfileController::class, 'index']);
    Route::get('index',[ProfileController::class, 'index']);
    Route::get('store',[ProfileController::class, 'store']);
    Route::get('{profile}',[ProfileController::class, 'show']);
    Route::get('{profile}/show',[ProfileController::class, 'show']);    
    Route::get('{profile}/update',[ProfileController::class, 'update']);
    Route::get('{profile}/destroy',[ProfileController::class, 'destroy']);
});

//Post
Route::group(['prefix' => 'posts'], function() {
    Route::get('',[PostController::class, 'index']);
    Route::get('index',[PostController::class, 'index']);
    Route::get('store',[PostController::class, 'store']);
    Route::get('{post}/show',[PostController::class, 'show']);
    Route::get('{post}/update',[PostController::class, 'update']);
    Route::get('{post}/destroy',[PostController::class, 'destroy']);
});

//Category
Route::group(['prefix' => 'categories'], function(){
    Route::get('',[CategoryController::class, 'index']);
    Route::get('index',[CategoryController::class, 'index']);
    Route::get('store',[CategoryController::class, 'store']);
    Route::get('{category}/show',[CategoryController::class, 'show']);
    Route::get('{category}/update',[CategoryController::class, 'update']);
    Route::get('{category}/destroy',[CategoryController::class, 'destroy']);
});

//Comments
Route::group(['prefix' => 'comments'], function(){
    Route::get('', [CommentController::class, 'index']);
    Route::get('index', [CommentController::class, 'index']);
    Route::get('store', [CommentController::class, 'store']);
    Route::get('{comment}/show', [CommentController::class, 'show']);
    Route::get('{comment}/update', [CommentController::class, 'update']);
    Route::get('{comment}/destroy', [CommentController::class, 'destroy']);
});

//Role
Route::group(['prefix' => 'roles'], function(){
    Route::get('', [RoleController::class, 'index']);
    Route::get('index', [RoleController::class, 'index']);
    Route::get('store', [RoleController::class, 'store']);
    Route::get('{role}/show', [RoleController::class, 'show']);
    Route::get('{role}/update', [RoleController::class, 'update']);
    Route::get('{role}/destroy', [RoleController::class, 'destroy']);
});