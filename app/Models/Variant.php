<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\VariantQueryBuilder;
use App\Traits\HasAttributes;
use App\Traits\HasCache;
use App\Traits\HasChannelVisibility;
use App\Traits\HasPrices;
use App\Traits\HasTranslations;
use App\Traits\Media\HasMediaCollections;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\HasMedia;

#[Fillable(['id', 'product_id'])]
class Variant extends BaseModel implements HasMedia
{
    use HasTranslations, HasCache, Sluggable, HasAttributes, HasChannelVisibility, HasPrices, HasMediaCollections;

    public array $translatable = ['name', 'slug', 'description', 'short_description'];
    public string $sluggable = 'name';

    protected static function boot()
    {
        parent::boot();

        static::bootSluggable();
        static::bootTranslations();
        static::bootAttributes();
        static::bootChannelVisibility();
        static::bootPrices();
        static::bootCache();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function ancestorGroupsForVisibility(): array
    {
        return [[$this->product]];
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'variants'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\VariantController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\VariantController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\VariantController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'variants'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\VariantController::class, 'index']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\VariantController::class, 'show']);
                });
            }),
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::VARIANTS_LIST->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::VARIANTS_LIST->value], $model->id);
        }
    }

    public static function newQueryBuilder()
    {
        return new VariantQueryBuilder();
    }
}
