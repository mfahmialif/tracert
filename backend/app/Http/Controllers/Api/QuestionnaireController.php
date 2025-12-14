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
    public function index(Request $request)
    {
        $user = $request->user();
        $alumniId = $user->alumni_id;

        $perPage = min($request->input('per_page', 15), 100);

        $questionnaires = Questionnaire::with('type')
            ->open()
            ->withCount('questions')
            ->paginate($perPage);

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

    public function show($id)
    {
        $questionnaire = Questionnaire::with(['type', 'questions' => function ($q) {
            $q->orderBy('section')->orderBy('order');
        }])->findOrFail($id);

        if (!$questionnaire->isOpen()) {
            return response()->json(['message' => 'Kuesioner tidak tersedia'], 403);
        }

        $sections = $questionnaire->questions->groupBy('section');

        return response()->json([
            'data' => [
                'id' => $questionnaire->id,
                'title' => $questionnaire->title,
                'description' => $questionnaire->description,
                'type' => $questionnaire->type->name,
                'is_mandatory' => $questionnaire->is_mandatory,
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

        if (!$user->alumni_id) {
            return response()->json(['message' => 'Hanya alumni yang dapat mengisi kuesioner'], 403);
        }

        $questionnaire = Questionnaire::with('questions')->findOrFail($id);

        if (!$questionnaire->isOpen()) {
            return response()->json(['message' => 'Kuesioner tidak tersedia'], 403);
        }

        // Check if already submitted
        $existingResponse = Response::where('questionnaire_id', $id)
            ->where('alumni_id', $user->alumni_id)
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
                'alumni_id' => $user->alumni_id,
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
            return response()->json(['message' => 'Terjadi kesalahan'], 500);
        }
    }
}
