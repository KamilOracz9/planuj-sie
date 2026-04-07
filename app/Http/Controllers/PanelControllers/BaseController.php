<?php

namespace App\Http\Controllers\PanelControllers;

use App\Http\Controllers\Controller;

abstract class BaseController extends Controller
{
    protected string $listCacheKey = '';
    protected string $resourceClass = '';

    protected $model = null;
    protected $modelTranslation = null;

    public function __construct()
    {
        if (empty($this->listCacheKey) || empty($this->resourceClass) || empty($this->requestClass)) {
            throw new \Exception('BaseController properties must be defined in the child class.');
        }
    }

    public function index(string $locale)
    {
        $data = cache()->remember(
            $this->listCacheKey . "_$locale",
            config('app.cache_lifetime'),
            fn() => $this->model::queryBuilder()
                ->when(
                    $this->modelTranslation,
                    fn($query) => $query
                        ->withTranslation($this->modelTranslation::class, $locale, 'id', $this->modelTranslation::FOREIGN_KEY, $this->model::class)
                )
                ->listSelect()
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray()
        );

        return response()->json($data);
    }

    public function show(string $locale, int $id)
    {
        $model = new $this->resourceClass(
            $this->model::queryBuilder()
                ->when(
                    $this->modelTranslation,
                    fn($query) => $query
                        ->withTranslation($this->modelTranslation::class, $locale, 'id', $this->modelTranslation::FOREIGN_KEY, $this->model::class)
                )
                ->where($this->model::columnName('id'), $id)
                ->listSelect()
                ->first()
        );

        return response()->json($model);
    }

    public function destroy(int $id)
    {
        $model = $this->model::findOrFail($id);

        $model->delete();

        return response()->json(['id' => $model->id]);
    }
}
