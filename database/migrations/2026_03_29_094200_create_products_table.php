<?php

use App\Models\Product;
use App\Models\Translations\ProductTranslation;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class, ProductTranslation::FOREIGN_KEY)->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('short_description', 255)->nullable();
            $table->text('description', 500)->nullable();
            $table->unique([ProductTranslation::FOREIGN_KEY, 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_translations');
    }
};
