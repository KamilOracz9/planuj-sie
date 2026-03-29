<?php

use App\Models\Product;
use App\Models\Translations\VariantTranslation;
use App\Models\Variant;
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
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class, 'product_id')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('variant_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Variant::class, VariantTranslation::FOREIGN_KEY)->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('short_description', 255)->nullable();
            $table->text('description', 500)->nullable();
            $table->unique([VariantTranslation::FOREIGN_KEY, 'locale']);
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
