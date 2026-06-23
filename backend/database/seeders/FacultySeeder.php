<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $faculties = [
            ['name' => 'Fakultas Tarbiyah', 'code' => 'FT'],
            ['name' => 'Fakultas Syariah', 'code' => 'FS'],
            ['name' => 'Fakultas Adab', 'code' => 'FA'],
            ['name' => 'Fakultas Dakwah', 'code' => 'FD'],
        ];

        foreach ($faculties as $faculty) {
            Faculty::updateOrCreate(
                ['code' => $faculty['code']],
                ['name' => $faculty['name']],
            );
        }
    }
}
