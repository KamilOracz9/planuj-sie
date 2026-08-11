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

    // attribute_type_id matches AttributeTypeSeeder::TYPES order:
    // 1=text, 2=number, 3=select, 4=multiselect, 5=date, 6=boolean.
    // One attribute per type, so every AttributeValue::data JSON shape
    // (see HasAttributes::bootAttributes()'s match()) gets exercised by
    // ProductSeeder/VariantSeeder.
    const ATTRIBUTES = [
        ['attribute_type_id' => 1, 'pl-PL' => ['name' => 'Materiał', 'slug' => 'material'], 'en-US' => ['name' => 'Material', 'slug' => 'material-en']],
        ['attribute_type_id' => 2, 'pl-PL' => ['name' => 'Waga (g)', 'slug' => 'waga-g'], 'en-US' => ['name' => 'Weight (g)', 'slug' => 'weight-g']],
        ['attribute_type_id' => 6, 'pl-PL' => ['name' => 'Bestseller', 'slug' => 'bestseller-pl'], 'en-US' => ['name' => 'Bestseller', 'slug' => 'bestseller-en']],
        ['attribute_type_id' => 5, 'pl-PL' => ['name' => 'Data wydania', 'slug' => 'data-wydania'], 'en-US' => ['name' => 'Release date', 'slug' => 'release-date']],
        ['attribute_type_id' => 3, 'pl-PL' => ['name' => 'Kolor', 'slug' => 'kolor'], 'en-US' => ['name' => 'Color', 'slug' => 'color']],
        ['attribute_type_id' => 4, 'pl-PL' => ['name' => 'Rozmiar', 'slug' => 'rozmiar'], 'en-US' => ['name' => 'Size', 'slug' => 'size']],
    ];

    public function run(): void
    {
        foreach (self::ATTRIBUTES as $index => $attribute) {
            $id = $index + 1;

            DB::table(Attribute::tableName())->insert([
                'id' => $id,
                'attribute_type_id' => $attribute['attribute_type_id'],
                'order_column' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table(AttributeTranslation::tableName())->insert([
                ['locale' => 'pl-PL', 'name' => $attribute['pl-PL']['name'], 'slug' => $attribute['pl-PL']['slug'], 'attribute_id' => $id],
                ['locale' => 'en-US', 'name' => $attribute['en-US']['name'], 'slug' => $attribute['en-US']['slug'], 'attribute_id' => $id],
            ]);
        }
    }
}
