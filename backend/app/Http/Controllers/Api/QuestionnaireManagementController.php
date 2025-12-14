<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionnaireManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Questionnaire::with(['year', 'prodis', 'questions'])
            ->withCount('responses');

        if ($request->filled('tahun_id')) {
            $query->where('tahun_id', $request->tahun_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
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
            'tahun_id' => 'required|exists:years,id',
            'prodi_ids' => 'required|array',
            'prodi_ids.*' => 'exists:prodis,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $questionnaire->update([
            'tahun_id' => $validated['tahun_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'is_mandatory' => $validated['is_mandatory'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        if (isset($validated['prodi_ids'])) {
            $questionnaire->prodis()->sync($validated['prodi_ids']);
        }

        return response()->json([
            'data' => $questionnaire->load(['year', 'prodis']),
            'message' => 'Kuesioner berhasil diupdate'
        ]);
    }

    public function destroy($id)
    {
        $questionnaire = Questionnaire::findOrFail($id);

        // Check if has responses
        if ($questionnaire->responses()->count() > 0) {
            return response()->json([
                'message' => 'Tidak dapat menghapus kuesioner yang sudah memiliki jawaban'
            ], 422);
        }

        // Delete all questions first
        $questionnaire->questions()->delete();
        $questionnaire->delete();

        return response()->json(['message' => 'Kuesioner berhasil dihapus']);
    }
}
