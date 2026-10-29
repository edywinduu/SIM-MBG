<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentAllergySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentMap = DB::table('students')->pluck('id', 'nisn');
        $allergenMap = DB::table('allergens')->pluck('id', 'name');
        $staffMap = DB::table('sppg_staff')
            ->join('users', 'sppg_staff.user_id', '=', 'users.id')
            ->pluck('sppg_staff.id', 'users.username');

        $studentAllergies = [
            [
                'student_nisn' => '0081234567',
                'allergen_name' => 'Kacang',
                'medical_notes' => 'Sensitif produk olahan kacang tanah',
                'verification_status' => 'active',
                'verified_by_username' => 'pgw.alya',
            ],
            [
                'student_nisn' => '0081234567',
                'allergen_name' => 'Seafood',
                'medical_notes' => 'Alergi udang laut',
                'verification_status' => 'active',
                'verified_by_username' => 'pgw.alya',
            ],
            [
                'student_nisn' => '0081234568',
                'allergen_name' => 'Telur',
                'medical_notes' => 'Alergi telur ayam (gatal)',
                'verification_status' => 'active',
                'verified_by_username' => 'pgw.alya',
            ],
            [
                'student_nisn' => '0081234569',
                'allergen_name' => 'Laktosa',
                'medical_notes' => 'Alergi susu sapi & olahan',
                'verification_status' => 'medical_review',
                'verified_by_username' => null,
            ],
        ];

        foreach ($studentAllergies as $allergy) {
            $studentId = $studentMap[$allergy['student_nisn']] ?? null;
            $allergenId = $allergenMap[$allergy['allergen_name']] ?? null;
            $verifiedBy = $allergy['verified_by_username']
                ? ($staffMap[$allergy['verified_by_username']] ?? null)
                : null;

            if (! $studentId || ! $allergenId) {
                continue;
            }

            DB::table('student_allergies')->updateOrInsert(
                [
                    'student_id' => $studentId,
                    'allergen_id' => $allergenId,
                ],
                [
                    'medical_notes' => $allergy['medical_notes'],
                    'verification_status' => $allergy['verification_status'],
                    'verified_by' => $verifiedBy,
                ]
            );
        }
    }
}
