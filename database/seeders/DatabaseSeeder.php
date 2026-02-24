<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['email' => 'admin@fileserver.hd5.at'],
            [
                'name' => 'Admin',
                'password' => Hash::make('changeme123'),
                'is_admin' => true,
                'storage_quota' => 100 * 1024 * 1024 * 1024, // 100 GB
                'storage_used' => 0,
                'email_verified_at' => now(),
            ]
        );
    }
}
