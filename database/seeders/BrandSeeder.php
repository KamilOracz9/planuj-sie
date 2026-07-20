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

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table(Brand::tableName())->insert([
            'id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table(BrandTranslation::tableName())->insert([
            'id' => 1,
            'locale' => 'pl-PL',
            'name' => 'Marka 1',
            'slug' => 'marka-1',
            'brand_id' => 1,
        ]);

        DB::table(BrandTranslation::tableName())->insert([
            'id' => 2,
            'locale' => 'en-US',
            'name' => 'Brand 1',
            'slug' => 'brand-1',
            'brand_id' => 1,
        ]);
    }
}
