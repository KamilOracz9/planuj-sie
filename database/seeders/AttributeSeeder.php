<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\Translations\AttributeTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributeSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table(Attribute::tableName())->insert([
            'id' => 1,
            'attribute_type_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table(AttributeTranslation::tableName())->insert([
            'id' => 1,
            'locale' => 'pl-PL',
            'name' => 'Atrybut tekstowy 1',
            'slug' => 'atrybut-tekstowy-1',
            'attribute_id' => 1,
        ]);

        DB::table(AttributeTranslation::tableName())->insert([
            'id' => 2,
            'locale' => 'en-US',
            'name' => 'Text attribute 1',
            'slug' => 'text-attribute-1',
            'attribute_id' => 1,
        ]);
    }
}
