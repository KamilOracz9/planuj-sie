<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use App\Models\Translations\BrandTranslation;

class BrandController extends BaseController
{
    protected string $listCacheKey = CacheKeys::BRANDS_LIST->value;
    protected string $resourceClass = BrandResource::class;

    protected $model;
    protected $modelTranslation;

    public function __construct()
    {
        $this->model = new Brand;
        $this->modelTranslation = new BrandTranslation;
    }

    public function update(BrandRequest $request, int $id)
    {
        $model = Brand::findOrFail($id);

        $model->update($request->query());

        return response()->json(['id' => $model->id]);
    }

    public function create(BrandRequest $request)
    {
        $model = new Brand($request->query());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }
}
