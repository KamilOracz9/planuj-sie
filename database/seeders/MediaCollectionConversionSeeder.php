<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// A "thumb" conversion on the `main_image` collection with DIFFERENT
// dimensions per channel - proof that App\Traits\Media\HasMediaCollections::registerMediaConversions()
// really does look up conversions by the specific media's own channel_id,
// not just by collection (verified manually earlier this session: two
// channels' "thumb" conversions for the same collection produce genuinely
// different files). Deliberately `main_image`, not `packshot`: packshot is
// only assigned to the Default channel (see MediaCollectionAssignmentSeeder),
// so it could never actually be uploaded on a second channel to compare -
// main_image is assigned on all 3 channels.
class MediaCollectionConversionSeeder extends Seeder
{
    use WithoutModelEvents;

    const CONVERSIONS = [
        ['channel_id' => 1, 'name' => 'thumb', 'width' => 600, 'height' => 600, 'fit' => 'crop'],
        ['channel_id' => 3, 'name' => 'thumb', 'width' => 800, 'height' => 800, 'fit' => 'contain'],
    ];

    public function run(): void
    {
        $mainImageId = DB::table('media_collections')->where('code', 'main_image')->value('id');
        $now = now();

        DB::table('media_collection_conversions')->insert(array_map(
            fn($conversion) => [
                'media_collection_id' => $mainImageId,
                ...$conversion,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            self::CONVERSIONS
        ));
    }
}
