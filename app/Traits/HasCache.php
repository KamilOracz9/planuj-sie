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
}
