<?php

use App\Http\Controllers\TaskController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


// user register route
Route::post('/register', [AuthController::class, 'register']);

// user login route
Route::post('/login', [AuthController::class, 'login']);

// protected routes
Route::group(['middleware' => 'auth:sanctum'], function() {
    Route::get('/tasks/today', [TaskController::class, 'getTasksToday']);
    Route::get('/tasks/tomorrow', [TaskController::class, 'getTasksTomorrow']);
    Route::get('/tasks/after-tomorrow', [TaskController::class, 'getTasksAfterTomorrow']);
    Route::post('/tasks/by-date', [TaskController::class, 'getTasksByDate']);

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});