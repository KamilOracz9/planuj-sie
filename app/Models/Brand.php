<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\BrandQueryBuilder;
use App\Traits\HasAttributes;
use App\Traits\HasCache;
use App\Traits\HasTranslations;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['id'])]
class Brand extends BaseModel
{
    use HasTranslations, HasCache, Sluggable, HasFactory, HasAttributes;

    public array $translatable = ['name', 'slug'];
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

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::BRANDS_LIST->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::BRANDS_LIST->value], $model->id);
        }
    }

    public static function newQueryBuilder()
    {
        return new BrandQueryBuilder();
    }
}
