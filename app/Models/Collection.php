<?php

namespace App\Models;

// NOTE: this class name shadows Illuminate\Support\Collection /
// Illuminate\Database\Eloquent\Collection by short name. Never `use` either
// of those inside this file's namespace family (this model, its controller,
// query builder, request, resource) — fully-qualify as
// \Illuminate\Support\Collection if ever needed.

use App\Enums\CacheKeys;
use App\QueryBuilders\CollectionQueryBuilder;
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
class Collection extends BaseModel implements HasMedia
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
            Route::group(['prefix' => 'collections'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\CollectionController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\CollectionController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\CollectionController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'collections'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\CollectionController::class, 'index']);
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\CollectionController::class, 'select']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\CollectionController::class, 'show']);
                });
            }),
            Route::group(['prefix' => 'collections/{id}/media'], function () {
                Route::get('/', [\App\Http\Controllers\PanelControllers\Media\CollectionMediaController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\Media\CollectionMediaController::class, 'store']);
                Route::post('/attach', [\App\Http\Controllers\PanelControllers\Media\CollectionMediaController::class, 'attach']);
                Route::post('/reorder', [\App\Http\Controllers\PanelControllers\Media\CollectionMediaController::class, 'reorder']);
                Route::delete('/{mediaId}', [\App\Http\Controllers\PanelControllers\Media\CollectionMediaController::class, 'destroy']);
            })
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::COLLECTIONS_LIST->value, CacheKeys::COLLECTIONS_SELECT->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::COLLECTIONS_LIST->value], $model->id);
        }
    }

    public static function newQueryBuilder()
    {
        return new CollectionQueryBuilder();
    }
}
