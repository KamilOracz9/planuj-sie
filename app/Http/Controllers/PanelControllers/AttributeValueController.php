<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\AttributeValueRequest;
use App\Http\Resources\AttributeValueResource;
use App\Models\AttributeValue;

class AttributeValueController extends BaseController
{
    protected string $listCacheKey = CacheKeys::ATTRIBUTE_VALUES_LIST->value;
    protected string $resourceClass = AttributeValueResource::class;

    protected mixed $model;

    public function __construct()
    {
        $this->model = new AttributeValue;
    }

    public function update(AttributeValueRequest $request, int $id)
    {
        $model = AttributeValue::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(AttributeValueRequest $request)
    {
        $model = new AttributeValue($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }
}
