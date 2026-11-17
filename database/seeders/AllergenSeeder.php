<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AllergenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allergens = [
            ['name' => 'Kacang'],
            ['name' => 'Seafood'],
            ['name' => 'Telur'],
            ['name' => 'Laktosa'],
            ['name' => 'Gluten'],
            ['name' => 'Vegetarian'],
        ];

        foreach ($allergens as $allergen) {
            DB::table('allergens')->updateOrInsert(
                ['name' => $allergen['name']],
                $allergen
            );
        }
    }
}
