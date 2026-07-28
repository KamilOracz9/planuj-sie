<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\ProductQueryBuilder;
use App\Traits\HasAttributes;
use App\Traits\HasCache;
use App\Traits\HasTranslations;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;

#[Fillable(['id'])]
class Product extends BaseModel
{
    use HasTranslations, HasCache, Sluggable, HasAttributes;

    public array $translatable = ['name', 'slug', 'description', 'short_description'];
    public string $sluggable = 'name';

    protected static function boot()
    {
        parent::boot();

        static::bootSluggable();
        static::bootTranslations();
        static::bootAttributes();
        static::bootCache();
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'products'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\ProductController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\ProductController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\ProductController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'products'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\ProductController::class, 'index']);
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\ProductController::class, 'select']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\ProductController::class, 'show']);
                });
            })
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::PRODUCTS_LIST->value, CacheKeys::PRODUCTS_SELECT->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::PRODUCTS_LIST->value], $model->id);
        }
    }

    public static function newQueryBuilder()
    {
        return new ProductQueryBuilder();
    }
}
