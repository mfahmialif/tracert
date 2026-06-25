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
    protected $isStatusEnabled = false;

    public function __construct(Builder $query)
    {
        $this->query = $query;
        $this->isStatusEnabled = \App\Models\Setting::where('key', 'enable_alumni_status')->value('value') === 'true';
    }

    public function query()
    {
        return $this->query->with(['prodi', 'year']);
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'NIM',
            'Nama',
            'Prodi',
            'Tahun Lulus',
            'Email',
            'No HP',
        ];

        if ($this->isStatusEnabled) {
            $headings[] = 'Status';
        }

        return $headings;
    }

    public function map($alumni): array
    {
        $this->rowNumber++;

        $row = [
            $this->rowNumber,
            $alumni->nim,
            $alumni->nama,
            $alumni->prodi->name ?? '-',
            $alumni->year->name ?? '-',
            $alumni->email ?? '-',
            $alumni->no_hp ?? '-',
        ];

        if ($this->isStatusEnabled) {
            $row[] = $alumni->status ?? '-';
        }

        return $row;
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
