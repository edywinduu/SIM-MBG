<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SppgUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sppgUnits = [
            [
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
                'code' => 'SPPG-BDG-04',
                'name' => 'SPPG Badung Utara 04',
                'region' => 'Kab. Badung',
                'address' => 'Jl. Raya Mengwi No. 102, Mengwi, Badung',
                'capacity' => 2200,
                'contact_person' => 'I Nyoman Suweta',
                'phone' => '081234567894',
                'status' => 'maintenance',
            ],
        ];

        foreach ($sppgUnits as $unit) {
            DB::table('sppg_units')->updateOrInsert(
                ['code' => $unit['code']],
                $unit
            );
        }
    }
}
