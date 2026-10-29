<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sppgMap = DB::table('sppg_units')->pluck('id', 'code');

        $announcements = [
            [
                'sppg_code' => 'SPPG-DPS-01',
                'title' => 'MBG Menu Distribution Schedule',
                'content' => 'October 2026 cycle is actively proceeding in compliance with National Nutrition Standards.',
            ],
        ];

        foreach ($announcements as $announcement) {
            $sppgId = $sppgMap[$announcement['sppg_code']] ?? null;

            if (! $sppgId) {
                continue;
            }

            DB::table('announcements')->updateOrInsert(
                [
                    'sppg_id' => $sppgId,
                    'title' => $announcement['title'],
                ],
                [
                    'content' => $announcement['content'],
                    'created_at' => now(),
                ]
            );
        }
    }
}
