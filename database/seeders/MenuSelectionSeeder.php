<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSelectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentMap = DB::table('students')->pluck('id', 'nisn');
        $menuMap = DB::table('menus')->pluck('id', 'name');
        $sppgId = DB::table('sppg_units')->where('code', 'SPPG-DPS-01')->value('id');

        $today = date('Y-m-d');
        $todayScheduleId = DB::table('menu_schedules')
            ->where('sppg_id', $sppgId)
            ->where('date', $today)
            ->value('id');

        if (! $todayScheduleId) {
            return;
        }

        $selections = [
            [
                'student_nisn' => '0081234567',
                'menu_name' => 'Paket Ayam Panggang Madu & Capcay Sayur',
                'consumption_status' => 'active',
            ],
            [
                'student_nisn' => '0081234568',
                'menu_name' => 'Paket Ikan Bakar Sambal Dabu & Cah Kangkung',
                'consumption_status' => 'active',
            ],
            [
                'student_nisn' => '0081234569',
                'menu_name' => 'Paket Ikan Bakar Sambal Dabu & Cah Kangkung',
                'consumption_status' => 'medical_review',
            ],
        ];

        foreach ($selections as $selection) {
            $studentId = $studentMap[$selection['student_nisn']] ?? null;
            $menuId = $menuMap[$selection['menu_name']] ?? null;

            if (! $studentId || ! $menuId) {
                continue;
            }

            DB::table('menu_selections')->updateOrInsert(
                [
                    'student_id' => $studentId,
                    'schedule_id' => $todayScheduleId,
                ],
                [
                    'menu_id' => $menuId,
                    'consumption_status' => $selection['consumption_status'],
                    'created_at' => now(),
                ]
            );
        }
    }
}
