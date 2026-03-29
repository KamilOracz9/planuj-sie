<?php

namespace App\Traits;

trait HasCache
{
    protected static function bootCache()
    {
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
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
}
