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
                'title' => 'Serah terima batch selesai - SMP Negeri 1 Denpasar',
                'description' => '240 porsi diserahterimakan ke Unit Kesehatan Sekolah dengan suhu 65°C.',
                'status' => 'Selesai',
                'created_at' => now()->subMinutes(12),
            ],
            [
                'sppg_code' => 'SPPG-BDG-02',
                'title' => 'Armada kurir dalam perjalanan - SMP Negeri 2 Kuta',
                'description' => '180 porsi dalam thermal distribution box sedang dikirim.',
                'status' => 'Dalam Pengiriman',
                'created_at' => now()->subMinutes(35),
            ],
            [
                'sppg_code' => 'SPPG-DPS-03',
                'title' => 'Audit kebersihan dapur berkala',
                'description' => 'Pemeriksaan standar higienitas & kalibrasi suhu matang memenuhi syarat Grade A.',
                'status' => 'Audit Lulus',
                'created_at' => now()->subHours(2),
            ],
            [
                'sppg_code' => 'SPPG-DPS-01',
                'title' => 'Pembaruan kuota harian & alokasi bahan',
                'description' => 'Sinkronisasi 520 porsi menu Paket Sehat Nusantara selesai diproses.',
                'status' => 'Terverifikasi',
                'created_at' => now()->subHours(3),
            ],
            [
                'sppg_code' => 'SPPG-BDG-04',
                'title' => 'Pemeliharaan fasilitas pendingin',
                'description' => 'Perawatan rutin cold storage unit kompresor pendingin selesai dilakukan.',
                'status' => 'Pemeliharaan',
                'created_at' => now()->subHours(5),
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
                    'created_at' => $log['created_at'],
                ]
            );
        }
    }
}
