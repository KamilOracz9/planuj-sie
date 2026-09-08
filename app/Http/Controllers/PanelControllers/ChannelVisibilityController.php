<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Controllers\Controller;
use App\Models\ChannelVisibility;
use App\Traits\HasChannelVisibility;
use Illuminate\Support\Str;
use Kamiloracz9\SwappableCache\Facades\SwappableCache;

class ChannelVisibilityController extends Controller
{
    public function report(string $locale, string $modelType, int $modelId, int $channelId)
    {
        $modelClass = "App\\Models\\" . ucfirst(Str::camel($modelType));

        if (!class_exists($modelClass) || !in_array(HasChannelVisibility::class, class_uses_recursive($modelClass))) {
            return response()->json(['error' => 'Unsupported model type'], 400);
        }

        $model = $modelClass::findOrFail($modelId);

        return response()->json($model->visibilityReport($channelId, $locale));
    }

    public function selectByModel(string $locale, string $modelType, int $modelId)
    {
        $models = SwappableCache::remember(
            key: CacheKeys::CHANNEL_VISIBILITIES_SELECT_BY_MODEL->value . "_$locale" . "_$modelType" . "_$modelId",
            resolve: fn() => ChannelVisibility::queryBuilder()
                ->filterByModel($modelType, $modelId)
                ->listSelect()
                ->get()
                ->map(fn($item) => [
                    'channel_id' => $item->channel_id,
                    'is_enabled' => (bool) $item->is_enabled,
                ])
                ->toArray(),
            fingerprint: fn() => ChannelVisibility::where('model_type', $modelType)->where('model_id', $modelId)->max('updated_at'),
            checkInterval: 60,
        );

        return response()->json($models);
    }
}
