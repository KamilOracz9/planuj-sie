<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\ProductQueryBuilder;
use App\Traits\HasAttributes;
use App\Traits\HasCache;
use App\Traits\HasTranslations;
use App\Traits\Media\HasDocumentMedia;
use App\Traits\Media\HasGalleryMedia;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['id'])]
class Product extends BaseModel implements HasMedia
{
    use HasTranslations, HasCache, Sluggable, HasAttributes, HasGalleryMedia, HasDocumentMedia;

    public array $translatable = ['name', 'slug', 'description', 'short_description'];
    public string $sluggable = 'name';

    protected static function boot()
    {
        parent::boot();

        static::bootSluggable();
        static::bootTranslations();
        static::bootAttributes();
        static::bootCache();
    }

    public function registerMediaCollections(): void
    {
        $this->registerGalleryCollections();
        $this->registerDocumentCollection();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerGalleryConversions();
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'products'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\ProductController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\ProductController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\ProductController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'products'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\ProductController::class, 'index']);
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\ProductController::class, 'select']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\ProductController::class, 'show']);
                });
            }),
            Route::group(['prefix' => 'products/{id}/media'], function () {
                Route::get('/', [\App\Http\Controllers\PanelControllers\Media\ProductMediaController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\Media\ProductMediaController::class, 'store']);
                Route::post('/attach', [\App\Http\Controllers\PanelControllers\Media\ProductMediaController::class, 'attach']);
                Route::post('/reorder', [\App\Http\Controllers\PanelControllers\Media\ProductMediaController::class, 'reorder']);
                Route::delete('/{mediaId}', [\App\Http\Controllers\PanelControllers\Media\ProductMediaController::class, 'destroy']);
            })
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::PRODUCTS_LIST->value, CacheKeys::PRODUCTS_SELECT->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::PRODUCTS_LIST->value], $model->id);
        }
    }

    public static function newQueryBuilder()
    {
        return new ProductQueryBuilder();
    }
}
