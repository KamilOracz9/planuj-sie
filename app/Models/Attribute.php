<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\AttributeQueryBuilder;
use App\Traits\HasCache;
use App\Traits\HasTranslations;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;

#[Fillable(['id', 'order_column'])]
class Attribute extends BaseModel
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
            Route::group(['prefix' => 'attributes'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\AttributeController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\AttributeController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\AttributeController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'attributes'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\AttributeController::class, 'index']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\AttributeController::class, 'show']);
                });
            })
        ];
    }

    private static function clearCache()
    {
        static::clearLocaleCache([CacheKeys::ATTRIBUTES_LIST->value]);
    }

    public static function newQueryBuilder()
    {
        return new AttributeQueryBuilder();
    }
}
