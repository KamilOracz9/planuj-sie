<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\SeriesQueryBuilder;
use App\Traits\HasAttributes;
use App\Traits\HasCache;
use App\Traits\HasChannelVisibility;
use App\Traits\HasTranslations;
use App\Traits\Media\HasMediaCollections;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;

#[Fillable(['id'])]
class Series extends BaseModel implements HasMedia
{
    use HasTranslations, HasCache, Sluggable, HasFactory, HasAttributes, HasChannelVisibility, HasMediaCollections;

    protected $table = 'series';

    public array $translatable = ['name', 'slug'];
    public string $sluggable = 'name';

    protected static function boot()
    {
        parent::boot();

        static::bootSluggable();
        static::bootTranslations();
        static::bootAttributes();
        static::bootChannelVisibility();
        static::bootCache();
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'series'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\SeriesController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\SeriesController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\SeriesController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'series'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\SeriesController::class, 'index']);
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\SeriesController::class, 'select']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\SeriesController::class, 'show']);
                });
            }),
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::SERIES_LIST->value, CacheKeys::SERIES_SELECT->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::SERIES_LIST->value], $model->id);
        }
    }

    public static function newQueryBuilder()
    {
        return new SeriesQueryBuilder();
    }
}
