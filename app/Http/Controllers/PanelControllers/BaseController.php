<?php

namespace App\Http\Controllers\PanelControllers;

use App\Http\Controllers\Controller;
use App\Http\Repositories\ModelListRepository;

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

        $models = ModelListRepository::select($this->model::class, $this->modelTranslation::class, $this->selectCacheKey, $locale);

        return response()->json($models);
    }

    public function index(string $locale)
    {
        // Optional global "active channel" scope (see panel's ChannelSwitcher) -
        // filterByChannel() is a safe no-op for models without channel
        // visibility, so this is always applied rather than conditioned on
        // which controller this is.
        $channelId = request()->integer('channel_id') ?: null;

        $data = ModelListRepository::list(
            $this->model::class,
            $this->modelTranslation ? $this->modelTranslation::class : null,
            $this->listCacheKey,
            $locale,
            $channelId,
        );

        return response()->json($data);
    }

    public function show(string $locale, int $id)
    {
        $model = ModelListRepository::show(
            $this->model::class,
            $this->modelTranslation ? $this->modelTranslation::class : null,
            $this->listCacheKey,
            $locale,
            $id,
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
