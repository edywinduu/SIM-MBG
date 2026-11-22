<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolFeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolMap = DB::table('schools')->pluck('id', 'npsn');

        $feedbacks = [
            [
                'school_npsn' => '50103120',
                'sender_name' => 'I Nyoman Sukra',
                'position' => 'UKS & Nutrition Coordinator',
                'score' => 5.0,
                'content' => 'Students were very enthusiastic, packaging is hygienic and served warm.',
                'container_temperature' => 'Warm (58 C)',
                'waste_percentage' => 0.8,
                'status' => 'very_satisfied',
                'is_verified' => 1,
                'kitchen_response' => null,
            ],
            [
                'school_npsn' => '50103121',
                'sender_name' => 'Ni Ketut Suartini',
                'position' => 'Distribution Supervisor',
                'score' => 4.8,
                'content' => 'Allergen tags are very clear, avoiding any distribution errors.',
                'container_temperature' => 'Warm (56 C)',
                'waste_percentage' => 1.5,
                'status' => 'very_satisfied',
                'is_verified' => 1,
                'kitchen_response' => null,
            ],
            [
                'school_npsn' => '50101980',
                'sender_name' => 'Luh Putu Aryani',
                'position' => 'School Committee',
                'score' => 3.8,
                'content' => 'Distribution arrived close to break time, please arrive 15 minutes earlier.',
                'container_temperature' => 'Warm (54 C)',
                'waste_percentage' => 6.2,
                'status' => 'needs_evaluation',
                'is_verified' => 0,
                'kitchen_response' => null,
            ],
        ];

        foreach ($feedbacks as $feedback) {
            $schoolId = $schoolMap[$feedback['school_npsn']] ?? null;

            if (! $schoolId) {
                continue;
            }

            DB::table('school_feedbacks')->updateOrInsert(
                [
                    'school_id' => $schoolId,
                    'sender_name' => $feedback['sender_name'],
                ],
                [
                    'position' => $feedback['position'],
                    'score' => $feedback['score'],
                    'content' => $feedback['content'],
                    'container_temperature' => $feedback['container_temperature'],
                    'waste_percentage' => $feedback['waste_percentage'],
                    'status' => $feedback['status'],
                    'is_verified' => $feedback['is_verified'],
                    'kitchen_response' => $feedback['kitchen_response'],
                    'created_at' => now(),
                ]
            );
        }
    }
}
