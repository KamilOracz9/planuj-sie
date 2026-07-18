<?php

namespace Database\Seeders;

use Database\Seeders\BrandSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\VariantSeeder;
use Database\Seeders\AttributeSeeder;
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
            BrandSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            VariantSeeder::class,
            AttributeSeeder::class,
        ]);
    }
}
