<?php

namespace Database\Seeders;

use App\Models\Series;
use App\Models\Translations\SeriesTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeriesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::table(Series::tableName())->insert([
            ['id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table(SeriesTranslation::tableName())->insert([
            ['locale' => 'pl-PL', 'name' => 'Seria Klasyczna', 'slug' => 'seria-klasyczna', 'series_id' => 1],
            ['locale' => 'en-US', 'name' => 'Classic Series', 'slug' => 'classic-series', 'series_id' => 1],
            ['locale' => 'pl-PL', 'name' => 'Seria Sportowa', 'slug' => 'seria-sportowa', 'series_id' => 2],
            ['locale' => 'en-US', 'name' => 'Sport Series', 'slug' => 'sport-series', 'series_id' => 2],
        ]);

        // Default (1) carries both; B2B (2) only wants the Classic line;
        // Marketplace (3) only lists the Sport line - each channel again
        // ends up with a different, non-overlapping-with-B2B set.
        DB::table('channel_visibilities')->insert([
            ['channel_id' => 2, 'model_type' => Series::class, 'model_id' => 2, 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['channel_id' => 3, 'model_type' => Series::class, 'model_id' => 1, 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
