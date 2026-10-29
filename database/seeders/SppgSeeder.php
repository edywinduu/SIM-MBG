<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SppgSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sppgUsers = [
            [
                'username' => 'pgw.alya',
                'email' => 'sppg.dps01@mbg.go.id',
                'password_hash' => Hash::make('embegeh'),
                'full_name' => 'Alisa Mikhailovna Kujou',
                'role' => 'sppg',
                'institution' => 'SPPG Denpasar Barat 01',
                'status' => 'active',
                'updated_at' => now(),
                'created_at' => now(),
            ],
        ];

        foreach ($sppgUsers as $user) {
            DB::table('users')->updateOrInsert(
                ['username' => $user['username']],
                $user
            );
        }
    }
}
