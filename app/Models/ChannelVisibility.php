<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\ChannelVisibilityQueryBuilder;
use App\Traits\HasCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

#[Fillable(['id', 'channel_id', 'model_id', 'model_type', 'is_enabled'])]
class ChannelVisibility extends BaseModel
{
    use HasCache;

    protected static function boot()
    {
        parent::boot();

        static::bootCache();
    }

    // No CRUD routes: rows are written exclusively through the owning
    // entity's own save, via the HasChannelVisibility trait. This is the
    // only route ChannelVisibility exposes directly - read-back for the
    // owning entity's edit form.
    public static function routes()
    {
        return [
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'channel-visibilities'], function () {
                    Route::get('/select/{modelType}/{modelId}', [\App\Http\Controllers\PanelControllers\ChannelVisibilityController::class, 'selectByModel']);
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

    private static function clearSelectByModelCache(?string $modelType, mixed $modelId)
    {
        if (!$modelType || !$modelId) {
            return;
        }

        $modelName = Str::snake(class_basename($modelType));

        foreach (config('app.supported_locales') as $locale) {
            cache()->forget(CacheKeys::CHANNEL_VISIBILITIES_SELECT_BY_MODEL->value . "_$locale" . "_{$modelName}_{$modelId}");
        }
    }

    public static function newQueryBuilder()
    {
        return new ChannelVisibilityQueryBuilder();
    }
}
