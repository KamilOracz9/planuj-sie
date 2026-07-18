<?php

namespace Database\Seeders;

use App\Models\Variant;
use App\Models\Translations\VariantTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VariantSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table(Variant::tableName())->insert([
            'id' => 1,
            'product_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table(VariantTranslation::tableName())->insert([
            'id' => 1,
            'locale' => 'pl-PL',
            'name' => 'Wariant 1',
            'slug' => 'wariant-1',
            'variant_id' => 1,
        ]);
    }
}
