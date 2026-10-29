<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil kredensial dari file config/auth.php yang membaca .env
        $adminConfig = config('auth.admin_seed');

        // Gunakan updateOrCreate untuk mencegah error duplikasi data jika seeder dijalankan berulang
        User::updateOrCreate(
            [
                // Kunci pencarian: cari user dengan email ini
                'email' => $adminConfig['email']
            ],
            [
                // Kolom yang dibuat atau diperbarui
                'name'     => $adminConfig['name'],
                'username' => $adminConfig['username'],
                'password' => Hash::make($adminConfig['password']),
                'role'     => 'admin',
            ]
        );
    }
}
