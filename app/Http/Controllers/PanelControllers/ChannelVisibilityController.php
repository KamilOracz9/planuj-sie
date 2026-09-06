<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Controllers\Controller;
use App\Models\ChannelVisibility;
use App\Traits\HasChannelVisibility;
use Illuminate\Support\Str;

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
