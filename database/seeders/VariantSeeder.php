<?php

namespace Database\Seeders;

use App\Models\AttributeValue;
use App\Models\Variant;
use App\Models\Translations\VariantTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// 3 variants across Products #1/#2, each with its OWN attribute values and
// its OWN price - proves a Variant's price is independent from its parent
// Product's price (see App\Traits\HasPrices, used by both models).
class VariantSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::table(Variant::tableName())->insert([
            ['id' => 1, 'product_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'product_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'product_id' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table(VariantTranslation::tableName())->insert([
            ['locale' => 'pl-PL', 'name' => 'Wariant 1', 'slug' => 'wariant-1', 'variant_id' => 1],
            ['locale' => 'en-US', 'name' => 'Variant 1', 'slug' => 'variant-1', 'variant_id' => 1],
            ['locale' => 'pl-PL', 'name' => 'Wariant 2', 'slug' => 'wariant-2', 'variant_id' => 2],
            ['locale' => 'en-US', 'name' => 'Variant 2', 'slug' => 'variant-2', 'variant_id' => 2],
            ['locale' => 'pl-PL', 'name' => 'Wariant 3', 'slug' => 'wariant-3', 'variant_id' => 3],
            ['locale' => 'en-US', 'name' => 'Variant 3', 'slug' => 'variant-3', 'variant_id' => 3],
        ]);

        $now = now();

        // Kolor/Rozmiar option ids from AttributeOptionSeeder: 1=Czerwony,
        // 2=Niebieski, 3=Zielony, 4=S, 5=M, 6=L, 7=XL. Each variant picks a
        // DIFFERENT color/size than its parent Product's own values.
        DB::table(AttributeValue::tableName())->insert([
            ['attribute_id' => 5, 'model_type' => Variant::class, 'model_id' => 1, 'data' => json_encode(['value' => 3]), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 6, 'model_type' => Variant::class, 'model_id' => 1, 'data' => json_encode(['value' => [6]]), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 5, 'model_type' => Variant::class, 'model_id' => 2, 'data' => json_encode(['value' => 2]), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 6, 'model_type' => Variant::class, 'model_id' => 2, 'data' => json_encode(['value' => [7]]), 'created_at' => $now, 'updated_at' => $now],
            ['attribute_id' => 5, 'model_type' => Variant::class, 'model_id' => 3, 'data' => json_encode(['value' => 1]), 'created_at' => $now, 'updated_at' => $now],
        ]);

        // Variant-level channel visibility is independent from the parent
        // Product's own visibility (see Variant::ancestorGroupsForVisibility()
        // - a variant still needs its own channel to also be enabled on the
        // parent Product, but can be MORE restrictive on top of that).
        // Variant #1 belongs to Product #1 (visible on Default+Marketplace,
        // hidden on B2B) but is explicitly hidden on Marketplace too, so it
        // only actually shows up on Default. Variants #2/#3 have no override
        // and simply inherit their parent Product's visibility.
        DB::table('channel_visibilities')->insert([
            'channel_id' => 3,
            'model_type' => Variant::class,
            'model_id' => 1,
            'is_enabled' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('prices')->insert([
            // Variant #1 (Produkt #1's variant): 109.99 PLN - different from
            // the parent Product's own 99.99 PLN price.
            ['channel_id' => 1, 'currency_id' => 1, 'model_type' => Variant::class, 'model_id' => 1, 'amount' => 10999, 'created_at' => $now, 'updated_at' => $now],
            // Variant #2: priced on Marketplace in EUR, like its parent's
            // Marketplace price, but a different amount (29.99 vs 24.99).
            ['channel_id' => 3, 'currency_id' => 2, 'model_type' => Variant::class, 'model_id' => 2, 'amount' => 2999, 'created_at' => $now, 'updated_at' => $now],
            // Variant #3 (Produkt #2's variant): 159.99 PLN.
            ['channel_id' => 1, 'currency_id' => 1, 'model_type' => Variant::class, 'model_id' => 3, 'amount' => 15999, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
