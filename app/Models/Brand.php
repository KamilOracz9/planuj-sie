<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\BrandQueryBuilder;
use App\Traits\HasAttributes;
use App\Traits\HasCache;
use App\Traits\HasChannelVisibility;
use App\Traits\HasTranslations;
use App\Traits\Media\HasDocumentMedia;
use App\Traits\Media\HasLogoMedia;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['id'])]
class Brand extends BaseModel implements HasMedia
{
    use HasTranslations, HasCache, Sluggable, HasFactory, HasAttributes, HasChannelVisibility, HasLogoMedia, HasDocumentMedia;

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

    public function registerMediaCollections(): void
    {
        $this->registerLogoCollection();
        $this->registerDocumentCollection();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerLogoConversions();
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
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\BrandController::class, 'select']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\BrandController::class, 'show']);
                });
            }),
            Route::group(['prefix' => 'brands/{id}/media'], function () {
                Route::get('/', [\App\Http\Controllers\PanelControllers\Media\BrandMediaController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\Media\BrandMediaController::class, 'store']);
                Route::post('/attach', [\App\Http\Controllers\PanelControllers\Media\BrandMediaController::class, 'attach']);
                Route::post('/reorder', [\App\Http\Controllers\PanelControllers\Media\BrandMediaController::class, 'reorder']);
                Route::delete('/{mediaId}', [\App\Http\Controllers\PanelControllers\Media\BrandMediaController::class, 'destroy']);
            })
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::BRANDS_LIST->value, CacheKeys::BRANDS_SELECT->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::BRANDS_LIST->value], $model->id);
        }
    }

    public static function newQueryBuilder()
    {
        return new BrandQueryBuilder();
    }
}
