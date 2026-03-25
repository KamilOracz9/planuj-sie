<?php

namespace App\Http\Controllers;

use App\Enums\CacheKeys;
use App\Models\Channel;
use Illuminate\Http\Request;
use App\Http\Resources\ChannelResource;

class ChannelController extends Controller
{
    public function index(string $locale)
    {
        app()->setLocale($locale);

        $channels = cache()->remember(
            CacheKeys::CHANNELS_LIST->value,
            config('app.cache_lifetime'),
            fn() => Channel::select(...Channel::LIST_FIELDS)
                ->get()
                ->toResourceCollection(ChannelResource::class)
                ->toArray(request())
        );

        return response()->json($channels);
    }

    public function show(string $locale, int $id)
    {
        app()->setLocale($locale);

        $channel = new ChannelResource(Channel::findOrFail($id));

        return response()->json($channel);
    }

    public function update(Request $request, int $id)
    {
        $channel = Channel::findOrFail($id);

        $channel->fill($request->query());

        $channel->save();

        return response()->json(new ChannelResource($channel));
    }

    public function create(Request $request)
    {
        $channel = new Channel($request->query());

        $channel->save();

        return response()->json(new ChannelResource($channel), 201);
    }

    public function destroy(int $id)
    {
        $channel = Channel::findOrFail($id);

        $channel->delete();

        return response()->json(new ChannelResource($channel));
    }
}
