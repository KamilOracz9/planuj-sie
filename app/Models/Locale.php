<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\LocaleQueryBuilder;
use App\Traits\HasCache;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;

#[Fillable(['code', 'id'])]
class Locale extends BaseModel
{
    use HasTranslations, HasCache;

    private array $translatable = ['name'];

    protected static function boot()
    {
        parent::boot();

        static::bootTranslations();
        static::bootCache();
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'locales'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\LocaleController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\LocaleController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\LocaleController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'locales'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\LocaleController::class, 'index']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\LocaleController::class, 'show']);
                });
            })
        ];
    }

    private static function clearCache()
    {
        static::clearLocaleCache([CacheKeys::LOCALES_LIST->value]);
    }

    public static function newQueryBuilder()
    {
        return new LocaleQueryBuilder();
    }
}
