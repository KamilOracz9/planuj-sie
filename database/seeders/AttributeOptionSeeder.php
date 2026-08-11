<?php

namespace Database\Seeders;

use App\Models\AttributeOption;
use App\Models\Translations\AttributeOptionTranslation;
use App\Models\Price;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Options for the two "select"-family attributes from AttributeSeeder:
// Kolor (id 5, select) and Rozmiar (id 6, multiselect).
class AttributeOptionSeeder extends Seeder
{
    use WithoutModelEvents;

    const OPTIONS = [
        ['id' => 1, 'attribute_id' => 5, 'pl-PL' => ['name' => 'Czerwony', 'slug' => 'czerwony'], 'en-US' => ['name' => 'Red', 'slug' => 'red']],
        ['id' => 2, 'attribute_id' => 5, 'pl-PL' => ['name' => 'Niebieski', 'slug' => 'niebieski'], 'en-US' => ['name' => 'Blue', 'slug' => 'blue']],
        ['id' => 3, 'attribute_id' => 5, 'pl-PL' => ['name' => 'Zielony', 'slug' => 'zielony'], 'en-US' => ['name' => 'Green', 'slug' => 'green']],
        ['id' => 4, 'attribute_id' => 6, 'pl-PL' => ['name' => 'S', 'slug' => 's-rozmiar'], 'en-US' => ['name' => 'S', 'slug' => 's-size']],
        ['id' => 5, 'attribute_id' => 6, 'pl-PL' => ['name' => 'M', 'slug' => 'm-rozmiar'], 'en-US' => ['name' => 'M', 'slug' => 'm-size']],
        ['id' => 6, 'attribute_id' => 6, 'pl-PL' => ['name' => 'L', 'slug' => 'l-rozmiar'], 'en-US' => ['name' => 'L', 'slug' => 'l-size']],
        ['id' => 7, 'attribute_id' => 6, 'pl-PL' => ['name' => 'XL', 'slug' => 'xl-rozmiar'], 'en-US' => ['name' => 'XL', 'slug' => 'xl-size']],
    ];

    public function run(): void
    {
        foreach (self::OPTIONS as $index => $option) {
            DB::table(AttributeOption::tableName())->insert([
                'id' => $option['id'],
                'attribute_id' => $option['attribute_id'],
                'order_column' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table(AttributeOptionTranslation::tableName())->insert([
                ['locale' => 'pl-PL', 'name' => $option['pl-PL']['name'], 'slug' => $option['pl-PL']['slug'], 'attribute_option_id' => $option['id']],
                ['locale' => 'en-US', 'name' => $option['en-US']['name'], 'slug' => $option['en-US']['slug'], 'attribute_option_id' => $option['id']],
            ]);
        }

        // AttributeOption also `use HasPrices` - a size surcharge on "XL"
        // (id 7) demonstrates prices aren't only a Product/Variant thing.
        // Default channel (id 1) + PLN (id 1): 10.00 PLN -> 1000 minor units.
        DB::table(Price::tableName())->insert([
            'channel_id' => 1,
            'currency_id' => 1,
            'model_type' => AttributeOption::class,
            'model_id' => 7,
            'amount' => 1000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
