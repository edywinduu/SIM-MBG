<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuAllergenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menuMap = DB::table('menus')->pluck('id', 'name');
        $allergenMap = DB::table('allergens')->pluck('id', 'name');

        $menuAllergens = [
            [
                'menu_name' => 'Paket Ayam Panggang Madu & Capcay Sayur',
                'allergen_name' => 'Kacang',
                'type' => 'free_from',
            ],
            [
                'menu_name' => 'Paket Ayam Panggang Madu & Capcay Sayur',
                'allergen_name' => 'Laktosa',
                'type' => 'contains',
            ],
            [
                'menu_name' => 'Paket Ikan Bakar Sambal Dabu & Cah Kangkung',
                'allergen_name' => 'Seafood',
                'type' => 'contains',
            ],
            [
                'menu_name' => 'Paket Ikan Bakar Sambal Dabu & Cah Kangkung',
                'allergen_name' => 'Kacang',
                'type' => 'free_from',
            ],
            [
                'menu_name' => 'Paket Rolade Daging Sapi & Sup Sayur Bening',
                'allergen_name' => 'Seafood',
                'type' => 'free_from',
            ],
            [
                'menu_name' => 'Paket Rolade Daging Sapi & Sup Sayur Bening',
                'allergen_name' => 'Telur',
                'type' => 'free_from',
            ],
            [
                'menu_name' => 'Paket Sate Ayam Lilit & Sayur Daun Kelor',
                'allergen_name' => 'Gluten',
                'type' => 'free_from',
            ],
            [
                'menu_name' => 'Paket Siaga Cadangan: Ayam Opor Suwir',
                'allergen_name' => 'Telur',
                'type' => 'free_from',
            ],
        ];

        foreach ($menuAllergens as $item) {
            $menuId = $menuMap[$item['menu_name']] ?? null;
            $allergenId = $allergenMap[$item['allergen_name']] ?? null;

            if (! $menuId || ! $allergenId) {
                continue;
            }

            DB::table('menu_allergens')->updateOrInsert(
                [
                    'menu_id' => $menuId,
                    'allergen_id' => $allergenId,
                ],
                [
                    'type' => $item['type'],
                ]
            );
        }
    }
}
