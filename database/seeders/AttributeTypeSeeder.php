<?php

namespace Database\Seeders;

use App\Models\AttributeType;
use App\Models\Translations\AttributeTypeTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributeTypeSeeder extends Seeder
{
    use WithoutModelEvents;

    const TYPES = [
        [
            'code' => 'text',
            'pl-PL' => [
                'name' => 'Tekst',
                'slug' => 'tekst',
            ],
            'en-US' => [
                'name' => 'Text',
                'slug' => 'text',
            ]
        ],
        [
            'code' => 'number',
            'pl-PL' => [
                'name' => 'Liczba',
                'slug' => 'liczba',
            ],
            'en-US' => [
                'name' => 'Number',
                'slug' => 'number',
            ]
        ],
        [
            'code' => 'select',
            'pl-PL' => [
                'name' => 'Wybór',
                'slug' => 'wybor',
            ],
            'en-US' => [
                'name' => 'Select',
                'slug' => 'select',
            ]
        ],
        [
            'code' => 'multiselect',
            'pl-PL' => [
                'name' => 'Wielokrotny wybór',
                'slug' => 'wielokrotny-wybor',
            ],
            'en-US' => [
                'name' => 'Multiselect',
                'slug' => 'multiselect',
            ]
        ],
        [
            'code' => 'date',
            'pl-PL' => [
                'name' => 'Data',
                'slug' => 'data',
            ],
            'en-US' => [
                'name' => 'Date',
                'slug' => 'date',
            ]
        ],
        [
            'code' => 'boolean',
            'pl-PL' => [
                'name' => 'Prawda/Fałsz',
                'slug' => 'prawda-falsz',
            ],
            'en-US' => [
                'name' => 'Boolean',
                'slug' => 'boolean',
            ]
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::TYPES as $index => $type) {
            DB::table(AttributeType::tableName())->insert([
                'id' => $index + 1,
                'code' => $type['code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table(AttributeTypeTranslation::tableName())->insert([
                'id' => $index + count(self::TYPES) + 1,
                'locale' => 'pl-PL',
                'name' => $type['pl-PL']['name'],
                'slug' => $type['pl-PL']['slug'],
                'attribute_type_id' => $index + 1,
            ]);

            DB::table(AttributeTypeTranslation::tableName())->insert([
                'id' => $index + count(self::TYPES) * 2 + 1,
                'locale' => 'en-US',
                'name' => $type['en-US']['name'],
                'slug' => $type['en-US']['slug'],
                'attribute_type_id' => $index + 1,
            ]);
        }
    }
}
