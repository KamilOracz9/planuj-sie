<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\BrandQueryBuilder;
use App\Traits\HasCache;
use App\Traits\HasTranslations;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;

#[Fillable(['id'])]
class Brand extends BaseModel
{
    use HasTranslations, HasCache, Sluggable;

    public array $translatable = ['name', 'slug'];
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
            Route::group(['prefix' => 'brands'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\BrandController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\BrandController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\BrandController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'brands'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\BrandController::class, 'index']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\BrandController::class, 'show']);
                });
            })
        ];
    }

    private static function clearCache()
    {
        static::clearLocaleCache([CacheKeys::BRANDS_LIST->value]);
    }

    public static function newQueryBuilder()
    {
        return new BrandQueryBuilder();
    }
}
