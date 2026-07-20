<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Translations\CategoryTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table(Category::tableName())->insert([
            'id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table(CategoryTranslation::tableName())->insert([
            'id' => 1,
            'locale' => 'pl-PL',
            'name' => 'Kategoria 1',
            'slug' => 'kategoria-1',
            'category_id' => 1,
        ]);
        
        DB::table(CategoryTranslation::tableName())->insert([
            'id' => 2,
            'locale' => 'en-US',
            'name' => 'Category 1',
            'slug' => 'category-1',
            'category_id' => 1,
        ]);

        DB::table(Category::tableName())->insert([
            'id' => 2,
            'parent_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table(CategoryTranslation::tableName())->insert([
            'id' => 3,
            'locale' => 'pl-PL',
            'name' => 'Kategoria 2',
            'slug' => 'kategoria-2',
            'category_id' => 2,
        ]);

        DB::table(CategoryTranslation::tableName())->insert([
            'id' => 4,
            'locale' => 'en-US',
            'name' => 'Category 2',
            'slug' => 'category-2',
            'category_id' => 2,
        ]);
    }
}
