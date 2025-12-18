<?php

namespace App\Exports;

use App\Models\Questionnaire;
use App\Models\Response;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class QuestionnaireResultExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $questionnaireId;
    protected $questions;

    public function __construct($questionnaireId)
    {
        $this->questionnaireId = $questionnaireId;
        $this->questions = Questionnaire::find($questionnaireId)->questions()->orderBy('section')->orderBy('order')->get();
    }

    public function collection()
    {
        return Response::with(['alumni.prodi', 'alumni.year', 'answers'])
            ->where('questionnaire_id', $this->questionnaireId)
            ->get();
    }

    public function headings(): array
    {
        $headers = [
            'NIM',
            'Nama Lengkap',
            'Prodi',
            'Tahun Lulus',
            'Tanggal Submit',
        ];

        foreach ($this->questions as $question) {
            $headers[] = $question->question_text;
        }

        return $headers;
    }

    public function map($response): array
    {
        $row = [
            $response->alumni->nim ?? '-',
            $response->alumni->fullname ?? '-',
            $response->alumni->prodi->name ?? '-',
            $response->alumni->year->year ?? '-',
            $response->submitted_at->format('Y-m-d H:i:s'),
        ];

        $answers = $response->answers->keyBy('question_id');

        foreach ($this->questions as $question) {
            $answer = $answers->get($question->id);
            $val = $answer ? $answer->answer_value : '-';

            // Format array values (checkboxes) to string
            $decoded = json_decode($val, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $val = implode(', ', $decoded);
            } else {
                // Remove quotes if present from single value json
                $val = trim($val, '"');
            }

            $row[] = $val;
        }

        return $row;
    }
}
