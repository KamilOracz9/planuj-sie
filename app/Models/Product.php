<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\ProductQueryBuilder;
use App\Traits\HasCache;
use App\Traits\HasTranslations;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;

#[Fillable(['id', 'category_id'])]
class Product extends BaseModel
{
    use HasTranslations, HasCache, Sluggable;

    public array $translatable = ['name', 'slug', 'description', 'short_description'];
    public string $sluggable = 'name';

    protected static function boot()
    {
        parent::boot();

        static::bootSluggable();
        static::bootTranslations();
        static::bootCache();
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'products'], function () {
                Route::put('/{id}', [\App\Http\Controllers\ProductController::class, 'update']);
                Route::post('/create', [\App\Http\Controllers\ProductController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\ProductController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'products'], function () {
                    Route::get('/', [\App\Http\Controllers\ProductController::class, 'index']);
                    Route::get('/{id}', [\App\Http\Controllers\ProductController::class, 'show']);
                });
            })
        ];
    }

    private static function clearCache()
    {
        static::clearLocaleCache([CacheKeys::PRODUCTS_LIST->value]);
    }

    public static function newQueryBuilder()
    {
        return new ProductQueryBuilder();
    }
}
