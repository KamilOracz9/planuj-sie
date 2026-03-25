<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::group(['middleware' => 'api'], function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);

        Route::group(['prefix' => 'users'], function () {
            Route::get('/', [\App\Http\Controllers\UserController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\UserController::class, 'show']);
            Route::post('/create', [\App\Http\Controllers\UserController::class, 'create']);
            Route::delete('/{id}', [\App\Http\Controllers\UserController::class, 'destroy']);
        });

        Route::group(['prefix' => 'brands'], function () {
            Route::put('/{id}', [\App\Http\Controllers\BrandController::class, 'update']);
            Route::post('/create', [\App\Http\Controllers\BrandController::class, 'create']);
            Route::delete('/{id}', [\App\Http\Controllers\BrandController::class, 'destroy']);
        });

        Route::group(['prefix' => '{locale}'], function () {
            Route::group(['prefix' => 'brands'], function () {
                Route::get('/', [\App\Http\Controllers\BrandController::class, 'index']);
                Route::get('/{id}', [\App\Http\Controllers\BrandController::class, 'show']);
            });
        });
    });
});
