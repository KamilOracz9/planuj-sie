<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\AttributeTypeQueryBuilder;
use App\Traits\HasCache;
use App\Traits\HasTranslations;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;

#[Fillable(['id', 'order_column'])]
class AttributeType extends BaseModel
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
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'attribute-types'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\AttributeTypeController::class, 'index']);
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\AttributeTypeController::class, 'select']);
                });
            })
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::ATTRIBUTE_TYPES_LIST->value, CacheKeys::ATTRIBUTE_TYPES_SELECT->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::ATTRIBUTE_TYPES_LIST->value], $model->id);
        }
    }

    public static function newQueryBuilder()
    {
        return new AttributeTypeQueryBuilder();
    }
}
