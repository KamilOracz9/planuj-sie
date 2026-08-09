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
        // BaseController::index() suffixes its list cache key with
        // "_channel_{id}" when the panel's global channel switcher is active
        // (see BaseQueryBuilder::filterByChannel) - every list-cache key
        // passed here could be one of those, so every existing channel's
        // variant needs invalidating too, alongside the unscoped ("all
        // channels") key. Harmless no-op for cache keys that were never
        // written with a channel suffix (e.g. *_SELECT keys).
        $channelIds = \App\Models\Channel::query()->pluck('id')->all();

        foreach (config('app.supported_locales') as $locale) {
            foreach ($cacheKeys as $key) {
                cache()->forget($key . "_$locale");

                foreach ($channelIds as $channelId) {
                    cache()->forget($key . "_$locale" . "_channel_$channelId");
                }
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
