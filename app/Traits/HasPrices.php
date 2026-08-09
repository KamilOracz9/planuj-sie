<?php

namespace App\Traits;

use App\Models\Currency;
use App\Models\Price;
use Illuminate\Support\Facades\DB;

trait HasPrices
{
    protected static function bootPrices()
    {
        static::saved(function ($model) {
            $prices = request()->input('prices');

            if (!is_array($prices)) {
                return;
            }

            DB::transaction(function () use ($model, $prices) {
                // whereNotIn() only works on a single column, but "stale" here
                // means "not in the submitted set of (channel_id, currency_id)
                // pairs" - a composite condition - so the stale rows are found
                // in PHP and removed by id in one mass delete instead.
                $submittedPairs = array_map(
                    fn($price) => (int) $price['channel_id'] . '-' . (int) $price['currency_id'],
                    $prices
                );

                $staleIds = Price::query()
                    ->where('model_type', get_class($model))
                    ->where('model_id', $model->id)
                    ->get()
                    ->reject(fn($existing) => in_array($existing->channel_id . '-' . $existing->currency_id, $submittedPairs))
                    ->pluck('id');

                if ($staleIds->isNotEmpty()) {
                    Price::query()->whereIn('id', $staleIds)->delete();
                }

                // `prices.amount` is an integer stored in the currency's
                // smallest unit (see Currency::toMinorUnits/toMajorUnits) -
                // the admin types major units (e.g. "100"), so it's converted
                // here before writing, keyed per-row by that row's own currency.
                $currencies = Currency::query()
                    ->whereIn('id', array_column($prices, 'currency_id'))
                    ->get()
                    ->keyBy('id');

                foreach ($prices as $price) {
                    $currency = $currencies->get($price['currency_id']);

                    Price::query()->updateOrCreate(
                        [
                            'model_id' => $model->id,
                            'model_type' => get_class($model),
                            'channel_id' => $price['channel_id'],
                            'currency_id' => $price['currency_id'],
                        ],
                        [
                            'amount' => $currency->toMinorUnits($price['amount']),
                        ]
                    );
                }
            });

            // Explicit call regardless of whether any updateOrCreate() ran above:
            // if $prices is an empty array, every existing row is removed via a
            // mass delete (no Price::saved()/deleted() model events fire for
            // that), so nothing else would clear this model's select-by-model
            // cache entry.
            Price::clearSelectByModelCache(get_class($model), $model->id);
        });

        static::deleted(function ($model) {
            Price::query()
                ->where('model_type', get_class($model))
                ->where('model_id', $model->id)
                ->delete();

            Price::clearSelectByModelCache(get_class($model), $model->id);
        });
    }
}
