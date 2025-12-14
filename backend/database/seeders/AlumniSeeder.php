<?php

namespace Database\Seeders;

use App\Models\Year;
use Illuminate\Database\Seeder;
use App\Models\Alumni;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AlumniSeeder extends Seeder
{
    public function run(): void
    {
        // Fetch valid IDs
        $years = Year::all();
        $prodis = \App\Models\Prodi::all();

        if ($years->isEmpty() || $prodis->isEmpty()) {
            $this->command->error('Years or Prodis not found. Please run YearSeeder and ProdiSeeder first.');
            return;
        }

        $alumniData = [
            ['nim' => '2020001', 'nama' => 'Ahmad Fadli', 'prodi_code' => 'TI', 'year_code' => '20232', 'email' => 'ahmad@email.com', 'no_hp' => '081234567890', 'status' => 'Bekerja'],
            ['nim' => '2020002', 'nama' => 'Siti Nurhaliza', 'prodi_code' => 'TI', 'year_code' => '20232', 'email' => 'siti@email.com', 'no_hp' => '081234567891', 'status' => 'Mencari Kerja'],
            ['nim' => '2020003', 'nama' => 'Budi Santoso', 'prodi_code' => 'SI', 'year_code' => '20232', 'email' => 'budi@email.com', 'no_hp' => '081234567892', 'status' => 'Wirausaha'],
            ['nim' => '2020004', 'nama' => 'Dewi Lestari', 'prodi_code' => 'SI', 'year_code' => '20232', 'email' => 'dewi@email.com', 'no_hp' => '081234567893', 'status' => 'Studi Lanjut'],
            ['nim' => '2019001', 'nama' => 'Eko Prasetyo', 'prodi_code' => 'DKV', 'year_code' => '20222', 'email' => 'eko@email.com', 'no_hp' => '081234567894', 'status' => 'Bekerja'],
            ['nim' => '2019002', 'nama' => 'Fitri Handayani', 'prodi_code' => 'DKV', 'year_code' => '20222', 'email' => 'fitri@email.com', 'no_hp' => '081234567895', 'status' => 'Belum Bekerja'],
            ['nim' => '2019003', 'nama' => 'Gunawan Wibowo', 'prodi_code' => 'MI', 'year_code' => '20222', 'email' => 'gunawan@email.com', 'no_hp' => '081234567896', 'status' => 'Bekerja'],
            ['nim' => '2019004', 'nama' => 'Hana Pertiwi', 'prodi_code' => 'MI', 'year_code' => '20222', 'email' => 'hana@email.com', 'no_hp' => '081234567897', 'status' => 'Mencari Kerja'],
            ['nim' => '2018001', 'nama' => 'Irfan Hakim', 'prodi_code' => 'TI', 'year_code' => '20212', 'email' => 'irfan@email.com', 'no_hp' => '081234567898', 'status' => 'Wirausaha'],
            ['nim' => '2018002', 'nama' => 'Jihan Aulia', 'prodi_code' => 'SI', 'year_code' => '20212', 'email' => 'jihan@email.com', 'no_hp' => '081234567899', 'status' => 'Bekerja'],
        ];

        foreach ($alumniData as $data) {
            // Find related IDs. Fallback to first if specific code not found (for robustness in dev).
            // Assuming ProdiSeeder creates prodis (we don't have codes in seeder view, taking a guess or using random/first)
            // Actually, let's just pick random if not found or be specific if we knew codes.
            // For simplicity and robustness:
            $prodi = $prodis->random();
            $year = $years->firstWhere('code', $data['year_code']) ?? $years->first();

            // Create user account for alumni first
            $checkUser = User::where('username', $data['nim'])->first();
            if (!$checkUser) {
                $user = User::create([
                    'username' => $data['nim'],
                    'password' => Hash::make('password123'),
                    'role_id' => Role::ALUMNI,
                ]);
                $userId = $user->id;
            } else {
                $userId = $checkUser->id;
            }

            // Check if alumni exists
            $checkAlumni = Alumni::where('nim', $data['nim'])->first();
            if (!$checkAlumni) {
                Alumni::create([
                    'user_id' => $userId,
                    'nim' => $data['nim'],
                    'nama' => $data['nama'],
                    'prodi_id' => $prodi->id,
                    'tahun_id' => $year->id,
                    'email' => $data['email'],
                    'no_hp' => $data['no_hp'],
                    'status' => $data['status'],
                ]);
            }
        }
    }
}
