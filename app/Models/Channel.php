<?php

namespace App\Models;

use App\Enums\CacheKeys;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

#[Fillable(['name', 'slug'])]
class Channel extends BaseModel
{
    use HasTranslations;

    const LIST_FIELDS = ['id', 'name', 'slug'];

    protected array $translatable = ['name', 'slug'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($channel) {
            $channel->setTranslations('slug', array_map(fn($item) => Str::slug($item), $channel->getTranslations('name')));

            cache()->forget(CacheKeys::CHANNELS_LIST->value);
        });

        static::updating(function ($channel) {
            $channel->setTranslations('slug', array_map(fn($item) => Str::slug($item), $channel->getTranslations('name')));

            cache()->forget(CacheKeys::CHANNELS_LIST->value);
        });

        static::deleting(function () {
            cache()->forget(CacheKeys::CHANNELS_LIST->value);
        });
    }

    public static function routes()
    {
        return [
            Route::group(['prefix' => 'channels'], function () {
                Route::put('/{id}', [\App\Http\Controllers\ChannelController::class, 'update']);
                Route::post('/create', [\App\Http\Controllers\ChannelController::class, 'create']);
                Route::delete('/{id}', [\App\Http\Controllers\ChannelController::class, 'destroy']);
            }),
            Route::group(['prefix' => '{locale}'], function () {
                Route::group(['prefix' => 'channels'], function () {
                    Route::get('/', [\App\Http\Controllers\ChannelController::class, 'index']);
                    Route::get('/{id}', [\App\Http\Controllers\ChannelController::class, 'show']);
                });
            })
        ];
    }
}
