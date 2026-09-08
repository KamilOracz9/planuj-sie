<?php

namespace App\Http\Repositories;

use App\Enums\CacheKeys;
use App\Models\Attribute;
use App\Models\AttributeType;
use Kamiloracz9\SwappableCache\Facades\SwappableCache;

class AttributeRepository
{
    public static function getAttributesWithType()
    {
        return SwappableCache::remember(
            key: CacheKeys::ATTRIBUTES_WITH_TYPE_LIST->value,
            resolve: fn () => Attribute::queryBuilder()
                ->withAttributeType()
                ->select(
                    Attribute::columnName('id'),
                    AttributeType::columnName('code'),
                )
                ->get()
                ->pluck('code', 'id')
                ->toArray(),
            fingerprint: fn () => Attribute::max('updated_at'),
            checkInterval: 60,
        );
    }

    public static function getAttributeType(int $attributeId): ?string
    {
        $attributesWithType = self::getAttributesWithType();
        return $attributesWithType[$attributeId] ?? null;
    }
}