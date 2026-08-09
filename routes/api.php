<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PanelControllers\AuthController;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\AttributeOption;
use App\Models\AttributeType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Channel;
use App\Models\ChannelVisibility;
use App\Models\Collection;
use App\Models\Currency;
use App\Models\Gallery;
use App\Models\Locale;
use App\Models\MediaCollection;
use App\Models\Price;
use App\Models\Product;
use App\Models\Series;
use App\Models\User;
use App\Models\Variant;

Route::group(['middleware' => 'api'], function () {
    Route::post('login', [AuthController::class, 'login']);

    // Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);

        User::routes();
        Brand::routes();
        Series::routes();
        Collection::routes();
        Channel::routes();
        ChannelVisibility::routes();
        Currency::routes();
        Price::routes();
        MediaCollection::routes();
        Locale::routes();
        Category::routes();
        Product::routes();
        Variant::routes();
        Attribute::routes();
        AttributeValue::routes();
        AttributeOption::routes();
        AttributeType::routes();
        Gallery::routes();
    // });
});
