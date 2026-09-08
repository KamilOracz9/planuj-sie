<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Repositories\ProductRepository;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Translations\ProductTranslation;
use App\Services\ProductService;

class ProductController extends BaseController
{
    protected string $listCacheKey = CacheKeys::PRODUCTS_LIST->value;
    protected string $selectCacheKey = CacheKeys::PRODUCTS_SELECT->value;
    protected string $resourceClass = ProductResource::class;

    protected mixed $model;
    protected mixed $modelTranslation;

    public function __construct()
    {
        $this->model = new Product;
        $this->modelTranslation = new ProductTranslation;
    }

    public function update(ProductRequest $request, int $id)
    {
        $model = Product::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(ProductRequest $request)
    {
        $model = new Product($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }

    public function show(string $locale, int $id)
    {
        $response = parent::show($locale, $id);
        $data = $response->getData(true);

        if (isset($data['id'])) {
            $data['collection_ids'] = ProductRepository::collectionIds($data['id']);
        }

        return response()->json($data);
    }

    public function priceBreakdown(string $locale, int $id)
    {
        $channelId = request()->integer('channel_id') ?: null;

        return response()->json(ProductService::priceBreakdown($locale, $id, $channelId));
    }
}
