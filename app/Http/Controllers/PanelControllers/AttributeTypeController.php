<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Resources\AttributeTypeResource;
use App\Models\AttributeType;
use App\Models\Translations\AttributeTypeTranslation;
use Kamiloracz9\SwappableCache\Facades\SwappableCache;

class AttributeTypeController extends BaseController
{
    protected string $listCacheKey = CacheKeys::ATTRIBUTE_TYPES_LIST->value;
    protected string $selectCacheKey = CacheKeys::ATTRIBUTE_TYPES_SELECT->value;
    protected string $resourceClass = AttributeTypeResource::class;

    protected mixed $model;
    protected mixed $modelTranslation;

    public function __construct()
    {
        $this->model = new AttributeType;
        $this->modelTranslation = new AttributeTypeTranslation;
    }

    public function select(string $locale)
    {
        $models = SwappableCache::remember(
            key: $this->selectCacheKey . "_$locale",
            resolve: fn() => AttributeType::queryBuilder()
                ->withTranslation(AttributeTypeTranslation::class, $locale, 'id', AttributeTypeTranslation::FOREIGN_KEY, AttributeType::class)
                ->select(
                    AttributeType::columnName('id'),
                    AttributeTypeTranslation::columnName('name'),
                    AttributeType::columnName('code'),
                )
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray(),
            fingerprint: fn() => AttributeType::max('updated_at'),
            checkInterval: 60,
        );

        return response()->json($models);
    }
}
