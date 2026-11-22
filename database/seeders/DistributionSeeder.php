<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistributionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sppgMap = DB::table('sppg_units')->pluck('id', 'code');
        $schoolMap = DB::table('schools')->pluck('id', 'npsn');

        $today = date('Y-m-d');

        $distributions = [
            [
                'sppg_code' => 'SPPG-DPS-01',
                'school_npsn' => '50103120',
                'date' => $today,
                'portion_count' => 542,
                'eta_start' => '09:00:00',
                'eta_end' => '09:30:00',
                'status' => 'delivered',
                'fleet' => 'B-01',
            ],
            [
                'sppg_code' => 'SPPG-DPS-01',
                'school_npsn' => '50103121',
                'date' => $today,
                'portion_count' => 765,
                'eta_start' => '09:15:00',
                'eta_end' => '09:45:00',
                'status' => 'in_transit',
                'fleet' => 'B-02',
            ],
            [
                'sppg_code' => 'SPPG-DPS-01',
                'school_npsn' => '50102044',
                'date' => $today,
                'portion_count' => 480,
                'eta_start' => '09:30:00',
                'eta_end' => '10:00:00',
                'status' => 'in_transit',
                'fleet' => 'B-04',
            ],
            [
                'sppg_code' => 'SPPG-DPS-01',
                'school_npsn' => '50101980',
                'date' => $today,
                'portion_count' => 415,
                'eta_start' => '10:00:00',
                'eta_end' => '10:30:00',
                'status' => 'pending',
                'fleet' => 'B-05',
            ],
            [
                'sppg_code' => 'SPPG-BDG-02',
                'school_npsn' => '50101567',
                'date' => $today,
                'portion_count' => 738,
                'eta_start' => '09:00:00',
                'eta_end' => '09:30:00',
                'status' => 'delivered',
                'fleet' => 'B-03',
            ],
        ];

        foreach ($distributions as $distribution) {
            $sppgId = $sppgMap[$distribution['sppg_code']] ?? null;
            $schoolId = $schoolMap[$distribution['school_npsn']] ?? null;

            if (! $sppgId || ! $schoolId) {
                continue;
            }

            DB::table('distributions')->updateOrInsert(
                [
                    'sppg_id' => $sppgId,
                    'school_id' => $schoolId,
                    'date' => $distribution['date'],
                ],
                [
                    'portion_count' => $distribution['portion_count'],
                    'eta_start' => $distribution['eta_start'],
                    'eta_end' => $distribution['eta_end'],
                    'status' => $distribution['status'],
                    'fleet' => $distribution['fleet'],
                ]
            );
        }
    }
}
