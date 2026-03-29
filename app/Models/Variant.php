<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\VariantQueryBuilder;
use App\Traits\HasCache;
use App\Traits\HasTranslations;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;

#[Fillable(['id', 'product_id'])]
class Variant extends BaseModel
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
            Route::group(['prefix' => 'variants'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\VariantController::class, 'update']);
                Route::post('/create', [\App\Http\Controllers\PanelControllers\VariantController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\VariantController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'variants'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\VariantController::class, 'index']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\VariantController::class, 'show']);
                });
            })
        ];
    }

    private static function clearCache()
    {
        static::clearLocaleCache([CacheKeys::VARIANTS_LIST->value]);
    }

    public static function newQueryBuilder()
    {
        return new VariantQueryBuilder();
    }
}
