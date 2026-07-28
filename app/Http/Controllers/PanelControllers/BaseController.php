<?php

namespace App\Http\Controllers\PanelControllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

abstract class BaseController extends Controller
{
    protected string $listCacheKey = '';
    protected string $selectCacheKey = '';
    protected string $resourceClass = '';

    protected mixed $model = null;
    protected mixed $modelTranslation = null;

    public function __construct()
    {
        if (empty($this->listCacheKey) || empty($this->resourceClass) || empty($this->requestClass)) {
            throw new \Exception('BaseController properties must be defined in the child class.');
        }
    }

    public function select(string $locale)
    {
        if (!$this->selectCacheKey) {
            return response()->json(['error' => 'Select cache key not defined.'], 500);
        }

        $models = cache()->remember(
            $this->selectCacheKey . "_$locale",
            config('app.cache_lifetime'),
            fn() => $this->model::queryBuilder()
                ->withTranslation($this->modelTranslation::class, $locale, 'id', $this->modelTranslation::FOREIGN_KEY, $this->model::class)
                ->select($this->model::columnName('id'), $this->modelTranslation::columnName('name'))
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray()
        );

        return response()->json($models);
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
                ->listExtended($locale)
                ->listSelect()
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray()
        );

        return response()->json($data);
    }

    public function show(string $locale, int $id)
    {
        $model = cache()->remember(
            "{$this->listCacheKey}_show_{$locale}_{$id}",
            config('app.cache_lifetime'),
            function () use ($id) {
                $model = $this->model::queryBuilder()
                    ->where($this->model::columnName('id'), $id)
                    ->first();

                if (!$model) {
                    return null;
                }

                // Cache values are unserialized with `allowed_classes: false` (see
                // config/cache.php), so only plain arrays/scalars survive the round-trip —
                // objects come back as `__PHP_Incomplete_Class`. Cast to array like
                // index()/select() already do before handing anything to the cache.
                $model = (array) $model;

                if ($this->modelTranslation) $model['translations'] = DB::table($this->modelTranslation::tableName())
                    ->where($this->modelTranslation::columnName($this->modelTranslation::FOREIGN_KEY), $id)
                    ->get()
                    ->keyBy('locale')
                    ->map(fn($item) => (array) $item)
                    ->toArray();

                return $model;
            }
        );

        if (!$model) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($model);
    }

    public function destroy(int $id)
    {
        $model = $this->model::findOrFail($id);

        $model->delete();

        return response()->json(['id' => $model->id]);
    }
}
