<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Controllers\Controller;
use App\Models\ChannelVisibility;

class ChannelVisibilityController extends Controller
{
    public function selectByModel(string $locale, string $modelType, int $modelId)
    {
        $models = cache()->remember(
            CacheKeys::CHANNEL_VISIBILITIES_SELECT_BY_MODEL->value . "_$locale" . "_$modelType" . "_$modelId",
            config('app.cache_lifetime'),
            fn() => ChannelVisibility::queryBuilder()
                ->filterByModel($modelType, $modelId)
                ->listSelect()
                ->get()
                ->map(fn($item) => [
                    'channel_id' => $item->channel_id,
                    'is_enabled' => (bool) $item->is_enabled,
                ])
                ->toArray()
        );

        return response()->json($models);
    }
}
