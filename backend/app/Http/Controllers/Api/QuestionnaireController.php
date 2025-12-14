<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\Response;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class QuestionnaireController extends Controller
{
    public function counts(Request $request)
    {
        $user = $request->user();
        $alumni = $user->alumni;
        $alumniId = $alumni ? $alumni->alumni_id : null;

        if (!$alumni) {
            return response()->json(['all' => 0, 'completed' => 0, 'pending' => 0]);
        }

        // Base query for available questionnaires
        $baseQuery = Questionnaire::open();
        $baseQuery->whereHas('prodis', function ($q) use ($alumni) {
            $q->where('prodis.id', $alumni->prodi_id);
        });

        $total = $baseQuery->count();

        // Count completed (based on base query to ensure consistency)
        // We need to check which of these "available" ones are submitted
        $completed = (clone $baseQuery)->whereHas('responses', function ($q) use ($alumniId) {
            $q->where('alumni_id', $alumniId);
        })->count();

        $pending = $total - $completed;

        return response()->json([
            'all' => $total,
            'completed' => $completed,
            'pending' => $pending
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $alumni = $user->alumni;
        $alumniId = $alumni ? $alumni->alumni_id : null;

        $perPage = min($request->input('per_page', 15), 100);

        $query = Questionnaire::with('year')
            ->open()
            ->withCount('questions');

        // Filter by alumni's prodi if alumni exists
        if ($alumni) {
            $query->whereHas('prodis', function ($q) use ($alumni) {
                $q->where('prodis.id', $alumni->prodi_id);
            });
        }

        // Filter by Status
        $status = $request->input('status');
        if ($status === 'completed' && $alumniId) {
            $query->whereHas('responses', function ($q) use ($alumniId) {
                $q->where('alumni_id', $alumniId);
            });
        } elseif ($status === 'pending' && $alumniId) {
            $query->whereDoesntHave('responses', function ($q) use ($alumniId) {
                $q->where('alumni_id', $alumniId);
            });
        }

        // Search
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'newest');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortBy === 'title') {
            $query->orderBy('title', $sortOrder);
        } elseif ($sortBy === 'year') {
            $query->join('years', 'questionnaires.tahun_id', '=', 'years.id')
                ->orderBy('years.name', $sortOrder)
                ->select('questionnaires.*');
        } elseif ($sortBy === 'newest' || $sortBy === 'created_at') {
            $query->orderBy('created_at', $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $questionnaires = $query->paginate($perPage);

        // Transform each item to include has_submitted
        $questionnaires->getCollection()->transform(function ($q) use ($alumniId) {
            $hasSubmitted = $alumniId
                ? Response::where('questionnaire_id', $q->id)
                ->where('alumni_id', $alumniId)
                ->exists()
                : false;

            $q->has_submitted = $hasSubmitted;
            return $q;
        });

        return response()->json($questionnaires);
    }

    public function show(Request $request, $id)
    {
        $questionnaire = Questionnaire::with(['year', 'questions' => function ($q) {
            $q->orderBy('section')->orderBy('order');
        }])->findOrFail($id);

        $user = $request->user();
        $alumni = $user->alumni;
        $submittedAnswers = null;
        $hasSubmitted = false;

        if ($alumni) {
            $response = Response::with('answers')
                ->where('questionnaire_id', $id)
                ->where('alumni_id', $alumni->alumni_id)
                ->first();

            if ($response) {
                $hasSubmitted = true;
                $submittedAnswers = $response->answers->mapWithKeys(function ($a) {
                    // Try to decode JSON, if fails keep original string
                    $decoded = json_decode($a->answer_value, true);
                    return [$a->question_id => (json_last_error() === JSON_ERROR_NONE) ? $decoded : $a->answer_value];
                });
            }
        }

        // Note: Logic to check openness remains same
        // If submitted, allow viewing even if closed? For now keeping strict.
        if (!$questionnaire->isOpen() && !$hasSubmitted) {
            // For testing/admin purposes we might want to allow viewing content via show even if closed?
            // But valid for alumni usage.
            return response()->json(['message' => 'Kuesioner tidak tersedia'], 403);
        }

        $sections = $questionnaire->questions->groupBy('section');

        return response()->json([
            'data' => [
                'id' => $questionnaire->id,
                'title' => $questionnaire->title,
                'description' => $questionnaire->description,
                'year' => $questionnaire->year->name,
                'is_mandatory' => $questionnaire->is_mandatory,
                'has_submitted' => $hasSubmitted,
                'submitted_answers' => $submittedAnswers,
                'sections' => $sections->map(function ($questions, $sectionNum) {
                    return [
                        'section' => $sectionNum,
                        'questions' => $questions->map(function ($q) {
                            return [
                                'id' => $q->id,
                                'text' => $q->question_text,
                                'type' => $q->type,
                                'options' => $q->options,
                                'is_required' => $q->is_required,
                                'depends_on' => $q->depends_on,
                                'depends_value' => $q->depends_value,
                            ];
                        })->values(),
                    ];
                })->values(),
            ],
        ]);
    }

    public function submit(Request $request, $id)
    {
        $user = $request->user();
        $alumni = $user->alumni;

        if (!$alumni) {
            return response()->json(['message' => 'Hanya alumni yang dapat mengisi kuesioner'], 403);
        }

        $questionnaire = Questionnaire::with('questions')->findOrFail($id);

        if (!$questionnaire->isOpen()) {
            return response()->json(['message' => 'Kuesioner tidak tersedia'], 403);
        }

        // Check if already submitted
        $existingResponse = Response::where('questionnaire_id', $id)
            ->where('alumni_id', $alumni->alumni_id)
            ->exists();

        if ($existingResponse) {
            return response()->json(['message' => 'Anda sudah mengisi kuesioner ini'], 422);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.value' => 'required',
        ]);

        // Validate required questions
        $requiredQuestionIds = $questionnaire->questions
            ->where('is_required', true)
            ->pluck('id')
            ->toArray();

        $answeredQuestionIds = collect($request->answers)
            ->pluck('question_id')
            ->toArray();

        $missingRequired = array_diff($requiredQuestionIds, $answeredQuestionIds);

        if (!empty($missingRequired)) {
            return response()->json([
                'message' => 'Ada pertanyaan wajib yang belum dijawab',
                'missing' => $missingRequired,
            ], 422);
        }

        DB::beginTransaction();
        try {
            $response = Response::create([
                'questionnaire_id' => $id,
                'alumni_id' => $alumni->alumni_id,
                'submitted_at' => now(),
            ]);

            foreach ($request->answers as $answer) {
                $value = is_array($answer['value'])
                    ? json_encode($answer['value'])
                    : $answer['value'];

                Answer::create([
                    'response_id' => $response->id,
                    'question_id' => $answer['question_id'],
                    'answer_value' => $value,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Kuesioner berhasil disubmit',
                'response_id' => $response->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
