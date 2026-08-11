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

        // A 3rd, sibling root category (no parent_id) - rounds out the tree
        // with a second top-level branch alongside Kategoria 1.
        DB::table(Category::tableName())->insert([
            'id' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table(CategoryTranslation::tableName())->insert([
            'id' => 5,
            'locale' => 'pl-PL',
            'name' => 'Kategoria 3',
            'slug' => 'kategoria-3',
            'category_id' => 3,
        ]);

        DB::table(CategoryTranslation::tableName())->insert([
            'id' => 6,
            'locale' => 'en-US',
            'name' => 'Category 3',
            'slug' => 'category-3',
            'category_id' => 3,
        ]);

        // Each channel drops a different branch: B2B (2) has no Kategoria 3,
        // Marketplace (3) has no Kategoria 2 (the child of Kategoria 1) -
        // only Default (1) shows the full 3-category tree.
        DB::table('channel_visibilities')->insert([
            ['channel_id' => 2, 'model_type' => Category::class, 'model_id' => 3, 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
            ['channel_id' => 3, 'model_type' => Category::class, 'model_id' => 2, 'is_enabled' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
