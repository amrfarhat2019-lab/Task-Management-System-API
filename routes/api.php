<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/


Route::post('/auth/login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum'])->group(function () {

    
    Route::get('/tasks', [TaskController::class, 'index'])
        ->middleware('role:user,manager'); 

    
    Route::post('/tasks', [TaskController::class, 'store'])
        ->middleware('role:manager');

    
    Route::get('/tasks/{id}', [TaskController::class, 'show'])
        ->middleware('role:user,manager');

    
    Route::put('/tasks/{id}', [TaskController::class, 'update'])
        ->middleware('role:user,manager');
});
