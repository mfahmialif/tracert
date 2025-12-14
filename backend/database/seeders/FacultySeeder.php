<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $faculties = [
            ['name' => 'Fakultas Teknik', 'code' => 'FT'],
            ['name' => 'Fakultas Ekonomi', 'code' => 'FE'],
            ['name' => 'Fakultas Ilmu Komputer', 'code' => 'FIK'],
        ];

        foreach ($faculties as $faculty) {
            Faculty::create($faculty);
        }
    }
}
