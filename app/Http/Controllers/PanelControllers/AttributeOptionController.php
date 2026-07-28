<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\AttributeOptionRequest;
use App\Http\Resources\AttributeOptionResource;
use App\Models\AttributeOption;
use App\Models\Translations\AttributeOptionTranslation;

class AttributeOptionController extends BaseController
{
    protected string $listCacheKey = CacheKeys::ATTRIBUTE_OPTIONS_LIST->value;
    protected string $selectCacheKey = CacheKeys::ATTRIBUTE_OPTIONS_SELECT->value;
    protected string $resourceClass = AttributeOptionResource::class;

    protected mixed $model;
    protected mixed $modelTranslation;

    public function __construct()
    {
        $this->model = new AttributeOption;
        $this->modelTranslation = new AttributeOptionTranslation;
    }

    public function update(AttributeOptionRequest $request, int $id)
    {
        $model = AttributeOption::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(AttributeOptionRequest $request)
    {
        $model = new AttributeOption($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }

    public function selectByAttribute(string $locale, int $attributeId)
    {
        $models = cache()->remember(
            $this->selectCacheKey . "_$locale" . "_$attributeId",
            config('app.cache_lifetime'),
            fn() => AttributeOption::queryBuilder()
                ->withTranslation(AttributeOptionTranslation::class, $locale, 'id', AttributeOptionTranslation::FOREIGN_KEY, AttributeOption::class)
                ->filterByAttribute($attributeId)
                ->orderByOrderColumn()
                ->select(AttributeOption::columnName('id'), AttributeOptionTranslation::columnName('name'))
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray()
        );

        return response()->json($models);
    }
}
