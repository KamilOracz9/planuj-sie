<?php

use App\Models\AttributeOption;
use App\Models\Translations\AttributeOptionTranslation;
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
        Schema::create('attribute_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('attribute_option_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AttributeOption::class, AttributeOptionTranslation::FOREIGN_KEY)->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->unique([AttributeOptionTranslation::FOREIGN_KEY, 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_options');
        Schema::dropIfExists('attribute_option_translations');
    }
};
