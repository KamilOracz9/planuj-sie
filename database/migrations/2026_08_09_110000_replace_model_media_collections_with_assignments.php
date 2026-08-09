<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Replaces the explicit per-model-instance attach step (model_media_collections:
// media_collection_id + morphs('model')) with a per-(channel, model type)
// assignment: which collections are offered for which model TYPE, in which
// channel - shared by every instance of that type in that channel, not
// decided per individual Product/Brand/etc row.
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('model_media_collections');

        Schema::create('media_collection_assignments', function (Blueprint $table) {
            $table->id();
            // cascade: assignment config, disposable if the collection or
            // channel disappears - same philosophy as media_collection_conversions.
            $table->foreignId('media_collection_id')->constrained('media_collections')->cascadeOnDelete();
            $table->foreignId('channel_id')->constrained('channels')->cascadeOnDelete();
            // One of config('media.model_types') keys (e.g. 'products',
            // 'attribute-options') - a model TYPE, not a specific instance.
            $table->string('model_type');
            $table->timestamps();
            $table->unique(['media_collection_id', 'channel_id', 'model_type'], 'media_collection_assignments_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_collection_assignments');

        Schema::create('model_media_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_collection_id')->constrained('media_collections')->cascadeOnDelete();
            $table->morphs('model');
            $table->timestamps();
            $table->unique(['media_collection_id', 'model_type', 'model_id'], 'model_media_collections_unique');
        });
    }
};
