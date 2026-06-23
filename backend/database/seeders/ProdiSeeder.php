<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\Prodi;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    public function run(): void
    {
        $prodis = [
            // Urutan dan nilai kode/alias mengikuti data sumber prodi UII Dalwa.
            ['faculty_code' => 'FS', 'name' => 'Ekonomi Syariah', 'code' => '60202', 'alias' => 'ESY', 'strata' => 'S1'],
            ['faculty_code' => 'FD', 'name' => 'Bimbingan Dan Konseling Islam', 'code' => '70232', 'alias' => 'BKI', 'strata' => 'S1'],
            ['faculty_code' => 'FD', 'name' => 'Komunikasi dan Penyiaran Islam', 'code' => '70233', 'alias' => 'KPI', 'strata' => 'S1'],
            ['faculty_code' => 'FS', 'name' => 'Hukum Keluarga Islam (Ahwal Al Syakhshiyah)', 'code' => '74230', 'alias' => 'AS-HK', 'strata' => 'S1'],
            ['faculty_code' => 'FA', 'name' => 'Sejarah Peradaban Islam', 'code' => '80230', 'alias' => 'SKI', 'strata' => 'S1'],
            ['faculty_code' => 'FT', 'name' => 'Pendidikan Agama Islam', 'code' => '86208', 'alias' => 'PAI', 'strata' => 'S1'],
            ['faculty_code' => 'FT', 'name' => 'Manajemen Pendidikan Islam', 'code' => '86231', 'alias' => 'MPI', 'strata' => 'S1'],
            ['faculty_code' => 'FT', 'name' => 'Pendidikan Bahasa Arab', 'code' => '88204', 'alias' => 'PBA', 'strata' => 'S1'],
            ['faculty_code' => 'FT', 'name' => 'Pendidikan Bahasa Arab S2', 'code' => '88104', 'alias' => 'PBAS2', 'strata' => 'S2'],
            ['faculty_code' => 'FT', 'name' => 'Manajemen Pendidikan Islam S2', 'code' => '86131', 'alias' => 'MPIS2', 'strata' => 'S2'],
            ['faculty_code' => 'FT', 'name' => 'Pendidikan Agama Islam S3', 'code' => '86008', 'alias' => 'PAIS3', 'strata' => 'S3'],
            ['faculty_code' => 'FD', 'name' => 'Manajemen Haji dan Umroh', 'code' => '70234', 'alias' => 'MHU', 'strata' => 'S1'],
            ['faculty_code' => 'FS', 'name' => "Ilmu Al-Qur'an dan Tafsir", 'code' => '80231', 'alias' => 'IAI', 'strata' => 'S1'],
            ['faculty_code' => 'FA', 'name' => 'Sastra Arab', 'code' => '80232', 'alias' => 'SA', 'strata' => 'S1'],
            ['faculty_code' => 'FS', 'name' => 'Magister Hukum Keluarga Islam', 'code' => '88888', 'alias' => 'MHKI', 'strata' => 'S2'],
        ];

        $facultyIds = Faculty::whereIn('code', ['FT', 'FS', 'FA', 'FD'])
            ->pluck('id', 'code');

        foreach ($prodis as $prodi) {
            $facultyCode = $prodi['faculty_code'];
            unset($prodi['faculty_code']);
            $facultyId = $facultyIds->get($facultyCode);

            if ($facultyId === null) {
                throw new \RuntimeException("Faculty with code {$facultyCode} was not seeded.");
            }

            $existingProdi = Prodi::query()
                ->where('code', $prodi['code'])
                ->orWhere('alias', $prodi['alias'])
                ->orWhere('code', $prodi['alias'])
                ->first();

            $attributes = [
                ...$prodi,
                'faculty_id' => $facultyId,
            ];

            $existingProdi
                ? $existingProdi->update($attributes)
                : Prodi::create($attributes);
        }
    }
}
