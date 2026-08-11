<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// api/database/migrations/2026_08_09_110100_seed_media_collection_assignments.php
// cross-joins media_collections with every EXISTING channel - but on a
// truly fresh install that migration runs before any channel exists at all
// (channels only get seeded later, in ChannelSeeder, which runs during the
// seeder phase, strictly after every migration). So it's this seeder's job
// to actually create the real assignments, now that all 3 channels exist.
//
// Deliberately gives each channel a DIFFERENT mix for products/variants -
// this is the clearest demonstration that media collections are configured
// per (channel, model type), not identical everywhere:
//   - Default: the full catalog (packshot, tech included)
//   - B2B: technical documentation focus (manual, karta_techniczna), no packshot
//   - Marketplace: listing-oriented (listing_image), no documents at all
class MediaCollectionAssignmentSeeder extends Seeder
{
    use WithoutModelEvents;

    const PER_CHANNEL_PRODUCT_COLLECTIONS = [
        1 => ['gallery', 'main_image', 'main_image_2', 'documents', 'packshot', 'tech'],
        2 => ['gallery', 'main_image', 'documents', 'manual', 'karta_techniczna'],
        3 => ['gallery', 'main_image', 'listing_image'],
    ];

    // Uniform across channels - varying every model type per channel would
    // dilute the point being demonstrated above.
    const SAME_EVERY_CHANNEL = [
        'logo' => ['brands', 'series', 'collections'],
        'documents' => ['brands', 'series', 'collections', 'categories'],
        'icon' => ['attributes', 'attribute-options', 'attribute-values'],
    ];

    public function run(): void
    {
        $collectionIds = DB::table('media_collections')->pluck('id', 'code');
        $channelIds = DB::table('channels')->pluck('id');
        $now = now();

        $rows = [];

        foreach (self::PER_CHANNEL_PRODUCT_COLLECTIONS as $channelId => $codes) {
            foreach ($codes as $code) {
                foreach (['products', 'variants'] as $modelType) {
                    $rows[] = [
                        'media_collection_id' => $collectionIds[$code],
                        'channel_id' => $channelId,
                        'model_type' => $modelType,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        foreach (self::SAME_EVERY_CHANNEL as $code => $modelTypes) {
            foreach ($channelIds as $channelId) {
                foreach ($modelTypes as $modelType) {
                    $rows[] = [
                        'media_collection_id' => $collectionIds[$code],
                        'channel_id' => $channelId,
                        'model_type' => $modelType,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        DB::table('media_collection_assignments')->insertOrIgnore($rows);
    }
}
