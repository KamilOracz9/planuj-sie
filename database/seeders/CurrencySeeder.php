<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    use WithoutModelEvents;

    // JPY has decimal_places=0 (unlike the other three) - a concrete,
    // real-world proof that Currency::toMinorUnits()/toMajorUnits() convert
    // per-currency (10 ** decimal_places), not a hardcoded x100: 100 JPY
    // stays 100 in the minor-unit `prices.amount` column, not 10000.
    const CURRENCIES = [
        ['code' => 'PLN', 'name' => 'Polski złoty', 'symbol' => 'zł', 'decimal_places' => 2],
        ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimal_places' => 2],
        ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimal_places' => 2],
        ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥', 'decimal_places' => 0],
    ];

    public function run(): void
    {
        foreach (self::CURRENCIES as $index => $currency) {
            DB::table(Currency::tableName())->insert([
                'id' => $index + 1,
                ...$currency,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
