<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\Response;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicQuestionnaireController extends Controller
{
    public function index()
    {
        $questionnaires = Questionnaire::with(['year'])
            ->where('is_public', true)
            ->where('is_active', true)
            // ->where('start_date', '<=', now())
            // ->where('end_date', '>=', now())
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'title' => $q->title,
                    'description' => $q->description,
                    'year' => $q->year->name ?? '-',
                    'questions_count' => $q->questions()->count(),
                ];
            });

        return response()->json([
            'questionnaires' => $questionnaires,
        ]);
    }

    public function show($id)
    {
        $questionnaire = Questionnaire::with(['questions' => function ($q) {
            $q->orderBy('section')->orderBy('order');
        }])
            ->where('is_public', true)
            ->where('is_active', true)
            ->findOrFail($id);

        $sections = $questionnaire->questions->groupBy('section');

        $data = [
            'id' => $questionnaire->id,
            'title' => $questionnaire->title,
            'description' => $questionnaire->description,
            'year' => $questionnaire->year->name ?? '-',
            'end_date' => $questionnaire->end_date ? $questionnaire->end_date->format('Y-m-d') : null,
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
                            'order' => $q->order,
                            'section' => $q->section,
                        ];
                    })->values(),
                ];
            })->values(),
        ];

        return response()->json(['data' => $data]);
    }

    public function submit(Request $request, $id)
    {
        $questionnaire = Questionnaire::where('is_public', true)
            ->where('is_active', true)
            ->findOrFail($id);

        $validated = $request->validate([
            'respondent_name' => 'required|string|max:255',
            'respondent_email' => 'required|email|max:255',
            'respondent_phone' => 'nullable|string|max:20',
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.answer_value' => 'required',
        ]);

        // Check for duplicate email submission
        $existingResponse = Response::where('questionnaire_id', $id)
            ->where('respondent_email', $validated['respondent_email'])
            ->whereNotNull('submitted_at')
            ->first();

        if ($existingResponse) {
            return response()->json([
                'message' => 'You have already submitted this questionnaire'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create response
            $response = Response::create([
                'questionnaire_id' => $id,
                'alumni_id' => null,
                'respondent_name' => $validated['respondent_name'],
                'respondent_email' => $validated['respondent_email'],
                'respondent_phone' => $validated['respondent_phone'],
                'submitted_at' => now(),
            ]);

            // Save answers
            foreach ($validated['answers'] as $answerData) {
                $answerValue = $answerData['answer_value'];

                // If answer is array (checkbox), encode to JSON
                if (is_array($answerValue)) {
                    $answerValue = json_encode($answerValue);
                }

                Answer::create([
                    'response_id' => $response->id,
                    'question_id' => $answerData['question_id'],
                    'answer_value' => $answerValue,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Response submitted successfully',
                'response_id' => $response->id,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to submit response',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
