<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userMap = DB::table('users')->pluck('id', 'username');
        $schoolMap = DB::table('schools')->pluck('id', 'npsn');

        $students = [
            [
                'username' => 'siswa',
                'nisn' => '0081234567',
                'name' => 'Alya MBG',
                'school_npsn' => '50103120',
                'class_name' => 'AKU IPA',
                'recipient_status' => 'active',
            ],
            [
                'username' => 'aditya',
                'nisn' => '0081234568',
                'name' => 'Komang Aditya',
                'school_npsn' => '50103120',
                'class_name' => 'VIII C',
                'recipient_status' => 'active',
            ],
            [
                'username' => 'gita',
                'nisn' => '0081234569',
                'name' => 'Ni Putu Gita Dewi',
                'school_npsn' => '50103121',
                'class_name' => 'VII B',
                'recipient_status' => 'active',
            ],
        ];

        foreach ($students as $student) {
            $userId = $userMap[$student['username']] ?? null;
            $schoolId = $schoolMap[$student['school_npsn']] ?? null;

            if (! $schoolId) {
                continue;
            }

            DB::table('students')->updateOrInsert(
                ['nisn' => $student['nisn']],
                [
                    'user_id' => $userId,
                    'name' => $student['name'],
                    'school_id' => $schoolId,
                    'class_name' => $student['class_name'],
                    'recipient_status' => $student['recipient_status'],
                ]
            );
        }
    }
}
