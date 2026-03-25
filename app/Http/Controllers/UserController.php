<?php

namespace App\Http\Controllers;

use App\Enums\CacheKeys;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = cache()->remember(
            CacheKeys::USERS_LIST->value,
            config('app.cache_lifetime'),
            fn() => User::all()->toArray()
        );

        return response()->json($users);
    }

    public function show(int $id)
    {
        $user = User::findOrFail($id);

        return response()->json($user);
    }

    public function create(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->query('email'),
            'password' => bcrypt($request->query('password')),
        ]);

        cache()->forget(CacheKeys::USERS_LIST->value);

        return response()->json($user, 201);
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        cache()->forget(CacheKeys::USERS_LIST->value);

        return response()->json($user);
    }
}
