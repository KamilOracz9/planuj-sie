<?php

namespace Database\Seeders;

use Database\Seeders\AttributeOptionSeeder;
use Database\Seeders\AttributeSeeder;
use Database\Seeders\AttributeTypeSeeder;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ChannelSeeder;
use Database\Seeders\CollectionSeeder;
use Database\Seeders\CurrencySeeder;
use Database\Seeders\MediaCollectionAssignmentSeeder;
use Database\Seeders\MediaCollectionConversionSeeder;
use Database\Seeders\MediaSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SeriesSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\VariantSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // Channel #1 (default) already exists from
            // 2026_08_10_090100_seed_default_channel.php (a migration, not a
            // seeder - see its own docblock for why); this adds 2 more.
            ChannelSeeder::class,
            CurrencySeeder::class,
            AttributeTypeSeeder::class,
            AttributeSeeder::class,
            AttributeOptionSeeder::class,
            BrandSeeder::class,
            SeriesSeeder::class,
            CollectionSeeder::class,
            CategorySeeder::class,
            // Needs all 3 channels + media_collections to exist.
            MediaCollectionAssignmentSeeder::class,
            MediaCollectionConversionSeeder::class,
            ProductSeeder::class,
            VariantSeeder::class,
            // Needs Products/Variants/Brands/AttributeOptions and the two
            // media collection seeders above to already exist.
            MediaSeeder::class,
        ]);
    }
}
