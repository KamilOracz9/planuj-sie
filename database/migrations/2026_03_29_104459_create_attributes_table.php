<?php

use App\Models\Attribute;
use App\Models\Translations\AttributeTranslation;
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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('order_column')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('attribute_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Attribute::class, AttributeTranslation::FOREIGN_KEY)->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->unique([AttributeTranslation::FOREIGN_KEY, 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
        Schema::dropIfExists('attribute_translations');
    }
};
