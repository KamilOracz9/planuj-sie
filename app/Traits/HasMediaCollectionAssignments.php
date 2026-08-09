<?php

namespace App\Traits;

use App\Models\MediaCollectionAssignment;
use Illuminate\Support\Facades\DB;

// Same shape as HasMediaCollectionConversions/HasPrices: reads a nested
// array from the request, diffs against existing child rows keyed by a
// composite (channel_id, model_type) pair, updateOrCreate()s the rest inside
// a transaction. Attached to MediaCollection::saved() - assignments belong
// to the collection itself.
trait HasMediaCollectionAssignments
{
    protected static function bootMediaCollectionAssignments()
    {
        static::saved(function ($mediaCollection) {
            $assignments = request()->input('assignments');

            if (!is_array($assignments)) {
                return;
            }

            DB::transaction(function () use ($mediaCollection, $assignments) {
                $submittedPairs = array_map(
                    fn($assignment) => (int) $assignment['channel_id'] . '-' . $assignment['model_type'],
                    $assignments
                );

                $staleIds = MediaCollectionAssignment::query()
                    ->where('media_collection_id', $mediaCollection->id)
                    ->get()
                    ->reject(fn($existing) => in_array($existing->channel_id . '-' . $existing->model_type, $submittedPairs))
                    ->pluck('id');

                if ($staleIds->isNotEmpty()) {
                    MediaCollectionAssignment::query()->whereIn('id', $staleIds)->delete();
                }

                foreach ($assignments as $assignment) {
                    MediaCollectionAssignment::query()->updateOrCreate([
                        'media_collection_id' => $mediaCollection->id,
                        'channel_id' => $assignment['channel_id'],
                        'model_type' => $assignment['model_type'],
                    ]);
                }
            });
        });

        static::deleted(function ($mediaCollection) {
            MediaCollectionAssignment::query()
                ->where('media_collection_id', $mediaCollection->id)
                ->delete();
        });
    }
}
