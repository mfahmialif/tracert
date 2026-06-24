<?php

namespace App\Http\Controllers\Api;

use App\Exports\QuestionnaireImportTemplateExport;
use App\Exports\QuestionnaireResultExport;
use App\Imports\QuestionnaireResponseImport;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Questionnaire;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class QuestionnaireManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Questionnaire::with(['year', 'prodis', 'questions'])
            ->withCount(['responses', 'questions']);

        if ($request->filled('year_id')) {
            $query->where('year_id', $request->year_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        // Sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $allowedSorts = ['title', 'created_at', 'updated_at', 'is_active'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = min($request->input('per_page', 10), 100);
        $questionnaires = $query->paginate($perPage);

        return response()->json($questionnaires);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year_id' => 'required|exists:years,id',
            'prodi_ids' => 'present|array',
            'prodi_ids.*' => 'exists:prodis,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $questionnaire = Questionnaire::create([
            'year_id' => $validated['year_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_mandatory' => $validated['is_mandatory'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'is_public' => $validated['is_public'] ?? false,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        if (isset($validated['prodi_ids'])) {
            $questionnaire->prodis()->sync($validated['prodi_ids']);
        }

        return response()->json([
            'data' => $questionnaire->load(['year', 'prodis']),
            'message' => 'Kuesioner berhasil dibuat',
            'req' => $request->all(),
        ], 201);
    }

    public function show($id)
    {
        $questionnaire = Questionnaire::with(['year', 'prodis', 'questions' => function ($q) {
            $q->orderBy('section')->orderBy('order');
        }])->withCount('responses')->findOrFail($id);

        $sections = $questionnaire->questions->groupBy('section');

        // Transform to include sections structure
        $data = $questionnaire->toArray();
        $data['sections'] = $sections->map(function ($questions, $sectionNum) {
            return [
                'section' => $sectionNum,
                'questions' => $questions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'text' => $q->question_text,
                        'type' => $q->type,
                        'options' => $q->options,
                        'is_required' => $q->is_required,
                        'allow_other' => $q->allow_other,
                        'depends_on' => $q->depends_on,
                        'depends_value' => $q->depends_value,
                        'order' => $q->order,
                        'section' => $q->section,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json(['data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $questionnaire = Questionnaire::findOrFail($id);

        $validated = $request->validate([
            'year_id' => 'required|exists:years,id',
            'prodi_ids' => 'nullable|array',
            'prodi_ids.*' => 'exists:prodis,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $questionnaire->update([
            'year_id' => $validated['year_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_mandatory' => $validated['is_mandatory'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'is_public' => $validated['is_public'] ?? false,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        if (isset($validated['prodi_ids'])) {
            $questionnaire->prodis()->sync($validated['prodi_ids']);
        }

        return response()->json([
            'data' => $questionnaire->load(['year', 'prodis']),
            'message' => 'Kuesioner berhasil diupdate',
        ]);
    }

    public function destroy($id)
    {
        $questionnaire = Questionnaire::findOrFail($id);

        // Check if has responses
        if ($questionnaire->responses()->count() > 0) {
            return response()->json([
                'message' => 'Tidak dapat menghapus kuesioner yang sudah memiliki jawaban',
            ], 422);
        }

        // Delete all questions first
        $questionnaire->questions()->delete();
        $questionnaire->delete();

        return response()->json(['message' => 'Kuesioner berhasil dihapus']);
    }

    public function duplicate($id)
    {
        $original = Questionnaire::with(['questions' => function ($q) {
            $q->orderBy('section')->orderBy('order');
        }, 'prodis'])->findOrFail($id);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // Create duplicated questionnaire
            $newQuestionnaire = Questionnaire::create([
                'year_id' => $original->year_id,
                'title' => $original->title . ' (Copy)',
                'description' => $original->description,
                'is_mandatory' => $original->is_mandatory,
                'is_active' => false, // Start as draft
                'is_public' => $original->is_public,
                'start_date' => $original->start_date,
                'end_date' => $original->end_date,
            ]);

            // Sync prodis
            $prodiIds = $original->prodis->pluck('id')->toArray();
            if (!empty($prodiIds)) {
                $newQuestionnaire->prodis()->sync($prodiIds);
            }

            // Duplicate questions and build old->new ID mapping
            $idMapping = []; // old question id => new question id

            foreach ($original->questions as $question) {
                $newQuestion = Question::create([
                    'questionnaire_id' => $newQuestionnaire->id,
                    'question_text' => $question->question_text,
                    'type' => $question->type,
                    'options' => $question->options,
                    'is_required' => $question->is_required,
                    'allow_other' => $question->allow_other,
                    'order' => $question->order,
                    'section' => $question->section,
                    'depends_on' => null, // Will be remapped after
                    'depends_value' => $question->depends_value,
                ]);

                $idMapping[$question->id] = $newQuestion->id;
            }

            // Remap depends_on references to new question IDs
            foreach ($original->questions as $question) {
                if ($question->depends_on && isset($idMapping[$question->depends_on])) {
                    $newQuestionId = $idMapping[$question->id];
                    Question::where('id', $newQuestionId)->update([
                        'depends_on' => $idMapping[$question->depends_on],
                    ]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message' => 'Kuesioner berhasil diduplikasi',
                'data' => $newQuestionnaire->load(['year', 'prodis', 'questions']),
            ], 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'message' => 'Gagal menduplikasi kuesioner: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function results($id)
    {
        $data = $this->getResultsData($id);

        return response()->json($data);
    }

    public function respondents($id)
    {
        $questionnaire = Questionnaire::findOrFail($id);
        $showIdentity = ! $questionnaire->is_public;

        // Get all responses with alumni data
        $respondents = \App\Models\Response::where('questionnaire_id', $id)
            ->with(['alumni.prodi', 'alumni.year'])
            ->get()
            ->map(function ($response) use ($showIdentity) {
                // Check if this is a public response (alumni_id is null)
                if ($response->alumni_id === null) {
                    // Public respondent — return public contact fields
                    return [
                        'id' => $response->id,
                        'nama' => $response->respondent_name ?? 'Public User',
                        'email' => $response->respondent_email ?? '-',
                        'telepon' => $response->respondent_phone ?? '-',
                        'submitted_at' => $response->submitted_at ? $response->submitted_at->format('Y-m-d H:i:s') : $response->created_at->format('Y-m-d H:i:s'),
                        'status' => $response->submitted_at ? 'complete' : 'draft',
                        'is_public' => true,
                    ];
                } else {
                    // Alumni respondent
                    $alumni = $response->alumni;

                    return [
                        'id' => $alumni->alumni_id,
                        'nim' => $showIdentity ? $alumni->nim : null,
                        'nama' => $showIdentity ? $alumni->nama : null,
                        'prodi' => $showIdentity ? ($alumni->prodi->nama ?? '-') : null,
                        'tahun_lulus' => $showIdentity ? ($alumni->year->name ?? '-') : null,
                        'submitted_at' => $response->submitted_at ? $response->submitted_at->format('Y-m-d H:i:s') : $response->created_at->format('Y-m-d H:i:s'),
                        'status' => $response->submitted_at ? 'complete' : 'draft',
                        'is_public' => false,
                    ];
                }
            })
            ->sortByDesc('submitted_at')
            ->values();

        return response()->json([
            'questionnaire_title' => $questionnaire->title,
            'total_responses' => $respondents->count(),
            'is_public' => $showIdentity,
            'respondents' => $respondents,
        ]);
    }

    public function questionRespondents($questionnaireId, $questionId)
    {
        $questionnaire = Questionnaire::findOrFail($questionnaireId);
        $question = Question::findOrFail($questionId);
        $showIdentity = ! $questionnaire->is_public;

        // Get all answers for this question with alumni data
        $answers = \App\Models\Answer::where('question_id', $questionId)
            ->with(['response.alumni.prodi', 'response.alumni.year'])
            ->get()
            ->map(function ($answer) use ($showIdentity) {
                $response = $answer->response;
                $answerValue = $answer->answer_value;

                // Try to decode JSON for checkbox/multiple answers
                $decoded = json_decode($answerValue, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $answerValue = implode(', ', $decoded);
                } else {
                    $answerValue = trim($answerValue, '"');
                }

                // Check if this is a public response (alumni_id is null)
                if ($response->alumni_id === null) {
                    // Public respondent
                    return [
                        'id' => $response->id,
                        'nim' => $showIdentity ? '-' : null,
                        'nama' => $showIdentity ? ($response->respondent_name ?? 'Public User') : null,
                        'prodi' => $showIdentity ? '-' : null,
                        'tahun_lulus' => $showIdentity ? '-' : null,
                        'answer' => $answerValue,
                        'submitted_at' => $answer->created_at->format('Y-m-d H:i:s'),
                        'is_public' => true,
                    ];
                } else {
                    // Alumni respondent
                    $alumni = $response->alumni;

                    return [
                        'id' => $alumni->alumni_id,
                        'nim' => $showIdentity ? $alumni->nim : null,
                        'nama' => $showIdentity ? $alumni->nama : null,
                        'prodi' => $showIdentity ? ($alumni->prodi->name ?? '-') : null,
                        'tahun_lulus' => $showIdentity ? ($alumni->year->name ?? '-') : null,
                        'answer' => $answerValue,
                        'submitted_at' => $answer->created_at->format('Y-m-d H:i:s'),
                        'is_public' => false,
                    ];
                }
            })
            ->sortByDesc('submitted_at')
            ->values();

        return response()->json([
            'questionnaire_title' => $questionnaire->title,
            'question_text' => $question->question_text,
            'question_type' => $question->type,
            'total_answers' => $answers->count(),
            'is_public' => $showIdentity,
            'answers' => $answers,
        ]);
    }

    public function exportExcel($id)
    {
        $questionnaire = Questionnaire::findOrFail($id);
        $filename = 'hasil_kuesioner_'.Str::slug($questionnaire->title).'_'.date('Y-m-d').'.xlsx';

        return Excel::download(new QuestionnaireResultExport($id), $filename);
    }

    public function exportPdf($id)
    {
        // Increase memory and time limits for heavy PDF generation on servers
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');

        try {
            $data = $this->getResultsData($id);
            $pdf = Pdf::loadView('exports.questionnaire_results', $data);
            
            // Set some robust options for server environments
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'chroot' => public_path(),
            ]);

            $filename = 'hasil_kuesioner_'.Str::slug($data['title']).'_'.date('Y-m-d').'.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membuat PDF: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    private function getResultsData($id)
    {
        $questionnaire = Questionnaire::with(['questions' => function ($q) {
            $q->orderBy('section')->orderBy('order');
        }])->findOrFail($id);

        $results = $questionnaire->questions->map(function ($question) {
            $answers = \App\Models\Answer::where('question_id', $question->id)->get();
            $count = $answers->count();

            $data = [
                'id' => $question->id,
                'text' => $question->question_text,
                'type' => $question->type,
                'count' => $count,
                'options' => array_values($question->options ?? []),
                'is_required' => $question->is_required,
            ];

            if (in_array($question->type, ['text', 'textarea', 'long_text', 'number', 'date'])) {
                $data['answers'] = $answers->sortByDesc('created_at')->take(20)->map(function ($a) {
                    return $a->answer_value;
                })->values();
            } else {
                // Radio, Checkbox, Select, Scale
                $stats = [];
                // Initialize stats with 0 for all options
                if (is_array($question->options)) {
                    foreach ($question->options as $opt) {
                        $stats[$opt] = 0;
                    }
                }

                foreach ($answers as $answer) {
                    $val = $answer->answer_value;
                    $decoded = json_decode($val, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        foreach ($decoded as $v) {
                            $stats[$v] = ($stats[$v] ?? 0) + 1;
                        }
                    } else {
                        // Clean up quotes if present (sometimes happens with json encoding single strings)
                        $cleanVal = trim($val, '"');
                        $stats[$cleanVal] = ($stats[$cleanVal] ?? 0) + 1;
                    }
                }
                $data['stats'] = $stats;
            }

            return $data;
        });

        return [
            'title' => $questionnaire->title,
            'is_public' => $questionnaire->is_public,
            'total_alumni_count' => $questionnaire->is_public
                ? null
                : $this->totalAlumniCount($questionnaire),
            'available_alumni_count' => $questionnaire->is_public
                ? null
                : $this->availableAlumniCount($questionnaire),
            'results' => $results,
        ];
    }

    private function totalAlumniCount(Questionnaire $questionnaire): int
    {
        $prodiIds = $questionnaire->prodis()->pluck('prodis.id');
        $query = \App\Models\Alumni::query();

        if ($prodiIds->isNotEmpty()) {
            $query->whereIn('prodi_id', $prodiIds);
        }

        return $query->count();
    }

    private function availableAlumniCount(Questionnaire $questionnaire): int
    {
        $prodiIds = $questionnaire->prodis()->pluck('prodis.id');
        $query = \App\Models\Alumni::query()
            ->whereDoesntHave('responses', fn ($response) => $response
                ->where('questionnaire_id', $questionnaire->id));

        if ($prodiIds->isNotEmpty()) {
            $query->whereIn('prodi_id', $prodiIds);
        }

        return $query->count();
    }

    public function downloadImportTemplate($id)
    {
        $questionnaire = Questionnaire::findOrFail($id);
        $filename = 'Template_Import_Kuesioner_' . Str::slug($questionnaire->title) . '.xlsx';
        return Excel::download(new QuestionnaireImportTemplateExport($id), $filename);
    }

    public function importExcel(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120'
        ]);

        $questionnaire = Questionnaire::findOrFail($id);

        try {
            Excel::import(new QuestionnaireResponseImport($id), $request->file('file'));
            return response()->json([
                'message' => 'Data berhasil diimport.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengimport data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function clearResponses($id)
    {
        $questionnaire = Questionnaire::findOrFail($id);
        
        // This will cascade delete all associated answers due to foreign key constraints
        \App\Models\Response::where('questionnaire_id', $questionnaire->id)->delete();

        return response()->json([
            'message' => 'Semua data responden berhasil dikosongkan.'
        ]);
    }
}
