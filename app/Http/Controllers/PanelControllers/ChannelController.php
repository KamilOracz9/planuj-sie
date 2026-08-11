<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\ChannelRequest;
use App\Models\Channel;
use App\Http\Resources\ChannelResource;
use App\Models\Translations\ChannelTranslation;

class ChannelController extends BaseController
{
    protected string $listCacheKey = CacheKeys::CHANNELS_LIST->value;
    protected string $selectCacheKey = CacheKeys::CHANNELS_SELECT->value;
    protected string $resourceClass = ChannelResource::class;

    protected mixed $model;
    protected mixed $modelTranslation;

    public function __construct()
    {
        $this->model = new Channel;
        $this->modelTranslation = new ChannelTranslation();
    }

    public function update(ChannelRequest $request, int $id)
    {
        $model = Channel::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(ChannelRequest $request)
    {
        $model = new Channel($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }

    // Overridden (not inherited from BaseController::select(), which
    // hardcodes {id,name} only) so channelsSelect carries is_default too -
    // needed by the panel's global ChannelSwitcher to pre-select the default
    // channel before any cookie has been set.
    public function select(string $locale)
    {
        $models = cache()->remember(
            $this->selectCacheKey . "_$locale",
            config('app.cache_lifetime'),
            fn() => Channel::queryBuilder()
                ->withTranslation($this->modelTranslation::class, $locale, 'id', $this->modelTranslation::FOREIGN_KEY, Channel::class)
                ->select(Channel::columnName('id'), Channel::columnName('is_default'), $this->modelTranslation::columnName('name'))
                ->get()
                ->map(fn($item) => (array) $item)
                ->toArray()
        );

        return response()->json($models);
    }
}
