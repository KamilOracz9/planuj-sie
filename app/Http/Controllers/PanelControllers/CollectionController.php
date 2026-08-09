<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\CollectionRequest;
use App\Http\Resources\CollectionResource;
use App\Models\Collection;
use App\Models\Translations\CollectionTranslation;

class CollectionController extends BaseController
{
    protected string $listCacheKey = CacheKeys::COLLECTIONS_LIST->value;
    protected string $selectCacheKey = CacheKeys::COLLECTIONS_SELECT->value;
    protected string $resourceClass = CollectionResource::class;

    protected mixed $model;
    protected mixed $modelTranslation;

    public function __construct()
    {
        $this->model = new Collection;
        $this->modelTranslation = new CollectionTranslation;
    }

    public function update(CollectionRequest $request, int $id)
    {
        $model = Collection::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(CollectionRequest $request)
    {
        $model = new Collection($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }
}
