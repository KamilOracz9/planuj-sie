<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\PriceQueryBuilder;
use App\Traits\HasCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

#[Fillable(['id', 'channel_id', 'currency_id', 'model_id', 'model_type', 'amount'])]
class Price extends BaseModel
{
    use HasCache;

    protected static function boot()
    {
        parent::boot();

        static::bootCache();
    }

    // No CRUD routes: rows are written exclusively through the owning
    // entity's own save, via the HasPrices trait - mirrors ChannelVisibility.
    public static function routes()
    {
        return [
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'prices'], function () {
                    Route::get('/select/{modelType}/{modelId}', [\App\Http\Controllers\PanelControllers\PriceController::class, 'selectByModel']);
                });
            }),
        ];
    }

    private static function clearCache($model = null)
    {
        if (!$model) {
            return;
        }

        static::clearSelectByModelCache($model->model_type, $model->model_id);

        if ($model->wasChanged(['model_type', 'model_id'])) {
            static::clearSelectByModelCache($model->getOriginal('model_type'), $model->getOriginal('model_id'));
        }
    }

    // Public (not private, unlike the other *_select_by_model helpers on
    // ChannelVisibility/AttributeValue): HasPrices::bootPrices() needs to call
    // this explicitly even when no Price::saved()/deleted() event fires - e.g.
    // when a model's entire price list is cleared in one request (empty
    // `prices` array), no updateOrCreate() runs and stale rows are removed via
    // a query-builder mass delete, which doesn't fire model events at all.
    public static function clearSelectByModelCache(?string $modelType, mixed $modelId)
    {
        if (!$modelType || !$modelId) {
            return;
        }

        $modelName = Str::snake(class_basename($modelType));

        foreach (config('app.supported_locales') as $locale) {
            cache()->forget(CacheKeys::PRICES_SELECT_BY_MODEL->value . "_$locale" . "_{$modelName}_{$modelId}");
        }
    }

    public static function newQueryBuilder()
    {
        return new PriceQueryBuilder();
    }
}
