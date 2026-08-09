<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\MediaCollectionRequest;
use App\Http\Resources\MediaCollectionResource;
use App\Models\MediaCollection;
use App\Models\MediaCollectionAssignment;
use App\Models\MediaCollectionConversion;

class MediaCollectionController extends BaseController
{
    protected string $listCacheKey = CacheKeys::MEDIA_COLLECTIONS_LIST->value;
    protected string $selectCacheKey = CacheKeys::MEDIA_COLLECTIONS_SELECT->value;
    protected string $resourceClass = MediaCollectionResource::class;

    protected mixed $model;
    protected mixed $modelTranslation = null;

    public function __construct()
    {
        $this->model = new MediaCollection;
    }

    public function update(MediaCollectionRequest $request, int $id)
    {
        $model = MediaCollection::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(MediaCollectionRequest $request)
    {
        $model = new MediaCollection($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }

    // MediaCollection has no modelTranslation, but BaseController::select()
    // calls $this->modelTranslation::class unconditionally (unlike
    // index()/show(), which guard with when()/if()) - same gotcha as
    // CurrencyController.
    public function select(string $locale)
    {
        $models = cache()->remember(
            $this->selectCacheKey . "_$locale",
            config('app.cache_lifetime'),
            fn() => MediaCollection::queryBuilder()
                ->listSelect()
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray()
        );

        return response()->json($models);
    }

    // Overridden (rather than relying on BaseController::show()) to embed
    // conversions in the response, the same way BaseController::show()
    // itself embeds `translations` for translatable models - conversions
    // belong directly to this MediaCollection, not to some other model
    // fetched separately (unlike Price/ChannelVisibility, which are always
    // attached to a *different* owning entity via their own select-by-model
    // endpoint).
    public function show(string $locale, int $id)
    {
        $model = cache()->remember(
            "{$this->listCacheKey}_show_{$locale}_{$id}",
            config('app.cache_lifetime'),
            function () use ($id) {
                $model = MediaCollection::query()->find($id);

                if (!$model) {
                    return null;
                }

                $model = $model->toArray();

                $model['conversions'] = MediaCollectionConversion::query()
                    ->where('media_collection_id', $id)
                    ->get(['channel_id', 'name', 'width', 'height', 'fit'])
                    ->toArray();

                $model['assignments'] = MediaCollectionAssignment::query()
                    ->where('media_collection_id', $id)
                    ->get(['channel_id', 'model_type'])
                    ->toArray();

                return $model;
            }
        );

        if (!$model) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($model);
    }
}
