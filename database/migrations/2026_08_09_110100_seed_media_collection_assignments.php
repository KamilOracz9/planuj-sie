<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Recreates, under the new (channel, model_type) assignment model, the same
// "replacement collection -> model type" mapping the old per-instance
// auto-attach seed (2026_08_09_100400_seed_media_collections.php) used -
// just cross-joined against every existing channel instead of every existing
// model instance, since assignments are no longer per-instance.
return new class extends Migration
{
    private const ASSIGNMENTS = [
        'logo' => ['brands', 'series', 'collections'],
        'documents' => ['brands', 'series', 'collections', 'categories', 'products', 'variants'],
        'icon' => ['attributes', 'attribute-options', 'attribute-values'],
        'gallery' => ['products', 'variants'],
        'main_image' => ['products', 'variants'],
        'main_image_2' => ['products', 'variants'],
    ];

    public function up(): void
    {
        $now = now();
        $collectionIds = DB::table('media_collections')->whereIn('code', array_keys(self::ASSIGNMENTS))->pluck('id', 'code');
        $channelIds = DB::table('channels')->pluck('id');

        $rows = [];
        foreach (self::ASSIGNMENTS as $code => $modelTypes) {
            $mediaCollectionId = $collectionIds[$code] ?? null;
            if (!$mediaCollectionId) {
                continue;
            }

            foreach ($channelIds as $channelId) {
                foreach ($modelTypes as $modelType) {
                    $rows[] = [
                        'media_collection_id' => $mediaCollectionId,
                        'channel_id' => $channelId,
                        'model_type' => $modelType,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if ($rows) {
            DB::table('media_collection_assignments')->insertOrIgnore($rows);
        }
    }

    public function down(): void
    {
        $collectionIds = DB::table('media_collections')->whereIn('code', array_keys(self::ASSIGNMENTS))->pluck('id');
        DB::table('media_collection_assignments')->whereIn('media_collection_id', $collectionIds)->delete();
    }
};
