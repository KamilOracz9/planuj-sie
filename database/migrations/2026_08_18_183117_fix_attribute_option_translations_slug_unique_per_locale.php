<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Sluggable::bootSluggable() derives a slug per-locale from that locale's own
// name (Str::slug($name)). A globally-unique `slug` column then guarantees a
// crash for any option whose name is identical across locales - which is the
// common case for short codes like size options (S/M/L/XL are the same word
// in pl-PL and en-US), since both locale rows end up wanting the same slug.
// Scoping uniqueness to (slug, locale) instead - matching how routing itself
// is locale-prefixed throughout this app, so the same slug in two different
// locale namespaces was never actually a routing collision.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attribute_option_translations', function (Blueprint $table) {
            $table->dropUnique('attribute_option_translations_slug_unique');
            $table->unique(['slug', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::table('attribute_option_translations', function (Blueprint $table) {
            $table->dropUnique(['slug', 'locale']);
            $table->unique('slug');
        });
    }
};
