<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);

});
Route::group(['middleware' => 'auth'], function($routes){

    // Tasks CRUD routes
    Route::group(['prefix' => 'v1/tasks'], function($routes){
        Route::get('/list', [TaskController::class, 'index']); // Get all tasks
        Route::post('/add', [TaskController::class, 'store']); // Create a new task
        Route::get('/{task}', [TaskController::class, 'show']); // Get a single task
        Route::put('/{task}', [TaskController::class, 'update']); // Update a task
        Route::delete('/{task}', [TaskController::class, 'destroy']); // Delete a task
        Route::get('/search', [TaskController::class, 'search']); // Search tasks
    });

});

