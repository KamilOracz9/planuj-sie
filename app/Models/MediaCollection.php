<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\MediaCollectionQueryBuilder;
use App\Traits\HasCache;
use App\Traits\HasMediaCollectionAssignments;
use App\Traits\HasMediaCollectionConversions;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['id', 'code', 'name', 'kind', 'type'])]
class MediaCollection extends BaseModel
{
    use HasCache, HasMediaCollectionConversions, HasMediaCollectionAssignments;

    // No HasTranslations/Sluggable: code/name/kind/type are admin-facing
    // config, not translatable catalog content - same rationale as Currency.
    public bool $isSluggable = false;

    public const MIME_MAP = [
        'image' => ['image/jpeg', 'image/png', 'image/webp'],
        'document' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'text/csv',
        ],
    ];

    protected static function boot()
    {
        parent::boot();

        static::bootCache();
        static::bootMediaCollectionConversions();
        static::bootMediaCollectionAssignments();

        // media_collection_conversions.media_collection_id cascades, but the
        // actual uploaded `media` rows only reference their collection by a
        // plain `collection_name` string (no FK) - a mass-delete here would
        // never fire Spatie's own model events, so this must be explicit.
        static::deleted(function ($mediaCollection) {
            Media::query()->where('collection_name', $mediaCollection->code)->get()->each->delete();
        });
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'media-collections'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\MediaCollectionController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\MediaCollectionController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\MediaCollectionController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'media-collections'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\MediaCollectionController::class, 'index']);
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\MediaCollectionController::class, 'select']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\MediaCollectionController::class, 'show']);
                });
            }),
            // Generic per-model media routes, shared by all 9 entity types -
            // see config/media.php for the modelType allow-list.
            // Which collections are offered for this model TYPE, per channel -
            // configured centrally on the MediaCollection's own edit page
            // ("Przypisania" tab), not per model instance.
            Route::group(['prefix' => '{modelType}/media-collection-assignments'], function () {
                Route::get('/', [\App\Http\Controllers\PanelControllers\MediaCollectionAssignmentController::class, 'forModelType']);
            })->whereIn('modelType', array_keys(config('media.model_types'))),
            Route::group(['prefix' => '{modelType}/{id}/media'], function () {
                Route::get('/', [\App\Http\Controllers\PanelControllers\Media\MediaController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\Media\MediaController::class, 'store']);
                Route::post('/attach', [\App\Http\Controllers\PanelControllers\Media\MediaController::class, 'attach']);
                Route::post('/reorder', [\App\Http\Controllers\PanelControllers\Media\MediaController::class, 'reorder']);
                Route::delete('/{mediaId}', [\App\Http\Controllers\PanelControllers\Media\MediaController::class, 'destroy']);
            })->whereIn('modelType', array_keys(config('media.model_types'))),
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::MEDIA_COLLECTIONS_LIST->value, CacheKeys::MEDIA_COLLECTIONS_SELECT->value]);
        cache()->forget(CacheKeys::MEDIA_COLLECTIONS_REGISTRY->value);

        // Cheap to just clear every known modelType's assignment cache rather
        // than tracking which ones this specific collection's assignments
        // actually touched.
        foreach (array_keys(config('media.model_types')) as $modelType) {
            cache()->forget(CacheKeys::MEDIA_COLLECTION_ASSIGNMENTS_BY_MODEL_TYPE->value . "_$modelType");
        }

        if ($model) {
            static::clearShowCache([CacheKeys::MEDIA_COLLECTIONS_LIST->value], $model->id);
        }
    }

    // Read by App\Traits\Media\HasMediaCollections on (almost) every media
    // operation - cached as a single blob rather than hitting the DB per
    // call. Returns plain arrays, not models: config('cache.serializable_classes')
    // is false, so cached objects would come back as __PHP_Incomplete_Class.
    public static function registry(): array
    {
        return cache()->remember(
            CacheKeys::MEDIA_COLLECTIONS_REGISTRY->value,
            config('app.cache_lifetime'),
            fn() => static::queryBuilder()
                ->select(['id', 'code', 'name', 'kind', 'type'])
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray()
        );
    }

    public static function newQueryBuilder()
    {
        return new MediaCollectionQueryBuilder();
    }
}
