<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $users = [
            [
                'id' => (string) Str::uuid(),
                'name' => 'Admin Utama',
                'email' => 'admin1@visitlapas.com',
                'password' => Hash::make('Qwerty123*'),
                'phone' => '081234567890',
                'is_active' => true,
                'last_login_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Admin Operasional',
                'email' => 'admin2@visitlapas.com',
                'password' => Hash::make('Qwerty123*'),
                'phone' => '081298765432',
                'is_active' => true,
                'last_login_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Admin Layanan',
                'email' => 'admin3@visitlapas.com',
                'password' => Hash::make('Qwerty123*'),
                'phone' => null,
                'is_active' => true,
                'last_login_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // upsert to avoid duplicate emails if seeder is run multiple times
        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
