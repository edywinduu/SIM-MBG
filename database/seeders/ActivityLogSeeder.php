<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sppgMap = DB::table('sppg_units')->pluck('id', 'code');

        $logs = [
            [
                'sppg_code' => 'SPPG-DPS-01',
                'title' => 'Batch reception completed - SMP Negeri 1 Denpasar',
                'description' => '240 portions handed over to School Health Unit',
                'status' => 'Delivered',
            ],
            [
                'sppg_code' => 'SPPG-DPS-01',
                'title' => 'Courier fleet heading to destination - SMP Negeri 2 Gianyar',
                'description' => '180 portions in thermal distribution boxes',
                'status' => 'In Transit',
            ],
        ];

        foreach ($logs as $log) {
            $sppgId = $sppgMap[$log['sppg_code']] ?? null;

            if (! $sppgId) {
                continue;
            }

            DB::table('activity_logs')->updateOrInsert(
                [
                    'sppg_id' => $sppgId,
                    'title' => $log['title'],
                ],
                [
                    'description' => $log['description'],
                    'status' => $log['status'],
                    'created_at' => now(),
                ]
            );
        }
    }
}
