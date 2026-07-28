<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\AttributeQueryBuilder;
use App\Traits\HasCache;
use App\Traits\HasTranslations;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

#[Fillable(['id', 'order_column', 'attribute_type_id'])]
class Attribute extends BaseModel
{
    use HasTranslations, HasCache, Sluggable;

    public array $translatable = ['name', 'slug'];
    public string $sluggable = 'name';

    protected static function boot()
    {
        parent::boot();

        static::bootSluggable();
        static::bootTranslations();
        static::bootCache();

        // attribute_options/attribute_values rows for this attribute are removed by a DB
        // cascade on delete, so their model events never fire — clear their caches here,
        // before the cascade runs, while we can still see which rows will be affected.
        static::deleting(function ($model) {
            static::clearCascadedCache($model);
        });
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'attributes'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\AttributeController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\AttributeController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\AttributeController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'attributes'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\AttributeController::class, 'index']);
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\AttributeController::class, 'select']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\AttributeController::class, 'show']);
                });
            })
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::ATTRIBUTES_LIST->value, CacheKeys::ATTRIBUTES_SELECT->value]);
        cache()->forget(CacheKeys::ATTRIBUTES_WITH_TYPE_LIST->value);

        if ($model) {
            static::clearShowCache([CacheKeys::ATTRIBUTES_LIST->value], $model->id);
        }
    }

    private static function clearCascadedCache($model)
    {
        foreach (config('app.supported_locales') as $locale) {
            cache()->forget(CacheKeys::ATTRIBUTE_OPTIONS_SELECT->value . "_$locale" . "_{$model->id}");
        }

        $affectedModels = AttributeValue::query()
            ->where('attribute_id', $model->id)
            ->get(['model_type', 'model_id'])
            ->unique(fn($value) => $value->model_type . '_' . $value->model_id);

        foreach ($affectedModels as $value) {
            $modelName = Str::snake(class_basename($value->model_type));

            foreach (config('app.supported_locales') as $locale) {
                cache()->forget(CacheKeys::ATTRIBUTE_VALUES_SELECT_BY_MODEL->value . "_$locale" . "_{$modelName}_{$value->model_id}");
            }
        }
    }

    public static function newQueryBuilder()
    {
        return new AttributeQueryBuilder();
    }
}
