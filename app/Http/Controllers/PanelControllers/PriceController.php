<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Price;

class PriceController extends Controller
{
    public function selectByModel(string $locale, string $modelType, int $modelId)
    {
        $models = cache()->remember(
            CacheKeys::PRICES_SELECT_BY_MODEL->value . "_$locale" . "_$modelType" . "_$modelId",
            config('app.cache_lifetime'),
            function () use ($modelType, $modelId) {
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
            }
        );

        return response()->json($models);
    }
}
