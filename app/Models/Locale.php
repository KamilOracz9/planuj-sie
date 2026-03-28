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
                Route::put('/{id}', [\App\Http\Controllers\LocaleController::class, 'update']);
                Route::post('/create', [\App\Http\Controllers\LocaleController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\LocaleController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'locales'], function () {
                    Route::get('/', [\App\Http\Controllers\LocaleController::class, 'index']);
                    Route::get('/{id}', [\App\Http\Controllers\LocaleController::class, 'show']);
                });
            })
        ];
    }

    private static function clearCache()
    {
        foreach(config('app.supported_locales') as $locale) {
            cache()->forget(CacheKeys::LOCALES_LIST->value . "_$locale");
        }
    }

    public static function newQueryBuilder()
    {
        return new LocaleQueryBuilder();
    }
}
