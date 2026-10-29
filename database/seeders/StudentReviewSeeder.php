<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentMap = DB::table('students')->pluck('id', 'nisn');
        $menuMap = DB::table('menus')->pluck('id', 'name');

        $reviews = [
            [
                'student_nisn' => '0081234567',
                'menu_name' => 'Paket Ayam Panggang Madu & Capcay Sayur',
                'rating' => 5,
                'comment' => 'Delicious and well portioned!',
            ],
            [
                'student_nisn' => '0081234567',
                'menu_name' => 'Paket Rolade Daging Sapi & Sup Sayur Bening',
                'rating' => 4,
                'comment' => 'Tender beef roll and fresh soup',
            ],
            [
                'student_nisn' => '0081234568',
                'menu_name' => 'Paket Ikan Bakar Sambal Dabu & Cah Kangkung',
                'rating' => 5,
                'comment' => 'Safe for my allergy and healthy',
            ],
            [
                'student_nisn' => '0081234569',
                'menu_name' => 'Paket Ikan Bakar Sambal Dabu & Cah Kangkung',
                'rating' => 3,
                'comment' => 'A bit mild in taste',
            ],
        ];

        foreach ($reviews as $review) {
            $studentId = $studentMap[$review['student_nisn']] ?? null;
            $menuId = $menuMap[$review['menu_name']] ?? null;

            if (! $studentId || ! $menuId) {
                continue;
            }

            DB::table('student_reviews')->updateOrInsert(
                [
                    'student_id' => $studentId,
                    'menu_id' => $menuId,
                ],
                [
                    'rating' => $review['rating'],
                    'comment' => $review['comment'],
                    'created_at' => now(),
                ]
            );
        }
    }
}
