<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SppgStaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userMap = DB::table('users')->pluck('id', 'username');
        $sppgMap = DB::table('sppg_units')->pluck('id', 'code');

        $staffList = [
            [
                'username' => 'pgw.alya',
                'name' => 'Pgw Alya Sudarma',
                'position' => 'Pegawai SPPG',
                'sppg_code' => 'SPPG-DPS-01',
            ],
        ];

        foreach ($staffList as $staff) {
            $userId = $userMap[$staff['username']] ?? null;
            $sppgId = $sppgMap[$staff['sppg_code']] ?? null;

            if (! $userId || ! $sppgId) {
                continue;
            }

            DB::table('sppg_staff')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'name' => $staff['name'],
                    'position' => $staff['position'],
                    'sppg_id' => $sppgId,
                ]
            );
        }
    }
}
