<?php

use App\Models\Category;
use App\Models\Translations\CategoryTranslation;
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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Category::class, CategoryTranslation::FOREIGN_KEY)->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('short_description', 255)->nullable();
            $table->text('description', 500)->nullable();
            $table->unique([CategoryTranslation::FOREIGN_KEY, 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
        Schema::dropIfExists('category_translations');
    }
};
