<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\Translations\ProductTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// 3 products, each configured differently to cover distinct combinations:
//   #1 - brand+series+collection, all 6 attribute types, visible on
//        Default+Marketplace but explicitly HIDDEN on B2B, priced in two
//        different currencies on two different channels (no fallback).
//   #2 - different brand/series/collection/attribute values, visible on
//        every channel (no channel_visibilities rows at all - the
//        HasChannelVisibility default), priced in PLN+USD on Default.
//   #3 - no brand_id/series_id at all (proves those are genuinely
//        optional), single price in JPY (0 decimal places) to exercise the
//        zero-decimal-currency path on real product data.
class ProductSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::table(Product::tableName())->insert([
            ['id' => 1, 'brand_id' => 1, 'series_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'brand_id' => 2, 'series_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'brand_id' => null, 'series_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table(ProductTranslation::tableName())->insert([
            ['locale' => 'pl-PL', 'name' => 'Produkt 1', 'slug' => 'produkt-1', 'product_id' => 1],
            ['locale' => 'en-US', 'name' => 'Product 1', 'slug' => 'product-1', 'product_id' => 1],
            ['locale' => 'pl-PL', 'name' => 'Produkt 2', 'slug' => 'produkt-2', 'product_id' => 2],
            ['locale' => 'en-US', 'name' => 'Product 2', 'slug' => 'product-2', 'product_id' => 2],
            ['locale' => 'pl-PL', 'name' => 'Produkt 3', 'slug' => 'produkt-3', 'product_id' => 3],
            ['locale' => 'en-US', 'name' => 'Product 3', 'slug' => 'product-3', 'product_id' => 3],
        ]);

        DB::table('product_collection')->insert([
            ['product_id' => 1, 'collection_id' => 1],
            ['product_id' => 2, 'collection_id' => 2],
        ]);

        $now = now();

        // AttributeValue.data JSON shapes match HasAttributes::bootAttributes()'s
        // match() exactly: {"value": ...}, select/multiselect hold
        // AttributeOption id(s) (see AttributeOptionSeeder: 1=Czerwony,
        // 2=Niebieski, 3=Zielony, 4=S, 5=M, 6=L, 7=XL).
        DB::table(AttributeValue::tableName())->insert([
            // Product #1
            ['attribute_id' => 1, 'model_type' => Product::class, 'model_id' => 1, 'data' => json_encode(['value' => 'Bawełna']), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 2, 'model_type' => Product::class, 'model_id' => 1, 'data' => json_encode(['value' => 250]), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 3, 'model_type' => Product::class, 'model_id' => 1, 'data' => json_encode(['value' => true]), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 4, 'model_type' => Product::class, 'model_id' => 1, 'data' => json_encode(['value' => '2026-01-15']), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 5, 'model_type' => Product::class, 'model_id' => 1, 'data' => json_encode(['value' => 1]), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 6, 'model_type' => Product::class, 'model_id' => 1, 'data' => json_encode(['value' => [4, 5]]), 'created_at' => $now, 'updated_at' => $now],
            // Product #2
            ['attribute_id' => 1, 'model_type' => Product::class, 'model_id' => 2, 'data' => json_encode(['value' => 'Skóra']), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 2, 'model_type' => Product::class, 'model_id' => 2, 'data' => json_encode(['value' => 500]), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 3, 'model_type' => Product::class, 'model_id' => 2, 'data' => json_encode(['value' => false]), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 4, 'model_type' => Product::class, 'model_id' => 2, 'data' => json_encode(['value' => '2025-11-01']), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 5, 'model_type' => Product::class, 'model_id' => 2, 'data' => json_encode(['value' => 2]), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 6, 'model_type' => Product::class, 'model_id' => 2, 'data' => json_encode(['value' => [6, 7]]), 'created_at' => $now, 'updated_at' => $now],
            // Product #3 - minimal on purpose
            ['attribute_id' => 3, 'model_type' => Product::class, 'model_id' => 3, 'data' => json_encode(['value' => false]), 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Product #1 sits out B2B entirely; Product #2 is Default+B2B only
        // (not listed on Marketplace); Product #3 has no override, so it's
        // the only one visible on all 3 channels - each channel ends up
        // with a distinct product list.
        DB::table('channel_visibilities')->insert([
            ['channel_id' => 1, 'model_type' => Product::class, 'model_id' => 1, 'is_enabled' => true, 'created_at' => $now, 'updated_at' => $now],
            ['channel_id' => 2, 'model_type' => Product::class, 'model_id' => 1, 'is_enabled' => false, 'created_at' => $now, 'updated_at' => $now],
            ['channel_id' => 3, 'model_type' => Product::class, 'model_id' => 1, 'is_enabled' => true, 'created_at' => $now, 'updated_at' => $now],
            ['channel_id' => 3, 'model_type' => Product::class, 'model_id' => 2, 'is_enabled' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);

        // prices.amount is an integer in the currency's smallest unit (see
        // Currency::toMinorUnits/toMajorUnits) - PLN/EUR/USD have
        // decimal_places=2 (x100), JPY has 0 (no scaling at all).
        DB::table('prices')->insert([
            // Product #1: PLN on Default, EUR on Marketplace - no fallback,
            // no price at all on B2B (where it's hidden anyway).
            ['channel_id' => 1, 'currency_id' => 1, 'model_type' => Product::class, 'model_id' => 1, 'amount' => 9999, 'created_at' => $now, 'updated_at' => $now],
            ['channel_id' => 3, 'currency_id' => 2, 'model_type' => Product::class, 'model_id' => 1, 'amount' => 2499, 'created_at' => $now, 'updated_at' => $now],
            // Product #2: PLN + USD, both on Default.
            ['channel_id' => 1, 'currency_id' => 1, 'model_type' => Product::class, 'model_id' => 2, 'amount' => 14999, 'created_at' => $now, 'updated_at' => $now],
            ['channel_id' => 1, 'currency_id' => 3, 'model_type' => Product::class, 'model_id' => 2, 'amount' => 3999, 'created_at' => $now, 'updated_at' => $now],
            // Product #3: JPY on Default - 1500 JPY stored as 1500, not 150000.
            ['channel_id' => 1, 'currency_id' => 4, 'model_type' => Product::class, 'model_id' => 3, 'amount' => 1500, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
