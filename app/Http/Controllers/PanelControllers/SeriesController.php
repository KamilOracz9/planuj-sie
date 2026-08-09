<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\SeriesRequest;
use App\Http\Resources\SeriesResource;
use App\Models\Series;
use App\Models\Translations\SeriesTranslation;

class SeriesController extends BaseController
{
    protected string $listCacheKey = CacheKeys::SERIES_LIST->value;
    protected string $selectCacheKey = CacheKeys::SERIES_SELECT->value;
    protected string $resourceClass = SeriesResource::class;

    protected mixed $model;
    protected mixed $modelTranslation;

    public function __construct()
    {
        $this->model = new Series;
        $this->modelTranslation = new SeriesTranslation;
    }

    public function update(SeriesRequest $request, int $id)
    {
        $model = Series::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(SeriesRequest $request)
    {
        $model = new Series($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }
}
