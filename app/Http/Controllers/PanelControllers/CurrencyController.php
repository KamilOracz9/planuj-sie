<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\CurrencyRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;

class CurrencyController extends BaseController
{
    protected string $listCacheKey = CacheKeys::CURRENCIES_LIST->value;
    protected string $selectCacheKey = CacheKeys::CURRENCIES_SELECT->value;
    protected string $resourceClass = CurrencyResource::class;

    protected mixed $model;
    protected mixed $modelTranslation = null;

    public function __construct()
    {
        $this->model = new Currency;
    }

    public function update(CurrencyRequest $request, int $id)
    {
        $model = Currency::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(CurrencyRequest $request)
    {
        $model = new Currency($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }

    // Currency has no modelTranslation, but BaseController::select() calls
    // $this->modelTranslation::class unconditionally (unlike index()/show(),
    // which guard with when()/if()) — must override rather than inherit.
    public function select(string $locale)
    {
        $models = cache()->remember(
            $this->selectCacheKey . "_$locale",
            config('app.cache_lifetime'),
            fn() => Currency::queryBuilder()
                ->listSelect()
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray()
        );

        return response()->json($models);
    }
}
