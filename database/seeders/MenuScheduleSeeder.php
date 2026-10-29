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
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $schedules = [
            [
                'menu_name' => 'Paket Ayam Panggang Madu & Capcay Sayur',
                'sppg_code' => 'SPPG-DPS-01',
                'date' => $today,
                'cycle' => 'Oktober 2026 - Minggu 1',
                'cutoff_time' => '20:00:00',
            ],
            [
                'menu_name' => 'Paket Rolade Daging Sapi & Sup Sayur Bening',
                'sppg_code' => 'SPPG-DPS-01',
                'date' => $tomorrow,
                'cycle' => 'Oktober 2026 - Minggu 1',
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
