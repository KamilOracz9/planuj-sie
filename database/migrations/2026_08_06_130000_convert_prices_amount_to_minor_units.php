<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->unsignedBigInteger('amount_minor')->default(0)->after('amount');
        });

        // Convert existing decimal amounts (major units, e.g. "100.00" PLN)
        // to integer minor units using each row's own currency's
        // decimal_places (100.00 * 10^2 = 10000), rather than a fixed x100,
        // since decimal_places varies per currency.
        DB::statement('
            UPDATE prices
            INNER JOIN currencies ON currencies.id = prices.currency_id
            SET prices.amount_minor = ROUND(prices.amount * POWER(10, currencies.decimal_places))
        ');

        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn('amount');
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->renameColumn('amount_minor', 'amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prices', function (Blueprint $table) {
            $table->decimal('amount_major', 12, 4)->default(0)->after('amount');
        });

        DB::statement('
            UPDATE prices
            INNER JOIN currencies ON currencies.id = prices.currency_id
            SET prices.amount_major = prices.amount / POWER(10, currencies.decimal_places)
        ');

        Schema::table('prices', function (Blueprint $table) {
            $table->dropColumn('amount');
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->renameColumn('amount_major', 'amount');
        });
    }
};
