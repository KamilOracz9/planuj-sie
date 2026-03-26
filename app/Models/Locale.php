<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\Models\Translations\LocaleTranslation;
use App\QueryBuilders\LocaleQueryBuilder;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Support\Facades\Route;

#[Fillable(['code', 'id'])]
#[Hidden(['id'])]
class Locale extends BaseModel
{
    use HasTranslations;

    protected static function boot()
    {
        parent::boot();

        static::creating(function () {
            self::clearCache();
        });

        static::updating(function () {
            self::clearCache();
        });

        static::deleting(function () {
            self::clearCache();
        });
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

    public static function getListFields()
    {
        return [
            self::columnName('id'),
            self::columnName('code'),
            LocaleTranslation::columnName('name'),
        ];
    }

    private static function clearCache()
    {
        cache()->forget(CacheKeys::LOCALES_LIST->value);
    }

    public static function newQueryBuilder()
    {
        return new LocaleQueryBuilder();
    }
}
