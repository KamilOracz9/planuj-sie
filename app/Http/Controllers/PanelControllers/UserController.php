<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

class UserController extends BaseController
{
    protected string $listCacheKey = CacheKeys::USERS_LIST->value;
    protected string $resourceClass = UserResource::class;

    protected $model;
    protected $modelTranslation;

    public function __construct()
    {
        $this->model = new User;
        $this->modelTranslation = null;
    }

    public function update(UserRequest $request, int $id)
    {
        $model = User::findOrFail($id);

        $model->update($request->query());

        return response()->json(['id' => $model->id]);
    }

    public function create(UserRequest $request)
    {
        $model = new User($request->query());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }
}
