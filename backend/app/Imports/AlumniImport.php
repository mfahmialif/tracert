<?php

namespace App\Imports;

use App\Models\Alumni;
use App\Models\Prodi;
use App\Models\User;
use App\Models\Role;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Hash;

class AlumniImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    private $rowCount = 0;
    private $updateCount = 0;

    public function model(array $row)
    {
        $prodi = Prodi::where('kode_prodi', $row['kode_prodi'])->first();

        if (!$prodi) {
            return null;
        }

        $existingAlumni = Alumni::where('nim', $row['nim'])->first();

        if ($existingAlumni) {
            // Update existing
            $existingAlumni->update([
                'nama' => $row['nama'],
                'prodi_id' => $prodi->prodi_id,
                'tahun_lulus' => $row['tahun_lulus'],
                'email' => $row['email'] ?? null,
                'no_hp' => $row['no_hp'] ?? null,
            ]);
            $this->updateCount++;
            return null;
        }

        $this->rowCount++;

        // Create new alumni
        $alumni = Alumni::create([
            'nim' => $row['nim'],
            'nama' => $row['nama'],
            'prodi_id' => $prodi->prodi_id,
            'tahun_lulus' => $row['tahun_lulus'],
            'email' => $row['email'] ?? null,
            'no_hp' => $row['no_hp'] ?? null,
            'status' => 'active',
        ]);

        // Create user account for alumni
        User::create([
            'username' => $row['nim'],
            'password' => Hash::make('alumni123'), // Default password
            'role_id' => Role::ALUMNI,
            'alumni_id' => $alumni->alumni_id,
        ]);

        return null;
    }

    public function rules(): array
    {
        return [
            'nim' => 'required|string',
            'nama' => 'required|string',
            'kode_prodi' => 'required|exists:prodis,kode_prodi',
            'tahun_lulus' => 'required|integer|min:1900|max:2100',
        ];
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function getUpdateCount(): int
    {
        return $this->updateCount;
    }
}
