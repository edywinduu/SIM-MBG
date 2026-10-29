<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KitchenAuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sppgMap = DB::table('sppg_units')->pluck('id', 'code');
        $today = date('Y-m-d');

        $audits = [
            [
                'sppg_code' => 'SPPG-DPS-01',
                'audit_date' => $today,
                'audit_type' => 'hygiene',
                'result' => 'Sanitary & Food Hygiene Certified',
                'certificate_file' => 'hygiene-lab-cert.pdf',
            ],
        ];

        foreach ($audits as $audit) {
            $sppgId = $sppgMap[$audit['sppg_code']] ?? null;

            if (! $sppgId) {
                continue;
            }

            DB::table('kitchen_audits')->updateOrInsert(
                [
                    'sppg_id' => $sppgId,
                    'audit_date' => $audit['audit_date'],
                    'audit_type' => $audit['audit_type'],
                ],
                [
                    'result' => $audit['result'],
                    'certificate_file' => $audit['certificate_file'],
                ]
            );
        }
    }
}
