<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Response;
use App\Models\Questionnaire;
use App\Exports\TracerStudyExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportController extends Controller
{
    public function excel(Request $request)
    {
        $request->validate([
            'questionnaire_id' => 'required|exists:questionnaires,id',
        ]);

        $questionnaire = Questionnaire::findOrFail($request->questionnaire_id);
        $filename = 'tracer_study_' . str_replace(' ', '_', strtolower($questionnaire->title)) . '_' . date('Ymd') . '.xlsx';

        return Excel::download(
            new TracerStudyExport($request->questionnaire_id, $request->all()),
            $filename
        );
    }

    public function pdf(Request $request)
    {
        $request->validate([
            'questionnaire_id' => 'required|exists:questionnaires,id',
        ]);

        $questionnaire = Questionnaire::with(['type', 'questions'])->findOrFail($request->questionnaire_id);

        $responses = Response::with(['alumni.prodi', 'answers.question'])
            ->where('questionnaire_id', $request->questionnaire_id)
            ->when($request->filled('prodi_id'), function ($q) use ($request) {
                $q->whereHas('alumni', function ($q2) use ($request) {
                    $q2->where('prodi_id', $request->prodi_id);
                });
            })
            ->when($request->filled('tahun_lulus'), function ($q) use ($request) {
                $q->whereHas('alumni', function ($q2) use ($request) {
                    $q2->where('tahun_lulus', $request->tahun_lulus);
                });
            })
            ->get();

        // Calculate statistics
        $stats = $this->calculateStats($questionnaire, $responses);

        $pdf = Pdf::loadView('pdf.tracer_report', [
            'questionnaire' => $questionnaire,
            'responses' => $responses,
            'stats' => $stats,
            'generated_at' => now()->format('d F Y H:i'),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('laporan_tracer_study_' . date('Ymd') . '.pdf');
    }

    private function calculateStats($questionnaire, $responses)
    {
        $stats = [];

        foreach ($questionnaire->questions as $question) {
            if (in_array($question->type, ['radio', 'checkbox', 'select', 'scale'])) {
                $stats[$question->id] = [
                    'question' => $question->question_text,
                    'type' => $question->type,
                    'options' => $question->options ?? [],
                    'counts' => [],
                ];

                foreach ($responses as $response) {
                    $answer = $response->answers->firstWhere('question_id', $question->id);
                    if ($answer) {
                        $value = $answer->answer_value;
                        if (!isset($stats[$question->id]['counts'][$value])) {
                            $stats[$question->id]['counts'][$value] = 0;
                        }
                        $stats[$question->id]['counts'][$value]++;
                    }
                }
            }
        }

        return $stats;
    }
}
