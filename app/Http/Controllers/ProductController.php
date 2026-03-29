<?php

namespace App\Http\Controllers;

use App\Enums\CacheKeys;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Translations\ProductTranslation;

class ProductController extends BaseController
{
    protected string $listCacheKey = CacheKeys::PRODUCTS_LIST->value;
    protected string $resourceClass = ProductResource::class;

    protected $model;
    protected $modelTranslation;

    public function __construct()
    {
        $this->model = new Product;
        $this->modelTranslation = new ProductTranslation;
    }

    public function update(ProductRequest $request, int $id)
    {
        $model = Product::findOrFail($id);

        $model->update($request->query());

        return response()->json(['id' => $model->id]);
    }

    public function create(ProductRequest $request)
    {
        $model = new Product($request->query());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }
}
