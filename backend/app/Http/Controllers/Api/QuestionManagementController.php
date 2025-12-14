<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Questionnaire;
use Illuminate\Http\Request;

class QuestionManagementController extends Controller
{
    public function store(Request $request, $questionnaireId)
    {
        $questionnaire = Questionnaire::findOrFail($questionnaireId);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:text,textarea,number,date,radio,checkbox,select,scale',
            'options' => 'nullable|array',
            'is_required' => 'boolean',
            'order' => 'required|integer|min:1',
            'section' => 'required|integer|min:1',
            'depends_on' => 'nullable|exists:questions,id',
            'depends_value' => 'nullable|string',
        ]);

        $validated['questionnaire_id'] = $questionnaireId;

        $question = Question::create($validated);
        return response()->json([
            'data' => $question,
            'message' => 'Pertanyaan berhasil ditambahkan'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $question = Question::findOrFail($id);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'type' => 'required|in:text,textarea,number,date,radio,checkbox,select,scale',
            'options' => 'nullable|array',
            'is_required' => 'boolean',
            'order' => 'required|integer|min:1',
            'section' => 'required|integer|min:1',
            'depends_on' => 'nullable|exists:questions,id',
            'depends_value' => 'nullable|string',
        ]);

        $question->update($validated);
        return response()->json([
            'data' => $question,
            'message' => 'Pertanyaan berhasil diupdate'
        ]);
    }

    public function destroy($id)
    {
        $question = Question::findOrFail($id);

        // Check if any question depends on this question
        $dependentQuestions = Question::where('depends_on', $id)->count();
        if ($dependentQuestions > 0) {
            return response()->json([
                'message' => 'Tidak dapat menghapus pertanyaan yang menjadi syarat pertanyaan lain'
            ], 422);
        }

        // Check if has answers
        if ($question->answers()->count() > 0) {
            return response()->json([
                'message' => 'Tidak dapat menghapus pertanyaan yang sudah memiliki jawaban'
            ], 422);
        }

        $question->delete();
        return response()->json(['message' => 'Pertanyaan berhasil dihapus']);
    }

    public function reorder(Request $request, $questionnaireId)
    {
        $validated = $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:questions,id',
            'questions.*.order' => 'required|integer|min:1',
            'questions.*.section' => 'required|integer|min:1',
        ]);

        foreach ($validated['questions'] as $questionData) {
            Question::where('id', $questionData['id'])
                ->update([
                    'order' => $questionData['order'],
                    'section' => $questionData['section'],
                ]);
        }

        return response()->json(['message' => 'Urutan pertanyaan berhasil diupdate']);
    }
}
