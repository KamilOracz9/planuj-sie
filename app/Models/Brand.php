<?php

namespace App\Models;

use App\Enums\CacheKeys;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug'])]
class Brand extends BaseModel
{
    use HasTranslations;

    const LIST_FIELDS = ['id', 'name', 'slug'];

    protected array $translatable = ['name', 'slug'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($brand) {
            $brand->setTranslations('slug', array_map(fn($item) => Str::slug($item), $brand->getTranslations('name')));

            cache()->forget(CacheKeys::BRANDS_LIST->value);
        });

        static::updating(function ($brand) {
            $brand->setTranslations('slug', array_map(fn($item) => Str::slug($item), $brand->getTranslations('name')));

            cache()->forget(CacheKeys::BRANDS_LIST->value);
        });

        static::deleting(function () {
            cache()->forget(CacheKeys::BRANDS_LIST->value);
        });
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'brands'], function () {
                Route::put('/{id}', [\App\Http\Controllers\BrandController::class, 'update']);
                Route::post('/create', [\App\Http\Controllers\BrandController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\BrandController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'brands'], function () {
                    Route::get('/', [\App\Http\Controllers\BrandController::class, 'index']);
                    Route::get('/{id}', [\App\Http\Controllers\BrandController::class, 'show']);
                });
            })
        ];
    }
}
