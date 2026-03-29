<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Translations\CategoryTranslation;

class CategoryController extends BaseController
{
    protected string $listCacheKey = CacheKeys::CATEGORIES_LIST->value;
    protected string $resourceClass = CategoryResource::class;

    protected $model;
    protected $modelTranslation;

    public function __construct()
    {
        $this->model = new Category;
        $this->modelTranslation = new CategoryTranslation;
    }

    public function update(CategoryRequest $request, int $id)
    {
        $model = Category::findOrFail($id);

        $model->update($request->query());

        return response()->json(['id' => $model->id]);
    }

    public function create(CategoryRequest $request)
    {
        $model = new Category($request->query());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }
}
