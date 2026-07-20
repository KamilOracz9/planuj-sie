<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\AttributeValueQueryBuilder;
use App\Traits\HasCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;

#[Fillable(['id', 'attribute_id', 'data', 'order_column', 'model_id', 'model_type'])]
class AttributeValue extends BaseModel
{
    use HasCache;

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
            })
        ];
    }

    private static function clearCache()
    {
        static::clearLocaleCache([CacheKeys::ATTRIBUTE_VALUES_LIST->value]);
    }

    public static function newQueryBuilder()
    {
        return new AttributeValueQueryBuilder();
    }
}
