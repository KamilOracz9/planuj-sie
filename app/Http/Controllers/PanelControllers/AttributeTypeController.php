<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Resources\AttributeTypeResource;
use App\Models\AttributeType;
use App\Models\Translations\AttributeTypeTranslation;

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
}
