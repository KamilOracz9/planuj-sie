<?php

namespace Database\Seeders;

use App\Enums\CacheKeys;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
        ]);

        cache()->forget(CacheKeys::USERS_LIST);
    }
}
