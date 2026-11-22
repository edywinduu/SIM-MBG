<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sppgMap = DB::table('sppg_units')->pluck('id', 'code');

        $schools = [
            [
                'npsn' => '50103120',
                'name' => 'SMP Negeri 1 Denpasar',
                'education_level' => 'SMP',
                'district' => 'Denpasar Barat',
                'city' => 'Kota Denpasar',
                'address' => 'Jl. Surapati No. 2, Dangin Puri',
                'pic_name' => 'I Nyoman Sukra',
                'sppg_code' => 'SPPG-DPS-01',
                'status' => 'active',
            ],
            [
                'npsn' => '50103121',
                'name' => 'SMP Negeri 2 Denpasar',
                'education_level' => 'SMP',
                'district' => 'Denpasar Timur',
                'city' => 'Kota Denpasar',
                'address' => 'Jl. Kamboja No. 4, Dangin Puri Kangin',
                'pic_name' => 'Ni Ketut Suartini',
                'sppg_code' => 'SPPG-DPS-01',
                'status' => 'active',
            ],
            [
                'npsn' => '50102044',
                'name' => 'SMP Negeri 1 Gianyar',
                'education_level' => 'SMP',
                'district' => 'Kota Gianyar',
                'city' => 'Kabupaten Gianyar',
                'address' => 'Jl. Raya Gianyar No. 10',
                'pic_name' => 'Wayan Sudarta',
                'sppg_code' => 'SPPG-DPS-01',
                'status' => 'active',
            ],
            [
                'npsn' => '50101980',
                'name' => 'SMP Negeri 1 Klungkung',
                'education_level' => 'SMP',
                'district' => 'Semarapura',
                'city' => 'Kabupaten Klungkung',
                'address' => 'Jl. Untung Surapati No. 8',
                'pic_name' => 'Luh Putu Aryani',
                'sppg_code' => 'SPPG-DPS-01',
                'status' => 'active',
            ],
            [
                'npsn' => '50101567',
                'name' => 'SMP Negeri 1 Kuta',
                'education_level' => 'SMP',
                'district' => 'Kuta Selatan',
                'city' => 'Kabupaten Badung',
                'address' => 'Jl. Kediri No. 7, Tuban, Kuta',
                'pic_name' => 'Gede Wira Pratama',
                'sppg_code' => 'SPPG-BDG-02',
                'status' => 'active',
            ],
            [
                'npsn' => '50103441',
                'name' => 'SD Negeri 1 Renon',
                'education_level' => 'SD',
                'district' => 'Denpasar Selatan',
                'city' => 'Kota Denpasar',
                'address' => 'Jl. Tukad Balian No. 15, Renon',
                'pic_name' => 'I Made Sujana',
                'sppg_code' => 'SPPG-DPS-03',
                'status' => 'pending',
            ],
        ];

        foreach ($schools as $school) {
            $sppgId = $sppgMap[$school['sppg_code']] ?? null;

            if (! $sppgId) {
                continue;
            }

            DB::table('schools')->updateOrInsert(
                ['npsn' => $school['npsn']],
                [
                    'name' => $school['name'],
                    'education_level' => $school['education_level'],
                    'district' => $school['district'],
                    'city' => $school['city'],
                    'address' => $school['address'],
                    'pic_name' => $school['pic_name'],
                    'sppg_id' => $sppgId,
                    'status' => $school['status'],
                ]
            );
        }
    }
}
