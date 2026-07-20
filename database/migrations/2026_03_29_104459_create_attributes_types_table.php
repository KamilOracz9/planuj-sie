<?php

use App\Models\AttributeType;
use App\Models\Translations\AttributeTypeTranslation;
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
        Schema::create('attribute_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 255);
            $table->unsignedInteger('order_column')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('attribute_type_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AttributeType::class, AttributeTypeTranslation::FOREIGN_KEY)->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->unique([AttributeTypeTranslation::FOREIGN_KEY, 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes_types');
        Schema::dropIfExists('attribute_type_translations');
    }
};
