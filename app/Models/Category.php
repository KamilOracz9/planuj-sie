<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\CategoryQueryBuilder;
use App\Traits\HasAttributes;
use App\Traits\HasCache;
use App\Traits\HasChannelVisibility;
use App\Traits\HasTranslations;
use App\Traits\Media\HasDocumentMedia;
use App\Traits\Media\HasIconMedia;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['id', 'parent_id'])]
class Category extends BaseModel implements HasMedia
{
    use HasTranslations, HasCache, Sluggable, HasAttributes, HasChannelVisibility, HasIconMedia, HasDocumentMedia;

    const PARENT_CATEGORY_TRANSLATIONTABLE_ALIAS = 'parent_category_translations';

    public array $translatable = ['name', 'slug', 'description', 'short_description'];
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function ancestorGroupsForVisibility(): array
    {
        return $this->parent_id ? [[$this->parent]] : [];
    }

    public function registerMediaCollections(): void
    {
        $this->registerIconCollection();
        $this->registerDocumentCollection();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerIconConversions();
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'categories'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\CategoryController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\CategoryController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\CategoryController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'categories'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\CategoryController::class, 'index']);
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\CategoryController::class, 'select']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\CategoryController::class, 'show']);
                });
            }),
            Route::group(['prefix' => 'categories/{id}/media'], function () {
                Route::get('/', [\App\Http\Controllers\PanelControllers\Media\CategoryMediaController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\Media\CategoryMediaController::class, 'store']);
                Route::post('/attach', [\App\Http\Controllers\PanelControllers\Media\CategoryMediaController::class, 'attach']);
                Route::post('/reorder', [\App\Http\Controllers\PanelControllers\Media\CategoryMediaController::class, 'reorder']);
                Route::delete('/{mediaId}', [\App\Http\Controllers\PanelControllers\Media\CategoryMediaController::class, 'destroy']);
            })
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::CATEGORIES_LIST->value]);
        static::clearLocaleCache([CacheKeys::CATEGORIES_SELECT->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::CATEGORIES_LIST->value], $model->id);
        }
    }

    public static function newQueryBuilder()
    {
        return new CategoryQueryBuilder();
    }
}
