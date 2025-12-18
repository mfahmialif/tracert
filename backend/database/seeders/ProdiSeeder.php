<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\Prodi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    public function run(): void
    {
        $ft = Faculty::where('code', 'FT')->first();
        $fe = Faculty::where('code', 'FE')->first();
        $fik = Faculty::where('code', 'FIK')->first();

        $prodis = [
            ['faculty_id' => $ft->id, 'name' => 'Teknik Sipil', 'code' => 'TS', 'strata' => 'S1'],
            ['faculty_id' => $ft->id, 'name' => 'Teknik Mesin', 'code' => 'TM', 'strata' => 'S1'],
            ['faculty_id' => $fe->id, 'name' => 'Manajemen', 'code' => 'MJ', 'strata' => 'S1'],
            ['faculty_id' => $fe->id, 'name' => 'Akuntansi', 'code' => 'AK', 'strata' => 'S1'],
            ['faculty_id' => $fik->id, 'name' => 'Sistem Informasi', 'code' => 'SI', 'strata' => 'S1'],
            ['faculty_id' => $fik->id, 'name' => 'Teknik Informatika', 'code' => 'TI', 'strata' => 'S1'],
        ];

        foreach ($prodis as $prodi) {
            Prodi::create($prodi);
        }
    }
}
