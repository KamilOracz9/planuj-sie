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
}
