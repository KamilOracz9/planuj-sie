<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PanelControllers\AuthController;
use App\Http\Controllers\PanelControllers\MediaStreamController;
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
use App\Models\Locale;
use App\Models\MediaCollection;
use App\Models\Price;
use App\Models\Product;
use App\Models\Series;
use App\Models\User;
use App\Models\Variant;

Route::group(['middleware' => 'api'], function () {
    // Named 'login': Laravel's default guest-redirect (Authenticate
    // middleware / exception handler) hardcodes route('login') for any
    // unauthenticated request that doesn't explicitly ask for JSON - without
    // this name it throws RouteNotFoundException instead of a clean 401.
    // See also the AuthenticationException render override in
    // bootstrap/app.php, which forces JSON for this API-only app instead of
    // ever attempting that redirect.
    Route::post('login', [AuthController::class, 'login'])->name('login');

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('media/{media}/{conversion?}', [MediaStreamController::class, 'show'])->name('media.show');

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
        // Gallery routes are registered by the kamiloracz9/media-gallery
        // package's own service provider (config/gallery.php), not here.
    });
});
