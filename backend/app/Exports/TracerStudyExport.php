<?php

namespace App\Exports;

use App\Models\Response;
use App\Models\Questionnaire;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TracerStudyExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $questionnaireId;
    protected $filters;
    protected $questionnaire;
    protected $questions;

    public function __construct($questionnaireId, $filters = [])
    {
        $this->questionnaireId = $questionnaireId;
        $this->filters = $filters;
        $this->questionnaire = Questionnaire::with('questions')->findOrFail($questionnaireId);
        $this->questions = $this->questionnaire->questions->sortBy(['section', 'order']);
    }

    public function collection()
    {
        return Response::with(['alumni.prodi', 'answers'])
            ->where('questionnaire_id', $this->questionnaireId)
            ->when(isset($this->filters['prodi_id']), function ($q) {
                $q->whereHas('alumni', function ($q2) {
                    $q2->where('prodi_id', $this->filters['prodi_id']);
                });
            })
            ->when(isset($this->filters['tahun_lulus']), function ($q) {
                $q->whereHas('alumni', function ($q2) {
                    $q2->where('tahun_lulus', $this->filters['tahun_lulus']);
                });
            })
            ->get();
    }

    public function headings(): array
    {
        $headers = ['No', 'NIM', 'Nama', 'Prodi', 'Tahun Lulus', 'Tanggal Submit'];

        foreach ($this->questions as $question) {
            $headers[] = $question->question_text;
        }

        return $headers;
    }

    public function map($response): array
    {
        static $index = 0;
        $index++;

        $row = [
            $index,
            $response->alumni->nim,
            $response->alumni->nama,
            $response->alumni->prodi->nama_prodi ?? '-',
            $response->alumni->tahun_lulus,
            $response->submitted_at?->format('Y-m-d H:i'),
        ];

        foreach ($this->questions as $question) {
            $answer = $response->answers->firstWhere('question_id', $question->id);
            $value = $answer ? $answer->answer_value : '-';

            // Decode JSON for checkbox answers
            if ($question->type === 'checkbox' && $answer) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $value = implode(', ', $decoded);
                }
            }

            $row[] = $value;
        }

        return $row;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
