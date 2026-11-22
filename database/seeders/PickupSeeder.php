<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PickupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentMap = DB::table('students')->pluck('id', 'nisn');

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $pickups = [
            [
                'student_nisn' => '0081234567',
                'date' => $today,
                'status' => 'pending',
                'pickup_time' => null,
            ],
            [
                'student_nisn' => '0081234567',
                'date' => $yesterday,
                'status' => 'picked_up',
                'pickup_time' => now()->subDay(),
            ],
            [
                'student_nisn' => '0081234568',
                'date' => $today,
                'status' => 'picked_up',
                'pickup_time' => now(),
            ],
        ];

        foreach ($pickups as $pickup) {
            $studentId = $studentMap[$pickup['student_nisn']] ?? null;

            if (! $studentId) {
                continue;
            }

            DB::table('pickups')->updateOrInsert(
                [
                    'student_id' => $studentId,
                    'date' => $pickup['date'],
                ],
                [
                    'status' => $pickup['status'],
                    'pickup_time' => $pickup['pickup_time'],
                ]
            );
        }
    }
}
