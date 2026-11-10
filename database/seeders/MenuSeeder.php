<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            [
                'name' => 'Paket Ayam Panggang Madu & Capcay Sayur',
                'description' => 'Nasi putih pulen, ayam panggang saus madu, capcay wortel brokoli jagung, semangka potong',
                'category' => 'regular',
                'calories' => 620,
                'carbs' => 65,
                'protein' => 32,
                'fat' => 18,
                'fiber' => 8.0,
                'image' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80',
                'is_active' => 1,
            ],
            [
                'name' => 'Paket Ikan Bakar Sambal Dabu & Cah Kangkung',
                'description' => 'Nasi merah organik, fillet ikan kembung bakar rempah, sambal dabu segar, pisang ambon',
                'category' => 'special',
                'calories' => 590,
                'carbs' => 60,
                'protein' => 34,
                'fat' => 14,
                'fiber' => 7.0,
                'image' => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=800&q=80',
                'is_active' => 1,
            ],
            [
                'name' => 'Paket Rolade Daging Sapi & Sup Sayur Bening',
                'description' => 'Nasi putih, rolade daging sapi saus gurih, sup bening wortel buncis, jeruk manis',
                'category' => 'regular',
                'calories' => 640,
                'carbs' => 70,
                'protein' => 28,
                'fat' => 20,
                'fiber' => 6.0,
                'image' => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80',
                'is_active' => 1,
            ],
            [
                'name' => 'Paket Sate Ayam Lilit & Sayur Daun Kelor',
                'description' => 'Nasi jagung, sate ayam lilit, sup kelor, tempe orek, buah pepaya',
                'category' => 'special',
                'calories' => 605,
                'carbs' => 70,
                'protein' => 25,
                'fat' => 15,
                'fiber' => 7.1,
                'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?auto=format&fit=crop&w=800&q=80',
                'is_active' => 1,
            ],
            [
                'name' => 'Paket Siaga Cadangan: Ayam Opor Suwir',
                'description' => 'Nasi pulen, ayam opor suwir, tahu tempe bacem, kuah gurih bening',
                'category' => 'backup',
                'calories' => 610,
                'carbs' => 76,
                'protein' => 24,
                'fat' => 15,
                'fiber' => 5.5,
                'image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=800&q=80',
                'is_active' => 1,
            ],
        ];

        foreach ($menus as $menu) {
            DB::table('menus')->updateOrInsert(
                ['name' => $menu['name']],
                $menu
            );
        }
    }
}
