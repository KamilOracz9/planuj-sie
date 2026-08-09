<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_media_collections', function (Blueprint $table) {
            $table->id();
            // cascade: if the MediaCollection itself is deleted, its
            // attachment records to specific model instances are meaningless
            // too - the underlying Media rows are cleaned up separately
            // (MediaCollection's own deleted hook), not by this FK.
            $table->foreignId('media_collection_id')->constrained('media_collections')->cascadeOnDelete();
            $table->morphs('model');
            $table->timestamps();
            $table->unique(['media_collection_id', 'model_type', 'model_id'], 'model_media_collections_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_media_collections');
    }
};
