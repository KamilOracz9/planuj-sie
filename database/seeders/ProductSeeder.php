<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Translations\ProductTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table(Product::tableName())->insert([
            'id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table(ProductTranslation::tableName())->insert([
            'id' => 1,
            'locale' => 'pl-PL',
            'name' => 'Produkt 1',
            'slug' => 'produkt-1',
            'product_id' => 1,
        ]);
    }
}
