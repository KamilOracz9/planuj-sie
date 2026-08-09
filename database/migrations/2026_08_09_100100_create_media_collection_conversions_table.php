<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_collection_conversions', function (Blueprint $table) {
            $table->id();
            // cascade: pure conversion config owned by its parent collection,
            // not historical/financial data - contrast with media.channel_id
            // below, which protects real uploaded assets with restrictOnDelete.
            $table->foreignId('media_collection_id')->constrained('media_collections')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('channels')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->enum('fit', ['crop', 'contain']);
            $table->timestamps();
            $table->unique(['media_collection_id', 'channel_id', 'name'], 'media_collection_conversions_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_collection_conversions');
    }
};
