<?php

namespace App\Http\Repositories;

use Illuminate\Support\Facades\DB;
use Kamiloracz9\SwappableCache\Facades\SwappableCache;

/**
 * Generic list/select/show data-fetching for every PanelControllers\*Controller
 * extending BaseController - they all share the exact same query+cache shape,
 * parameterized by model/translation class and cache key. Per the
 * project-architecture and caching-strategy skills: this is the one place
 * these queries actually execute, and every result is cached via
 * swappable-cache instead of a raw Cache::remember()/cache()->remember().
 */
class ModelListRepository
{
    public static function list(string $modelClass, ?string $translationClass, string $cacheKey, string $locale, ?int $channelId): array
    {
        $suffix = $locale.($channelId ? "_channel_{$channelId}" : '');

        return SwappableCache::remember(
            key: "{$cacheKey}_{$suffix}",
            resolve: fn () => $modelClass::queryBuilder()
                ->when(
                    $translationClass,
                    fn ($query) => $query->withTranslation($translationClass, $locale, 'id', $translationClass::FOREIGN_KEY, $modelClass)
                )
                ->filterByChannel($channelId)
                ->listExtended($locale)
                ->listSelect()
                ->get()
                ->map(fn ($item) => (array) $item)
                ->toArray(),
            fingerprint: fn () => $modelClass::max('updated_at'),
            checkInterval: 60,
        );
    }

    public static function select(string $modelClass, string $translationClass, string $cacheKey, string $locale): array
    {
        return SwappableCache::remember(
            key: "{$cacheKey}_{$locale}",
            resolve: fn () => $modelClass::queryBuilder()
                ->withTranslation($translationClass, $locale, 'id', $translationClass::FOREIGN_KEY, $modelClass)
                ->select($modelClass::columnName('id'), $translationClass::columnName('name'))
                ->get()
                ->map(fn ($item) => (array) $item)
                ->toArray(),
            fingerprint: fn () => $modelClass::max('updated_at'),
            checkInterval: 60,
        );
    }

    public static function show(string $modelClass, ?string $translationClass, string $cacheKey, string $locale, int $id): ?array
    {
        return SwappableCache::remember(
            key: "{$cacheKey}_show_{$locale}_{$id}",
            resolve: function () use ($modelClass, $translationClass, $id) {
                $model = $modelClass::queryBuilder()
                    ->where($modelClass::columnName('id'), $id)
                    ->first();

                if (! $model) {
                    return null;
                }

                $model = (array) $model;

                if ($translationClass) {
                    $model['translations'] = DB::table($translationClass::tableName())
                        ->where($translationClass::columnName($translationClass::FOREIGN_KEY), $id)
                        ->get()
                        ->keyBy('locale')
                        ->map(fn ($item) => (array) $item)
                        ->toArray();
                }

                return $model;
            },
            fingerprint: fn () => $modelClass::where($modelClass::columnName('id'), $id)->value('updated_at'),
            checkInterval: 60,
        );
    }
}
