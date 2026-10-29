<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminConfig = config('auth.admin_seed');

        User::updateOrCreate(
            [
                'email' => $adminConfig['email'],
            ],
            [
                'username' => $adminConfig['username'],
                'password_hash' => Hash::make($adminConfig['password']),
                'full_name' => $adminConfig['name'],
                'role' => 'admin',
                'institution' => 'Dinas Pendidikan & Gizi Provinsi',
                'status' => 'active',
            ]
        );

        if ($adminConfig['username'] !== 'admin' && $adminConfig['email'] !== 'admin@mbg.go.id') {
            User::updateOrCreate(
                [
                    'username' => 'admin',
                ],
                [
                    'email' => 'admin@mbg.go.id',
                    'password_hash' => Hash::make('password'),
                    'full_name' => 'Atmind Administrator',
                    'role' => 'admin',
                    'institution' => 'Dinas Pendidikan & Gizi Provinsi',
                    'status' => 'active',
                ]
            );
        }
    }
}
