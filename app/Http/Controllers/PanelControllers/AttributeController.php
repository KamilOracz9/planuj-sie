<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\AttributeRequest;
use App\Http\Resources\AttributeResource;
use App\Models\Attribute;
use App\Models\AttributeType;
use App\Models\Translations\AttributeTranslation;

class AttributeController extends BaseController
{
    protected string $listCacheKey = CacheKeys::ATTRIBUTES_LIST->value;
    protected string $selectCacheKey = CacheKeys::ATTRIBUTES_SELECT->value;
    protected string $resourceClass = AttributeResource::class;

    protected mixed $model;
    protected mixed $modelTranslation;

    public function __construct()
    {
        $this->model = new Attribute;
        $this->modelTranslation = new AttributeTranslation;
    }

    public function select(string $locale)
    {
        $models = cache()->remember(
            $this->selectCacheKey . "_$locale",
            config('app.cache_lifetime'),
            fn() => Attribute::queryBuilder()
                ->withTranslation(AttributeTranslation::class, $locale, 'id', AttributeTranslation::FOREIGN_KEY, Attribute::class)
                ->withAttributeType()
                ->select(
                    Attribute::columnName('id'),
                    AttributeTranslation::columnName('name'),
                    Attribute::columnName('attribute_type_id'),
                    AttributeType::columnName('code') . ' as attribute_type_code',
                )
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray()
        );

        return response()->json($models);
    }

    public function update(AttributeRequest $request, int $id)
    {
        $model = Attribute::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(AttributeRequest $request)
    {
        $model = new Attribute($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }
}
