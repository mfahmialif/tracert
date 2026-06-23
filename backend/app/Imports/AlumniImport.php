<?php

namespace App\Imports;

use App\Models\Alumni;
use App\Models\Prodi;
use App\Models\Year;
use App\Models\User;
use App\Models\Role;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AlumniImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    private $rowCount = 0;
    private $updateCount = 0;
    private $prodiMap = [];
    private $yearMap = [];
    private $defaultPassword;

    public function __construct()
    {
        // Pre-cache all prodi and year lookups (avoid N+1 queries)
        $this->prodiMap = Prodi::pluck('id', 'code')->toArray();
        $this->yearMap = Year::pluck('id', 'code')->toArray();

        // Hash password once instead of per-row
        $this->defaultPassword = Hash::make('alumni123');
    }

    public function collection(Collection $rows)
    {
        // Pre-fetch all existing alumni NIMs in this chunk for fast lookup
        $nims = $rows->pluck('nim')->filter()->unique()->toArray();
        $existingAlumni = Alumni::whereIn('nim', $nims)
            ->get()
            ->keyBy('nim');

        // Also check existing usernames to avoid duplicates
        $existingUsernames = User::whereIn('username', $nims)
            ->pluck('username')
            ->flip()
            ->toArray();

        $now = now();

        DB::transaction(function () use ($rows, $existingAlumni, $existingUsernames, $now) {
            $newUsers = [];
            $newAlumniData = [];
            $updateBatch = [];

            foreach ($rows as $row) {
                $nim = $row['nim'] ?? null;
                $nama = $row['nama'] ?? null;
                $kodeProdi = $row['kode_prodi'] ?? null;
                $tahunKode = $row['tahun_kode'] ?? null;

                // Skip rows with missing required fields
                if (!$nim || !$nama || !$kodeProdi || !$tahunKode) {
                    continue;
                }

                // Lookup from cached maps (no DB query)
                $prodiId = $this->prodiMap[$kodeProdi] ?? null;
                $yearId = $this->yearMap[$tahunKode] ?? null;

                if (!$prodiId || !$yearId) {
                    continue;
                }

                if ($existingAlumni->has($nim)) {
                    // Collect updates for batch processing
                    $updateBatch[] = [
                        'nim' => $nim,
                        'nama' => $nama,
                        'prodi_id' => $prodiId,
                        'year_id' => $yearId,
                        'email' => $row['email'] ?? null,
                        'no_hp' => $row['no_hp'] ?? null,
                    ];
                    $this->updateCount++;
                } else {
                    // Collect new records for batch insert
                    if (!isset($existingUsernames[$nim])) {
                        $newUsers[] = [
                            'username' => $nim,
                            'password' => $this->defaultPassword,
                            'role_id' => Role::ALUMNI,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $existingUsernames[$nim] = true; // Prevent duplicates within file
                    }

                    $newAlumniData[] = [
                        'nim' => $nim,
                        'nama' => $nama,
                        'prodi_id' => $prodiId,
                        'year_id' => $yearId,
                        'email' => $row['email'] ?? null,
                        'no_hp' => $row['no_hp'] ?? null,
                        'status' => 'Belum Bekerja',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $this->rowCount++;
                }
            }

            // Batch update existing alumni
            foreach ($updateBatch as $data) {
                Alumni::where('nim', $data['nim'])->update([
                    'nama' => $data['nama'],
                    'prodi_id' => $data['prodi_id'],
                    'year_id' => $data['year_id'],
                    'email' => $data['email'],
                    'no_hp' => $data['no_hp'],
                ]);
            }

            // Batch insert new users
            if (!empty($newUsers)) {
                // Insert in batches of 500
                foreach (array_chunk($newUsers, 500) as $chunk) {
                    User::insert($chunk);
                }

                // Map usernames to user IDs for alumni records
                $userIds = User::whereIn('username', array_column($newAlumniData, 'nim'))
                    ->pluck('id', 'username')
                    ->toArray();

                // Assign user_id to each alumni record
                foreach ($newAlumniData as &$alumni) {
                    $alumni['user_id'] = $userIds[$alumni['nim']] ?? null;
                }
                unset($alumni);

                // Remove any records that failed to get a user_id
                $newAlumniData = array_filter($newAlumniData, fn($a) => $a['user_id'] !== null);

                // Batch insert new alumni
                foreach (array_chunk($newAlumniData, 500) as $chunk) {
                    Alumni::insert($chunk);
                }
            }
        });
    }

    public function chunkSize(): int
    {
        return 500;
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
