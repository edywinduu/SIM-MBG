<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with SIM-MBG initial data.
     */
    public function run(): void
    {
        $this->call([
            // 1. Akun & Master Data Induk (tanpa ketergantungan foreign key)
            AdminSeeder::class,
            SppgSeeder::class,
            UserSeeder::class,
            SppgUnitSeeder::class,
            AllergenSeeder::class,
            MenuSeeder::class,

            // 2. Entitas Tingkat 1 (bergantung pada users / sppg_units)
            SchoolSeeder::class,
            SppgStaffSeeder::class,

            // 3. Entitas Tingkat 2 (bergantung pada schools, users, menus, allergens, sppg_units)
            StudentSeeder::class,
            MenuAllergenSeeder::class,
            MenuScheduleSeeder::class,
            DistributionSeeder::class,
            ActivityLogSeeder::class,
            AnnouncementSeeder::class,
            SchoolFeedbackSeeder::class,
            KitchenAuditSeeder::class,

            // 4. Entitas Tingkat 3 (bergantung pada students, sppg_staff, menu_schedules, menus)
            StudentAllergySeeder::class,
            MenuSelectionSeeder::class,
            PickupSeeder::class,
            StudentReviewSeeder::class,
        ]);
    }
}
