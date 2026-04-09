<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
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

    public function update(UpdateUserRequest $request, int $id)
    {
        $model = User::findOrFail($id);

        $model->update($request->validated());

        return response()->json(['id' => $model->id]);
    }

    public function create(CreateUserRequest $request)
    {
        $model = new User($request->validated());

        $model->save();

        return response()->json(['id' => $model->id], 201);
    }
}
