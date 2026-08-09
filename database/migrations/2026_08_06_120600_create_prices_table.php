<?php

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
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            // restrictOnDelete (not cascadeOnDelete, unlike channel_visibilities):
            // deleting a Channel/Currency that still has prices referencing it
            // should fail loudly, not silently wipe price history.
            $table->foreignId('channel_id')->constrained('channels')->restrictOnDelete();
            $table->foreignId('currency_id')->constrained('currencies')->restrictOnDelete();
            $table->morphs('model');
            $table->decimal('amount', 12, 4);
            $table->timestamps();
            $table->unique(['model_type', 'model_id', 'channel_id', 'currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
