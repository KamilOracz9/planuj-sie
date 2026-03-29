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
    protected string $resourceClass = ChannelResource::class;

    protected $model;
    protected $modelTranslation;

    public function __construct()
    {
        $this->model = new Channel;
        $this->modelTranslation = new ChannelTranslation();
    }

    public function update(ChannelRequest $request, int $id)
    {
        $model = Channel::findOrFail($id);

        $model->update($request->query());

        return response()->json(['id' => $model->id]);
    }

    public function create(ChannelRequest $request)
    {
        $model = new Channel($request->query());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }
}
