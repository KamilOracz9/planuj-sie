<?php

namespace App\Models;

use App\Enums\CacheKeys;
use App\QueryBuilders\ChannelQueryBuilder;
use App\Traits\HasCache;
use App\Traits\HasTranslations;
use App\Traits\Sluggable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;

#[Fillable(['id', 'is_default'])]
class Channel extends BaseModel
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

        // Enforces exactly one default channel: a mass update (not a model
        // save), so it doesn't re-fire this same saved() event.
        static::saved(function ($channel) {
            if ($channel->is_default) {
                static::query()->where('id', '!=', $channel->id)->update(['is_default' => false]);
            }
        });
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'channels'], function () {
                Route::put('/{id}', [\App\Http\Controllers\PanelControllers\ChannelController::class, 'update']);
                Route::post('/', [\App\Http\Controllers\PanelControllers\ChannelController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\PanelControllers\ChannelController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'channels'], function () {
                    Route::get('/', [\App\Http\Controllers\PanelControllers\ChannelController::class, 'index']);
                    Route::get('/select', [\App\Http\Controllers\PanelControllers\ChannelController::class, 'select']);
                    Route::get('/{id}', [\App\Http\Controllers\PanelControllers\ChannelController::class, 'show']);
                });
            })
        ];
    }

    private static function clearCache($model = null)
    {
        static::clearLocaleCache([CacheKeys::CHANNELS_LIST->value, CacheKeys::CHANNELS_SELECT->value]);

        if ($model) {
            static::clearShowCache([CacheKeys::CHANNELS_LIST->value], $model->id);
        }
    }

    public static function newQueryBuilder()
    {
        return new ChannelQueryBuilder();
    }
}
