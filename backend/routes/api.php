<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/tasks', [TaskController::class, "createTask"]);
    Route::get('/tasks', [TaskController::class, "getAllTasksByUserId"]);

    Route::middleware('task.owner')->group(function () {
        Route::put('/tasks/{task}', [TaskController::class, "updateTask"]);
        Route::delete('/tasks/{task}', [TaskController::class, "deleteTask"]);
        Route::post('/tasks/{task}/reminder', [TaskController::class, "createOrUpdateTaskReminder"]);
        Route::delete('/tasks/{task}/reminder', [TaskController::class, "deleteTaskReminder"]);
    });
});
