<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Repairs the degraded state left by 2026_08_09_100300_add_channel_id_to_media_table.php
// on any install where it ran with zero Channel rows in existence: that
// migration's own `if (!$backfillChannelId) return;` guard meant it silently
// skipped adding NOT NULL + the FK entirely, leaving media.channel_id
// nullable with no foreign key at all. 2026_08_10_090100_seed_default_channel.php
// now guarantees a channel exists by the time this migration runs, so this
// finishes the job. No-op (checked via information_schema, not a fixed
// assumption) on any environment where the original migration already
// completed properly.
return new class extends Migration
{
    public function up(): void
    {
        $hasForeignKey = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'media')
            ->where('COLUMN_NAME', 'channel_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if ($hasForeignKey) {
            return;
        }

        $defaultChannelId = DB::table('channels')->where('is_default', true)->value('id')
            ?? DB::table('channels')->orderBy('id')->value('id');

        if (!$defaultChannelId) {
            throw new \RuntimeException('Cannot fix media.channel_id: no Channel rows exist yet.');
        }

        // No doctrine/dbal installed, so this can't use ->change() - same
        // add-column-with-default -> backfill -> drop -> rename dance as
        // the original migration.
        Schema::table('media', function (Blueprint $table) use ($defaultChannelId) {
            $table->unsignedBigInteger('channel_id_required')->default($defaultChannelId)->after('channel_id');
        });

        DB::statement('UPDATE media SET channel_id_required = COALESCE(channel_id, ?)', [$defaultChannelId]);

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('channel_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->renameColumn('channel_id_required', 'channel_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->foreign('channel_id')->references('id')->on('channels')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['channel_id']);
        });

        // No doctrine/dbal - can't ->change() a column to nullable in place,
        // same add/backfill/drop/rename dance as up().
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_id_nullable')->nullable()->after('channel_id');
        });

        DB::statement('UPDATE media SET channel_id_nullable = channel_id');

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('channel_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->renameColumn('channel_id_nullable', 'channel_id');
        });
    }
};
