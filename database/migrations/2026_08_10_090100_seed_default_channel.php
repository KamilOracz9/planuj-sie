<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Data-only migration (mirrors 2026_08_09_100400_seed_media_collections.php):
// a fresh install must have at least one Channel from migration time onward,
// not just from seeders (seeders run AFTER all migrations, but
// 2026_08_09_100300_add_channel_id_to_media_table.php needs a channel to
// exist AT MIGRATION TIME or it silently skips making media.channel_id
// NOT NULL/FK'd - see 2026_08_10_090200_fix_media_channel_id_not_null.php).
// Only inserts if channels is empty, so this is a no-op on any environment
// that already has channels (e.g. from a prior partial migration run).
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('channels')->count() > 0) {
            return;
        }

        $now = now();

        DB::table('channels')->insert([
            'id' => 1,
            'is_default' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('channel_translations')->insert([
            ['locale' => 'pl-PL', 'name' => 'Domyślny', 'slug' => 'domyslny', 'channel_id' => 1],
            ['locale' => 'en-US', 'name' => 'Default', 'slug' => 'default', 'channel_id' => 1],
        ]);
    }

    public function down(): void
    {
        DB::table('channel_translations')->where('channel_id', 1)->delete();
        DB::table('channels')->where('id', 1)->where('is_default', true)->delete();
    }
};
