<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Translations\BrandTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    use WithoutModelEvents;

    const BRANDS = [
        ['id' => 1, 'pl-PL' => ['name' => 'Marka 1', 'slug' => 'marka-1'], 'en-US' => ['name' => 'Brand 1', 'slug' => 'brand-1']],
        ['id' => 2, 'pl-PL' => ['name' => 'Marka 2', 'slug' => 'marka-2'], 'en-US' => ['name' => 'Brand 2', 'slug' => 'brand-2']],
        ['id' => 3, 'pl-PL' => ['name' => 'Marka 3', 'slug' => 'marka-3'], 'en-US' => ['name' => 'Brand 3', 'slug' => 'brand-3']],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::BRANDS as $brand) {
            DB::table(Brand::tableName())->insert([
                'id' => $brand['id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table(BrandTranslation::tableName())->insert([
                ['locale' => 'pl-PL', 'name' => $brand['pl-PL']['name'], 'slug' => $brand['pl-PL']['slug'], 'brand_id' => $brand['id']],
                ['locale' => 'en-US', 'name' => $brand['en-US']['name'], 'slug' => $brand['en-US']['slug'], 'brand_id' => $brand['id']],
            ]);
        }

        // Each channel carries a genuinely different brand catalog:
        // Default (1) sees all 3; B2B (2) drops Brand 3; Marketplace (3)
        // drops Brand 2 - no two channels show the same set.
        DB::table('channel_visibilities')->insert([
            ['channel_id' => 2, 'model_type' => Brand::class, 'model_id' => 3, 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['channel_id' => 3, 'model_type' => Brand::class, 'model_id' => 2, 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
