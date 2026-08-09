<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// No doctrine/dbal in this project, so this avoids Schema::table(...)->change()
// entirely: add a nullable column, backfill it, then add a NOT-NULL
// replacement column with a DEFAULT (same add -> backfill -> drop -> rename
// shape as 2026_08_06_130000_convert_prices_amount_to_minor_units.php).
//
// The DEFAULT is deliberately kept (not dropped) after backfill: the
// out-of-scope global Gallery/library flow (GalleryController/
// DocumentController) never sets channel_id explicitly and must keep working
// untouched, silently landing on this sentinel channel via the DB default.
// The new per-model media controller always passes channel_id explicitly,
// so the default never applies to it.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->unsignedBigInteger('channel_id')->nullable()->after('model_id');
        });

        $backfillChannelId = DB::table('channels')->orderBy('id')->value('id');

        if (DB::table('media')->count() > 0 && !$backfillChannelId) {
            throw new \RuntimeException('Cannot backfill media.channel_id: no Channel rows exist yet.');
        }

        if (!$backfillChannelId) {
            return;
        }

        Schema::table('media', function (Blueprint $table) use ($backfillChannelId) {
            $table->unsignedBigInteger('channel_id_required')->default($backfillChannelId)->after('channel_id');
        });

        DB::statement('UPDATE media SET channel_id_required = COALESCE(channel_id, ?)', [$backfillChannelId]);

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('channel_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->renameColumn('channel_id_required', 'channel_id');
        });

        // media rows represent real uploaded assets, not disposable config -
        // protect them the way Price protects financial history (restrict),
        // unlike the cascade used on media_collection_conversions.channel_id.
        Schema::table('media', function (Blueprint $table) {
            $table->foreign('channel_id')->references('id')->on('channels')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['channel_id']);
            $table->dropColumn('channel_id');
        });
    }
};
