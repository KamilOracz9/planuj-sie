<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Models\Brand;
use App\Models\Channel;
use App\Models\Locale;
use App\Models\User;

Route::group(['middleware' => 'api'], function () {
    Route::post('login', [AuthController::class, 'login']);

    // Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);

        User::routes();
        Brand::routes();
        Channel::routes();
        Locale::routes();
    // });
});
