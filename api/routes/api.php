<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::prefix('news')->group(function () {
        Route::get('/', [NewsController::class, 'index']);
        Route::get('/{id}', [NewsController::class, 'show']);
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('news')->group(function () {
            Route::post('/', [NewsController::class, 'store']);
            Route::put('/{id}', [NewsController::class, 'update']);
            Route::delete('/{id}', [NewsController::class, 'destroy']);
        });

        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });

    });
});
