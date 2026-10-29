<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultPasswordHash = Hash::make('password');

        $studentUsers = [
            [
                'username' => 'alya',
                'email' => 'alya@sekolah.id',
                'password_hash' => $defaultPasswordHash,
                'full_name' => 'Alya MBG',
                'role' => 'student',
                'institution' => 'SMA Negeri 1 Denpasar',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'aditya',
                'email' => 'aditya@sekolah.id',
                'password_hash' => $defaultPasswordHash,
                'full_name' => 'Komang Aditya',
                'role' => 'student',
                'institution' => 'SMP Negeri 1 Denpasar',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'gita',
                'email' => 'gita@sekolah.id',
                'password_hash' => $defaultPasswordHash,
                'full_name' => 'Ni Putu Gita Dewi',
                'role' => 'student',
                'institution' => 'SMP Negeri 2 Denpasar',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($studentUsers as $user) {
            DB::table('users')->updateOrInsert(
                ['username' => $user['username']],
                $user
            );
        }
    }
}
