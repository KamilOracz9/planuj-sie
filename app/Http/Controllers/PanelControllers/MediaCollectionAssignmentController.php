<?php

namespace App\Http\Controllers\PanelControllers;

use App\Enums\CacheKeys;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

// Read-only: which MediaCollections are offered for a given model TYPE, per
// channel - configured centrally on MediaCollection's own edit page (the
// "assignments" nested array, written via HasMediaCollectionAssignments),
// not per model instance. Grouped by channel_id so the panel can filter by
// whichever channel tab is currently active without a second request.
class MediaCollectionAssignmentController extends Controller
{
    public function forModelType(string $modelType)
    {
        abort_if(!array_key_exists($modelType, config('media.model_types')), 404, 'Unknown model type.');

        $data = cache()->remember(
            CacheKeys::MEDIA_COLLECTION_ASSIGNMENTS_BY_MODEL_TYPE->value . "_$modelType",
            config('app.cache_lifetime'),
            function () use ($modelType) {
                return DB::table('media_collection_assignments')
                    ->join('media_collections', 'media_collections.id', '=', 'media_collection_assignments.media_collection_id')
                    ->where('media_collection_assignments.model_type', $modelType)
                    ->select([
                        'media_collection_assignments.channel_id',
                        'media_collections.id as media_collection_id',
                        'media_collections.code',
                        'media_collections.name',
                        'media_collections.kind',
                        'media_collections.type',
                    ])
                    ->get()
                    ->groupBy('channel_id')
                    ->map(fn($rows) => $rows->map(fn($row) => [
                        'id' => $row->media_collection_id,
                        'code' => $row->code,
                        'name' => $row->name,
                        'kind' => $row->kind,
                        'type' => $row->type,
                    ])->values()->all())
                    ->toArray();
            }
        );

        return response()->json($data);
    }
}
