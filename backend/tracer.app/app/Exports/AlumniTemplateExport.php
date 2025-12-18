<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumniTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['2020001', 'Ahmad Fadli', 'TI', '2024', 'ahmad@email.com', '081234567890'],
            ['2020002', 'Siti Nurhaliza', 'SI', '2023', 'siti@email.com', '081234567891'],
        ];
    }

    public function headings(): array
    {
        return ['nim', 'nama', 'kode_prodi', 'tahun_kode', 'email', 'no_hp'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
