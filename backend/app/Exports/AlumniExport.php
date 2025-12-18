<?php

namespace App\Exports;

use App\Models\Alumni;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Database\Eloquent\Builder;

class AlumniExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $query;
    protected $rowNumber = 0;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->with(['prodi', 'year']);
    }

    public function headings(): array
    {
        return [
            'No',
            'NIM',
            'Nama',
            'Prodi',
            'Tahun Lulus',
            'Email',
            'No HP',
            'Status',
        ];
    }

    public function map($alumni): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $alumni->nim,
            $alumni->nama,
            $alumni->prodi->name ?? '-',
            $alumni->year->name ?? '-',
            $alumni->email ?? '-',
            $alumni->no_hp ?? '-',
            $alumni->status ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0']
                ]
            ],
        ];
    }
}
