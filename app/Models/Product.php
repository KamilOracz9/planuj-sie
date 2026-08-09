<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\ProductQueryBuilder;
use App\Traits\HasAttributes;
use App\Traits\HasCache;
use App\Traits\HasChannelVisibility;
use App\Traits\HasCollections;
use App\Traits\HasPrices;
use App\Traits\HasTranslations;
use App\Traits\Media\HasMediaCollections;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;
use Spatie\MediaLibrary\HasMedia;

#[Fillable(['id', 'brand_id', 'series_id'])]
class Product extends BaseModel implements HasMedia
{
    use HasTranslations, HasCache, Sluggable, HasAttributes, HasCollections, HasChannelVisibility, HasPrices, HasMediaCollections;

    public array $translatable = ['name', 'slug', 'description', 'short_description'];
    public string $sluggable = 'name';

    protected static function boot()
    {
        parent::boot();

        static::bootSluggable();
        static::bootTranslations();
        static::bootAttributes();
        static::bootCollections();
        static::bootChannelVisibility();
        static::bootPrices();
        static::bootCache();
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function ancestorGroupsForVisibility(): array
    {
        $groups = [];

        if ($this->brand_id) {
            $groups[] = [$this->brand];
        }

        if ($this->series_id) {
            $groups[] = [$this->series];
        }

        if ($this->collections->isNotEmpty()) {
            $groups[] = $this->collections->all();
        }

        return $groups;
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'products'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\ProductController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\ProductController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\ProductController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'products'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\ProductController::class, 'index']);
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\ProductController::class, 'select']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\ProductController::class, 'show']);
                });
            }),
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::PRODUCTS_LIST->value, CacheKeys::PRODUCTS_SELECT->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::PRODUCTS_LIST->value], $model->id);
        }
    }

    public static function newQueryBuilder()
    {
        return new ProductQueryBuilder();
    }
}
