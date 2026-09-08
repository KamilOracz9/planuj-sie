<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Kamiloracz9\MediaGallery\Models\Gallery;

/**
 * The in-house global Gallery (App\Models\Gallery) was replaced by the
 * kamiloracz9/media-gallery package (Kamiloracz9\MediaGallery\Models\Gallery).
 * Existing `media` rows uploaded through the old class still have that old
 * class name stored literally in `model_type` - without this rename they'd
 * become unreachable (the package's Gallery model queries by its own class
 * string, which no longer matches).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('media')
            ->where('model_type', 'App\\Models\\Gallery')
            ->update(['model_type' => Gallery::class]);
    }

    public function down(): void
    {
        DB::table('media')
            ->where('model_type', Gallery::class)
            ->update(['model_type' => 'App\\Models\\Gallery']);
    }
};
