<?php

use App\Models\Locale;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->timestamps();
        });

        Schema::create('locale_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Locale::class, 'locale_id')->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('name', 255);
            $table->unique(['locale_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locales');
        Schema::dropIfExists('locale_translations');
    }
};
