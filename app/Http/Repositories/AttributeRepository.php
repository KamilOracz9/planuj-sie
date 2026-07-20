<?php

namespace App\Http\Repositories;

use App\Enums\CacheKeys;
use App\Models\Attribute;
use App\Models\AttributeType;
use Illuminate\Support\Facades\Cache;

class AttributeRepository
{
    public static function getAttributesWithType()
    {
        return Cache::remember(CacheKeys::ATTRIBUTES_WITH_TYPE_LIST->value, config('app.cache_lifetime'), function () {
            return Attribute::queryBuilder()
                ->withAttributeType()
                ->select(
                    Attribute::columnName('id'),
                    AttributeType::columnName('code'),
                )
                ->get()
                ->pluck('code', 'id')
                ->toArray();
        });
    }

    public static function getAttributeType(int $attributeId): ?string
    {
        $attributesWithType = self::getAttributesWithType();
        return $attributesWithType[$attributeId] ?? null;
    }
}