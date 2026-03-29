<?php

namespace App\Http\Controllers;

use App\Enums\CacheKeys;
use App\Http\Requests\LocaleRequest;
use App\Http\Resources\LocaleResource;
use App\Models\Locale;
use App\Models\Translations\LocaleTranslation;

class LocaleController extends BaseController
{
    protected string $listCacheKey = CacheKeys::LOCALES_LIST->value;
    protected string $resourceClass = LocaleResource::class;

    protected $model;
    protected $modelTranslation;

    public function __construct()
    {
        $this->model = new Locale;
        $this->modelTranslation = new LocaleTranslation;
    }

    public function update(LocaleRequest $request, int $id)
    {
        $model = Locale::findOrFail($id);

        $model->update($request->query());

        return response()->json(['id' => $model->id]);
    }

    public function create(LocaleRequest $request)
    {
        $model = new Locale($request->query());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }
}
