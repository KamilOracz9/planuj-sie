<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\AttributeValueQueryBuilder;
use App\Traits\HasCache;
use App\Traits\Media\HasIconMedia;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;

#[Fillable(['id', 'attribute_id', 'data', 'order_column', 'model_id', 'model_type'])]
class AttributeValue extends BaseModel implements HasMedia
{
    use HasCache, HasIconMedia;

    protected static function boot()
    {
        parent::boot();

        static::bootCache();
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'attribute-values'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\AttributeValueController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\AttributeValueController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\AttributeValueController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'attribute-values'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\AttributeValueController::class, 'index']);
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\AttributeValueController::class, 'select']);
                    Route::get('/select/{modelType}/{modelId}', [\App\Http\Controllers\PanelControllers\AttributeValueController::class, 'selectByModel']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\AttributeValueController::class, 'show']);
                });
            }),
            Route::group(['prefix' => 'attribute-values/{id}/media'], function () {
                Route::get('/', [\App\Http\Controllers\PanelControllers\Media\AttributeValueMediaController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\Media\AttributeValueMediaController::class, 'store']);
                Route::post('/attach', [\App\Http\Controllers\PanelControllers\Media\AttributeValueMediaController::class, 'attach']);
                Route::delete('/{mediaId}', [\App\Http\Controllers\PanelControllers\Media\AttributeValueMediaController::class, 'destroy']);
            })
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::ATTRIBUTE_VALUES_LIST->value]);

        if (!$model) {
            return;
        }

        static::clearShowCache([CacheKeys::ATTRIBUTE_VALUES_LIST->value], $model->id);
        static::clearSelectByModelCache($model->model_type, $model->model_id);

        if ($model->wasChanged(['model_type', 'model_id'])) {
            static::clearSelectByModelCache($model->getOriginal('model_type'), $model->getOriginal('model_id'));
        }
    }

    private static function clearSelectByModelCache(?string $modelType, mixed $modelId)
    {
        if (!$modelType || !$modelId) {
            return;
        }

        $modelName = Str::snake(class_basename($modelType));

        foreach (config('app.supported_locales') as $locale) {
            cache()->forget(CacheKeys::ATTRIBUTE_VALUES_SELECT_BY_MODEL->value . "_$locale" . "_{$modelName}_{$modelId}");
        }
    }

    public static function newQueryBuilder()
    {
        return new AttributeValueQueryBuilder();
    }
}
