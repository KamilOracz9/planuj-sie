<?php

namespace App\Traits;

use App\Models\MediaCollectionConversion;
use Illuminate\Support\Facades\DB;

// Same shape as App\Traits\HasPrices: reads a nested array from the request,
// diffs it against existing child rows (whereNotIn can't express the
// composite (channel_id, name) key, so the stale set is found in PHP),
// updateOrCreate()s the rest inside a transaction. Attached to
// MediaCollection::saved() rather than to an owning entity, since
// conversions belong to the collection itself, not to a Product/Variant/etc.
trait HasMediaCollectionConversions
{
    protected static function bootMediaCollectionConversions()
    {
        static::saved(function ($mediaCollection) {
            $conversions = request()->input('conversions');

            if (!is_array($conversions)) {
                return;
            }

            DB::transaction(function () use ($mediaCollection, $conversions) {
                $submittedPairs = array_map(
                    fn($conversion) => (int) $conversion['channel_id'] . '-' . $conversion['name'],
                    $conversions
                );

                $staleIds = MediaCollectionConversion::query()
                    ->where('media_collection_id', $mediaCollection->id)
                    ->get()
                    ->reject(fn($existing) => in_array($existing->channel_id . '-' . $existing->name, $submittedPairs))
                    ->pluck('id');

                if ($staleIds->isNotEmpty()) {
                    MediaCollectionConversion::query()->whereIn('id', $staleIds)->delete();
                }

                foreach ($conversions as $conversion) {
                    MediaCollectionConversion::query()->updateOrCreate(
                        [
                            'media_collection_id' => $mediaCollection->id,
                            'channel_id' => $conversion['channel_id'],
                            'name' => $conversion['name'],
                        ],
                        [
                            'width' => $conversion['width'],
                            'height' => $conversion['height'],
                            'fit' => $conversion['fit'],
                        ]
                    );
                }
            });
        });

        static::deleted(function ($mediaCollection) {
            MediaCollectionConversion::query()
                ->where('media_collection_id', $mediaCollection->id)
                ->delete();
        });
    }
}
