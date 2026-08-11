<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Translations\CollectionTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CollectionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::table(Collection::tableName())->insert([
            ['id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table(CollectionTranslation::tableName())->insert([
            ['locale' => 'pl-PL', 'name' => 'Nowości', 'slug' => 'nowosci', 'collection_id' => 1],
            ['locale' => 'en-US', 'name' => 'New Arrivals', 'slug' => 'new-arrivals', 'collection_id' => 1],
            ['locale' => 'pl-PL', 'name' => 'Wyprzedaż', 'slug' => 'wyprzedaz', 'collection_id' => 2],
            ['locale' => 'en-US', 'name' => 'Clearance', 'slug' => 'clearance', 'collection_id' => 2],
        ]);

        // HasChannelVisibility isn't only a Product thing - and each channel
        // sees a different collection: B2B (2) drops "Nowości", Marketplace
        // (3) drops "Wyprzedaż" - only Default (1) shows both.
        DB::table('channel_visibilities')->insert([
            ['channel_id' => 2, 'model_type' => Collection::class, 'model_id' => 1, 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['channel_id' => 3, 'model_type' => Collection::class, 'model_id' => 2, 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
