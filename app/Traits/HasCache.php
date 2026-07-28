<?php

namespace App\Traits;

trait HasCache
{
    protected static function bootCache()
    {
        static::saved(function ($model) {
            static::clearCache($model);
        });

        static::deleted(function ($model) {
            static::clearCache($model);
        });
    }

    protected static function clearLocaleCache(array $cacheKeys)
    {
        foreach (config('app.supported_locales') as $locale) {
            foreach ($cacheKeys as $key) {
                cache()->forget($key . "_$locale");
            }
        }
    }

    protected static function clearShowCache(array $cacheKeys, int|string $id)
    {
        foreach (config('app.supported_locales') as $locale) {
            foreach ($cacheKeys as $key) {
                cache()->forget($key . "_show_{$locale}_{$id}");
            }
        }
    }
}
