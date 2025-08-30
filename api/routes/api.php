<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CommentController;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    Route::get('/user/{username}', [PerfilController::class, 'index']);

    Route::prefix('news')->group(function () {
        Route::get('/', [NewsController::class, 'index']);
        Route::get('/{id_new}', [NewsController::class, 'show']);
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('news')->group(function () {
            Route::post('/', [NewsController::class, 'store']);
            Route::put('/{id_new}', [NewsController::class, 'update']);
            Route::delete('/{id_new}', [NewsController::class, 'destroy']);
            Route::post('/{id_new}/like', [NewsController::class, 'like']);
            Route::post('/{id_new}/unlike', [NewsController::class, 'unlike']);

            Route::post('/{id_new}/comment', [CommentController::class, 'store']);
            Route::get('/{id_new}/comment', [CommentController::class, 'index']);
            Route::delete('/{id_comment}/comment', [CommentController::class, 'destroy']);

        });

        Route::prefix('/user/me')->group(function () {
            Route::get('/', [PerfilController::class, 'me']);
            Route::put('/update', [PerfilController::class, 'update']);
            Route::get('/avatar', [PerfilController::class, 'avatar']);
        });

        Route::prefix('/user')->group(function () {
            Route::post('/{username}/follow', [UserController::class, 'follow']);                    //
            Route::post('/{username}/unfollow', [UserController::class, 'unfollow']);                //
            Route::get('/{username}/followers', [UserController::class, 'followers']);               //
            Route::get('/{username}/following', [UserController::class, 'following']);               //
        });

        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });

    });
});
