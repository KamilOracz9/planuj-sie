<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Price;
use Kamiloracz9\SwappableCache\Facades\SwappableCache;

class PriceController extends Controller
{
    public function selectByModel(string $locale, string $modelType, int $modelId)
    {
        $models = SwappableCache::remember(
            key: CacheKeys::PRICES_SELECT_BY_MODEL->value . "_$locale" . "_$modelType" . "_$modelId",
            resolve: function () use ($modelType, $modelId) {
                $rows = Price::queryBuilder()
                    ->filterByModel($modelType, $modelId)
                    ->listSelect()
                    ->get();

                // `amount` is stored as an integer in the currency's smallest
                // unit (see Currency::toMinorUnits/toMajorUnits) - converted
                // back to major units here so the panel always deals in the
                // units an admin actually types (e.g. "100", not "10000").
                $currencies = Currency::query()
                    ->whereIn('id', $rows->pluck('currency_id')->unique())
                    ->get()
                    ->keyBy('id');

                return $rows->map(fn($item) => [
                    'channel_id' => $item->channel_id,
                    'currency_id' => $item->currency_id,
                    'amount' => $currencies->get($item->currency_id)?->toMajorUnits((int) $item->amount) ?? $item->amount,
                ])->toArray();
            },
            fingerprint: fn() => Price::where('model_type', $modelType)->where('model_id', $modelId)->max('updated_at'),
            checkInterval: 60,
        );

        return response()->json($models);
    }
}
