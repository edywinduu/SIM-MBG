<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menuMap = DB::table('menus')->pluck('id', 'name');
        $sppgMap = DB::table('sppg_units')->pluck('id', 'code');

        $today = date('Y-m-d');
        $sppgCode = 'SPPG-DPS-01';

        // 5 hari jadwal menu yang menyesuaikan dengan day tabs nav (-2 sampai +2 hari)
        $schedules = [
            [
                'menu_name' => 'Paket Sate Ayam Lilit & Sayur Daun Kelor',
                'sppg_code' => $sppgCode,
                'date' => date('Y-m-d', strtotime('-2 days')),
                'cycle' => 'Minggu '.date('W'),
                'cutoff_time' => '20:00:00',
            ],
            [
                'menu_name' => 'Paket Ikan Bakar Sambal Dabu & Cah Kangkung',
                'sppg_code' => $sppgCode,
                'date' => date('Y-m-d', strtotime('-1 day')),
                'cycle' => 'Minggu '.date('W'),
                'cutoff_time' => '20:00:00',
            ],
            [
                'menu_name' => 'Paket Ayam Panggang Madu & Capcay Sayur',
                'sppg_code' => $sppgCode,
                'date' => $today,
                'cycle' => 'Minggu '.date('W'),
                'cutoff_time' => '20:00:00',
            ],
            [
                'menu_name' => 'Paket Rolade Daging Sapi & Sup Sayur Bening',
                'sppg_code' => $sppgCode,
                'date' => date('Y-m-d', strtotime('+1 day')),
                'cycle' => 'Minggu '.date('W'),
                'cutoff_time' => '20:00:00',
            ],
            [
                'menu_name' => 'Paket Sate Ayam Lilit & Sayur Daun Kelor',
                'sppg_code' => $sppgCode,
                'date' => date('Y-m-d', strtotime('+2 days')),
                'cycle' => 'Minggu '.date('W'),
                'cutoff_time' => '20:00:00',
            ],
        ];

        foreach ($schedules as $schedule) {
            $menuId = $menuMap[$schedule['menu_name']] ?? null;
            $sppgId = $sppgMap[$schedule['sppg_code']] ?? null;

            if (! $menuId || ! $sppgId) {
                continue;
            }

            DB::table('menu_schedules')->updateOrInsert(
                [
                    'sppg_id' => $sppgId,
                    'date' => $schedule['date'],
                    'menu_id' => $menuId,
                ],
                [
                    'cycle' => $schedule['cycle'],
                    'cutoff_time' => $schedule['cutoff_time'],
                ]
            );
        }
    }
}
