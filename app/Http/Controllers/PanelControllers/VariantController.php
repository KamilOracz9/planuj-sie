<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\VariantRequest;
use App\Http\Resources\VariantResource;
use App\Models\Variant;
use App\Models\Translations\VariantTranslation;

class VariantController extends BaseController
{
    protected string $listCacheKey = CacheKeys::VARIANTS_LIST->value;
    protected string $resourceClass = VariantResource::class;

    protected mixed $model;
    protected mixed $modelTranslation;

    public function __construct()
    {
        $this->model = new Variant;
        $this->modelTranslation = new VariantTranslation;
    }

    public function update(VariantRequest $request, int $id)
    {
        $model = Variant::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(VariantRequest $request)
    {
        $model = new Variant($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }
}
