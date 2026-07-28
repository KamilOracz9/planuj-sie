<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\AttributeOptionQueryBuilder;
use App\Traits\HasCache;
use App\Traits\HasTranslations;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;

#[Fillable(['id', 'attribute_id', 'order_column'])]
class AttributeOption extends BaseModel
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

        static::saved(fn ($model) => static::clearAttributeSelectCache($model->attribute_id));
        static::deleted(fn ($model) => static::clearAttributeSelectCache($model->attribute_id));
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'attribute-options'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\AttributeOptionController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\AttributeOptionController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\AttributeOptionController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'attribute-options'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\AttributeOptionController::class, 'index']);
                    Route::get('/select/{attributeId}', [\App\Http\Controllers\PanelControllers\AttributeOptionController::class, 'selectByAttribute']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\AttributeOptionController::class, 'show']);
                });
            })
        ];
    }

    private static function clearCache()
    {
        static::clearLocaleCache([CacheKeys::ATTRIBUTE_OPTIONS_LIST->value]);
    }

    private static function clearAttributeSelectCache(int $attributeId)
    {
        foreach (config('app.supported_locales') as $locale) {
            cache()->forget(CacheKeys::ATTRIBUTE_OPTIONS_SELECT->value . "_$locale" . "_$attributeId");
        }
    }

    public static function newQueryBuilder()
    {
        return new AttributeOptionQueryBuilder();
    }
}
