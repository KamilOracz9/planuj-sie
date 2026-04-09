<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\AttributeRequest;
use App\Http\Resources\AttributeResource;
use App\Models\Attribute;
use App\Models\Translations\AttributeTranslation;

class AttributeController extends BaseController
{
    protected string $listCacheKey = CacheKeys::ATTRIBUTES_LIST->value;
    protected string $resourceClass = AttributeResource::class;

    protected $model;
    protected $modelTranslation;

    public function __construct()
    {
        $this->model = new Attribute;
        $this->modelTranslation = new AttributeTranslation;
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
