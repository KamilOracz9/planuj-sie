<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Translations\ChannelTranslation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Channel #1 (the default one) already exists by the time seeders run - see
// 2026_08_10_090100_seed_default_channel.php, a migration (not a seeder)
// because 2026_08_09_100300_add_channel_id_to_media_table.php needs a
// channel to exist at migration time, before any seeder runs. This just
// adds two more, non-default channels for a realistic multi-channel setup.
class ChannelSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        DB::table(Channel::tableName())->insert([
            ['id' => 2, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table(ChannelTranslation::tableName())->insert([
            ['locale' => 'pl-PL', 'name' => 'Sklep B2B', 'slug' => 'sklep-b2b', 'channel_id' => 2],
            ['locale' => 'en-US', 'name' => 'B2B Store', 'slug' => 'b2b-store', 'channel_id' => 2],
            ['locale' => 'pl-PL', 'name' => 'Marketplace', 'slug' => 'marketplace-pl', 'channel_id' => 3],
            ['locale' => 'en-US', 'name' => 'Marketplace', 'slug' => 'marketplace-en', 'channel_id' => 3],
        ]);
    }
}
