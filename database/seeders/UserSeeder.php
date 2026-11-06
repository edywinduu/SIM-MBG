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
                'username' => 'siswa',
                'email' => 'edy@sekolah.id',
                'password_hash' => Hash::make('123'),
                'full_name' => 'edy keren',
                'role' => 'student',
                'institution' => 'SMP Negeri 1 Denpasar',
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
