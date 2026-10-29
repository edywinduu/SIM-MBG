<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with SIM-MBG initial data.
     */
    public function run(): void
    {
        // 1. Users
        $defaultPasswordHash = Hash::make('password');

        DB::table('users')->insert([
            [
                'id' => 1,
                'username' => 'admin',
                'email' => 'admin@mbg.go.id',
                'password_hash' => $defaultPasswordHash,
                'full_name' => 'Atmind Administrator',
                'role' => 'admin',
                'institution' => 'Dinas Pendidikan & Gizi Provinsi',
                'status' => 'active',
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'username' => 'pgw.alya',
                'email' => 'sppg.dps01@mbg.go.id',
                'password_hash' => $defaultPasswordHash,
                'full_name' => 'Pgw Alya Sudarma',
                'role' => 'sppg',
                'institution' => 'SPPG Denpasar Barat 01',
                'status' => 'active',
                'created_at' => now(),
            ],
            [
                'id' => 3,
                'username' => 'alya',
                'email' => 'alya@sekolah.id',
                'password_hash' => $defaultPasswordHash,
                'full_name' => 'Alya MBG',
                'role' => 'student',
                'institution' => 'SMA Negeri 1 Denpasar',
                'status' => 'active',
                'created_at' => now(),
            ],
            [
                'id' => 4,
                'username' => 'aditya',
                'email' => 'aditya@sekolah.id',
                'password_hash' => $defaultPasswordHash,
                'full_name' => 'Komang Aditya',
                'role' => 'student',
                'institution' => 'SMP Negeri 1 Denpasar',
                'status' => 'active',
                'created_at' => now(),
            ],
            [
                'id' => 5,
                'username' => 'gita',
                'email' => 'gita@sekolah.id',
                'password_hash' => $defaultPasswordHash,
                'full_name' => 'Ni Putu Gita Dewi',
                'role' => 'student',
                'institution' => 'SMP Negeri 2 Denpasar',
                'status' => 'active',
                'created_at' => now(),
            ],
        ]);

        // 2. SPPG Units
        DB::table('sppg_units')->insert([
            [
                'id' => 1,
                'code' => 'SPPG-DPS-01',
                'name' => 'SPPG Denpasar Barat 01',
                'region' => 'Kota Denpasar',
                'address' => 'Jl. Gunung Agung No. 45, Pemecutan, Denpasar Barat',
                'capacity' => 3500,
                'contact_person' => 'I Wayan Sudarma',
                'phone' => '081234567891',
                'status' => 'active',
            ],
            [
                'id' => 2,
                'code' => 'SPPG-BDG-02',
                'name' => 'SPPG Badung Selatan 02',
                'region' => 'Kab. Badung',
                'address' => 'Jl. Bypass Ngurah Rai No. 12, Tuban, Kuta',
                'capacity' => 2800,
                'contact_person' => 'Ni Made Rai',
                'phone' => '081234567892',
                'status' => 'active',
            ],
            [
                'id' => 3,
                'code' => 'SPPG-DPS-03',
                'name' => 'SPPG Denpasar Selatan 03',
                'region' => 'Kota Denpasar',
                'address' => 'Jl. Tukad Yeh Aya No. 88, Panjer, Denpasar Selatan',
                'capacity' => 3200,
                'contact_person' => 'I Ketut Gede',
                'phone' => '081234567893',
                'status' => 'active',
            ],
            [
                'id' => 4,
                'code' => 'SPPG-BDG-04',
                'name' => 'SPPG Badung Utara 04',
                'region' => 'Kab. Badung',
                'address' => 'Jl. Raya Mengwi No. 102, Mengwi, Badung',
                'capacity' => 2200,
                'contact_person' => 'I Nyoman Suweta',
                'phone' => '081234567894',
                'status' => 'maintenance',
            ],
        ]);

        // 3. Schools
        DB::table('schools')->insert([
            [
                'id' => 1,
                'npsn' => '50103120',
                'name' => 'SMP Negeri 1 Denpasar',
                'education_level' => 'SMP',
                'district' => 'Denpasar Barat',
                'city' => 'Kota Denpasar',
                'address' => 'Jl. Surapati No. 2, Dangin Puri',
                'pic_name' => 'I Nyoman Sukra',
                'sppg_id' => 1,
                'status' => 'active',
            ],
            [
                'id' => 2,
                'npsn' => '50103121',
                'name' => 'SMP Negeri 2 Denpasar',
                'education_level' => 'SMP',
                'district' => 'Denpasar Timur',
                'city' => 'Kota Denpasar',
                'address' => 'Jl. Kamboja No. 4, Dangin Puri Kangin',
                'pic_name' => 'Ni Ketut Suartini',
                'sppg_id' => 1,
                'status' => 'active',
            ],
            [
                'id' => 3,
                'npsn' => '50102044',
                'name' => 'SMP Negeri 1 Gianyar',
                'education_level' => 'SMP',
                'district' => 'Kota Gianyar',
                'city' => 'Kabupaten Gianyar',
                'address' => 'Jl. Raya Gianyar No. 10',
                'pic_name' => 'Wayan Sudarta',
                'sppg_id' => 1,
                'status' => 'active',
            ],
            [
                'id' => 4,
                'npsn' => '50101980',
                'name' => 'SMP Negeri 1 Klungkung',
                'education_level' => 'SMP',
                'district' => 'Semarapura',
                'city' => 'Kabupaten Klungkung',
                'address' => 'Jl. Untung Surapati No. 8',
                'pic_name' => 'Luh Putu Aryani',
                'sppg_id' => 1,
                'status' => 'active',
            ],
            [
                'id' => 5,
                'npsn' => '50101567',
                'name' => 'SMP Negeri 1 Kuta',
                'education_level' => 'SMP',
                'district' => 'Kuta Selatan',
                'city' => 'Kabupaten Badung',
                'address' => 'Jl. Kediri No. 7, Tuban, Kuta',
                'pic_name' => 'Gede Wira Pratama',
                'sppg_id' => 2,
                'status' => 'active',
            ],
            [
                'id' => 6,
                'npsn' => '50103441',
                'name' => 'SD Negeri 1 Renon',
                'education_level' => 'SD',
                'district' => 'Denpasar Selatan',
                'city' => 'Kota Denpasar',
                'address' => 'Jl. Tukad Balian No. 15, Renon',
                'pic_name' => 'I Made Sujana',
                'sppg_id' => 3,
                'status' => 'pending',
            ],
        ]);

        // 4. Students
        DB::table('students')->insert([
            [
                'id' => 1,
                'user_id' => 3,
                'nisn' => '0081234567',
                'name' => 'Alya MBG',
                'school_id' => 1,
                'class_name' => 'XI IPA 1',
                'recipient_status' => 'active',
            ],
            [
                'id' => 2,
                'user_id' => 4,
                'nisn' => '0081234568',
                'name' => 'Komang Aditya',
                'school_id' => 1,
                'class_name' => 'VIII C',
                'recipient_status' => 'active',
            ],
            [
                'id' => 3,
                'user_id' => 5,
                'nisn' => '0081234569',
                'name' => 'Ni Putu Gita Dewi',
                'school_id' => 2,
                'class_name' => 'VII B',
                'recipient_status' => 'active',
            ],
        ]);

        // 5. SPPG Staff
        DB::table('sppg_staff')->insert([
            [
                'id' => 1,
                'user_id' => 2,
                'name' => 'Pgw Alya Sudarma',
                'position' => 'Pegawai SPPG',
                'sppg_id' => 1,
            ],
        ]);

        // 6. Allergens
        DB::table('allergens')->insert([
            ['id' => 1, 'name' => 'Kacang'],
            ['id' => 2, 'name' => 'Seafood'],
            ['id' => 3, 'name' => 'Telur'],
            ['id' => 4, 'name' => 'Laktosa'],
            ['id' => 5, 'name' => 'Gluten'],
        ]);

        // 7. Student Allergies
        DB::table('student_allergies')->insert([
            [
                'student_id' => 1,
                'allergen_id' => 1,
                'medical_notes' => 'Sensitif produk olahan kacang tanah',
                'verification_status' => 'active',
                'verified_by' => 1,
            ],
            [
                'student_id' => 1,
                'allergen_id' => 2,
                'medical_notes' => 'Alergi udang laut',
                'verification_status' => 'active',
                'verified_by' => 1,
            ],
            [
                'student_id' => 2,
                'allergen_id' => 3,
                'medical_notes' => 'Alergi telur ayam (gatal)',
                'verification_status' => 'active',
                'verified_by' => 1,
            ],
            [
                'student_id' => 3,
                'allergen_id' => 4,
                'medical_notes' => 'Alergi susu sapi & olahan',
                'verification_status' => 'medical_review',
                'verified_by' => null,
            ],
        ]);

        // 8. Menus
        DB::table('menus')->insert([
            [
                'id' => 1,
                'name' => 'Paket Ayam Panggang Madu & Capcay Sayur',
                'description' => 'Nasi putih pulen, ayam panggang saus madu, capcay wortel brokoli jagung, semangka potong',
                'category' => 'regular',
                'calories' => 620,
                'carbs' => 65,
                'protein' => 32,
                'fat' => 18,
                'fiber' => 8.0,
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Paket Ikan Bakar Sambal Dabu & Cah Kangkung',
                'description' => 'Nasi merah organik, fillet ikan kembung bakar rempah, sambal dabu segar, pisang ambon',
                'category' => 'special',
                'calories' => 590,
                'carbs' => 60,
                'protein' => 34,
                'fat' => 14,
                'fiber' => 7.0,
                'is_active' => 1,
            ],
            [
                'id' => 3,
                'name' => 'Paket Rolade Daging Sapi & Sup Sayur Bening',
                'description' => 'Nasi putih, rolade daging sapi saus gurih, sup bening wortel buncis, jeruk manis',
                'category' => 'regular',
                'calories' => 640,
                'carbs' => 70,
                'protein' => 28,
                'fat' => 20,
                'fiber' => 6.0,
                'is_active' => 1,
            ],
            [
                'id' => 4,
                'name' => 'Paket Sate Ayam Lilit & Sayur Daun Kelor',
                'description' => 'Nasi jagung, sate ayam lilit, sup kelor, tempe orek, buah pepaya',
                'category' => 'special',
                'calories' => 605,
                'carbs' => 70,
                'protein' => 25,
                'fat' => 15,
                'fiber' => 7.1,
                'is_active' => 1,
            ],
            [
                'id' => 5,
                'name' => 'Paket Siaga Cadangan: Ayam Opor Suwir',
                'description' => 'Nasi pulen, ayam opor suwir, tahu tempe bacem, kuah gurih bening',
                'category' => 'backup',
                'calories' => 610,
                'carbs' => 76,
                'protein' => 24,
                'fat' => 15,
                'fiber' => 5.5,
                'is_active' => 1,
            ],
        ]);

        // 9. Menu Allergens
        DB::table('menu_allergens')->insert([
            ['menu_id' => 1, 'allergen_id' => 1, 'type' => 'free_from'],
            ['menu_id' => 1, 'allergen_id' => 4, 'type' => 'contains'],
            ['menu_id' => 2, 'allergen_id' => 2, 'type' => 'contains'],
            ['menu_id' => 2, 'allergen_id' => 1, 'type' => 'free_from'],
            ['menu_id' => 3, 'allergen_id' => 2, 'type' => 'free_from'],
            ['menu_id' => 3, 'allergen_id' => 3, 'type' => 'free_from'],
            ['menu_id' => 4, 'allergen_id' => 5, 'type' => 'free_from'],
            ['menu_id' => 5, 'allergen_id' => 3, 'type' => 'free_from'],
        ]);

        // 10. Menu Schedules
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        DB::table('menu_schedules')->insert([
            [
                'id' => 1,
                'menu_id' => 1,
                'sppg_id' => 1,
                'date' => $today,
                'cycle' => 'Oktober 2026 - Minggu 1',
                'cutoff_time' => '20:00:00',
            ],
            [
                'id' => 2,
                'menu_id' => 3,
                'sppg_id' => 1,
                'date' => $tomorrow,
                'cycle' => 'Oktober 2026 - Minggu 1',
                'cutoff_time' => '20:00:00',
            ],
        ]);

        // 11. Menu Selections
        DB::table('menu_selections')->insert([
            [
                'id' => 1,
                'student_id' => 1,
                'schedule_id' => 1,
                'menu_id' => 1,
                'consumption_status' => 'active',
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'student_id' => 2,
                'schedule_id' => 1,
                'menu_id' => 2,
                'consumption_status' => 'active',
                'created_at' => now(),
            ],
            [
                'id' => 3,
                'student_id' => 3,
                'schedule_id' => 1,
                'menu_id' => 2,
                'consumption_status' => 'medical_review',
                'created_at' => now(),
            ],
        ]);

        // 12. Pickups
        DB::table('pickups')->insert([
            [
                'id' => 1,
                'student_id' => 1,
                'date' => $today,
                'status' => 'pending',
                'pickup_time' => null,
            ],
            [
                'id' => 2,
                'student_id' => 1,
                'date' => $yesterday,
                'status' => 'picked_up',
                'pickup_time' => now()->subDay(),
            ],
            [
                'id' => 3,
                'student_id' => 2,
                'date' => $today,
                'status' => 'picked_up',
                'pickup_time' => now(),
            ],
        ]);

        // 13. Distributions
        DB::table('distributions')->insert([
            [
                'id' => 1,
                'sppg_id' => 1,
                'school_id' => 1,
                'date' => $today,
                'portion_count' => 542,
                'eta_start' => '09:00:00',
                'eta_end' => '09:30:00',
                'status' => 'delivered',
                'fleet' => 'B-01',
            ],
            [
                'id' => 2,
                'sppg_id' => 1,
                'school_id' => 2,
                'date' => $today,
                'portion_count' => 765,
                'eta_start' => '09:15:00',
                'eta_end' => '09:45:00',
                'status' => 'in_transit',
                'fleet' => 'B-02',
            ],
            [
                'id' => 3,
                'sppg_id' => 1,
                'school_id' => 3,
                'date' => $today,
                'portion_count' => 480,
                'eta_start' => '09:30:00',
                'eta_end' => '10:00:00',
                'status' => 'in_transit',
                'fleet' => 'B-04',
            ],
            [
                'id' => 4,
                'sppg_id' => 1,
                'school_id' => 4,
                'date' => $today,
                'portion_count' => 415,
                'eta_start' => '10:00:00',
                'eta_end' => '10:30:00',
                'status' => 'pending',
                'fleet' => 'B-05',
            ],
            [
                'id' => 5,
                'sppg_id' => 2,
                'school_id' => 5,
                'date' => $today,
                'portion_count' => 738,
                'eta_start' => '09:00:00',
                'eta_end' => '09:30:00',
                'status' => 'delivered',
                'fleet' => 'B-03',
            ],
        ]);

        // 14. Activity Logs
        DB::table('activity_logs')->insert([
            [
                'id' => 1,
                'sppg_id' => 1,
                'title' => 'Batch reception completed - SMP Negeri 1 Denpasar',
                'description' => '240 portions handed over to School Health Unit',
                'status' => 'Delivered',
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'sppg_id' => 1,
                'title' => 'Courier fleet heading to destination - SMP Negeri 2 Gianyar',
                'description' => '180 portions in thermal distribution boxes',
                'status' => 'In Transit',
                'created_at' => now(),
            ],
        ]);

        // 15. Announcements
        DB::table('announcements')->insert([
            [
                'id' => 1,
                'sppg_id' => 1,
                'title' => 'MBG Menu Distribution Schedule',
                'content' => 'October 2026 cycle is actively proceeding in compliance with National Nutrition Standards.',
                'created_at' => now(),
            ],
        ]);

        // 16. Student Reviews
        DB::table('student_reviews')->insert([
            [
                'id' => 1,
                'student_id' => 1,
                'menu_id' => 1,
                'rating' => 5,
                'comment' => 'Delicious and well portioned!',
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'student_id' => 1,
                'menu_id' => 3,
                'rating' => 4,
                'comment' => 'Tender beef roll and fresh soup',
                'created_at' => now(),
            ],
            [
                'id' => 3,
                'student_id' => 2,
                'menu_id' => 2,
                'rating' => 5,
                'comment' => 'Safe for my allergy and healthy',
                'created_at' => now(),
            ],
            [
                'id' => 4,
                'student_id' => 3,
                'menu_id' => 2,
                'rating' => 3,
                'comment' => 'A bit mild in taste',
                'created_at' => now(),
            ],
        ]);

        // 17. School Feedbacks
        DB::table('school_feedbacks')->insert([
            [
                'id' => 1,
                'school_id' => 1,
                'sender_name' => 'I Nyoman Sukra',
                'position' => 'UKS & Nutrition Coordinator',
                'score' => 5.0,
                'content' => 'Students were very enthusiastic, packaging is hygienic and served warm.',
                'container_temperature' => 'Warm (58 C)',
                'waste_percentage' => 0.8,
                'status' => 'very_satisfied',
                'is_verified' => 1,
                'kitchen_response' => null,
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'school_id' => 2,
                'sender_name' => 'Ni Ketut Suartini',
                'position' => 'Distribution Supervisor',
                'score' => 4.8,
                'content' => 'Allergen tags are very clear, avoiding any distribution errors.',
                'container_temperature' => 'Warm (56 C)',
                'waste_percentage' => 1.5,
                'status' => 'very_satisfied',
                'is_verified' => 1,
                'kitchen_response' => null,
                'created_at' => now(),
            ],
            [
                'id' => 3,
                'school_id' => 4,
                'sender_name' => 'Luh Putu Aryani',
                'position' => 'School Committee',
                'score' => 3.8,
                'content' => 'Distribution arrived close to break time, please arrive 15 minutes earlier.',
                'container_temperature' => 'Warm (54 C)',
                'waste_percentage' => 6.2,
                'status' => 'needs_evaluation',
                'is_verified' => 0,
                'kitchen_response' => null,
                'created_at' => now(),
            ],
        ]);

        // 18. Kitchen Audits
        DB::table('kitchen_audits')->insert([
            [
                'id' => 1,
                'sppg_id' => 1,
                'audit_date' => $today,
                'audit_type' => 'hygiene',
                'result' => 'Sanitary & Food Hygiene Certified',
                'certificate_file' => 'hygiene-lab-cert.pdf',
            ],
        ]);
    }
}
