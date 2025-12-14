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
        $query = Questionnaire::with(['type', 'questions'])
            ->withCount('responses');

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->type_id);
        }

        if ($request->filled('periode_tahun')) {
            $query->where('periode_tahun', $request->periode_tahun);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Sorting
        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $allowedSorts = ['title', 'created_at', 'updated_at', 'periode_tahun', 'is_active'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = min($request->input('per_page', 9), 100);
        $questionnaires = $query->paginate($perPage);

        return response()->json($questionnaires);
    }

    public function show($id)
    {
        $questionnaire = Questionnaire::with(['type', 'questions' => function ($q) {
            $q->orderBy('section')->orderBy('order');
        }])->withCount('responses')->findOrFail($id);

        return response()->json(['data' => $questionnaire]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type_id' => 'required|exists:questionnaire_types,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'periode_tahun' => 'required|integer|min:2000|max:2100',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $questionnaire = Questionnaire::create($validated);
        return response()->json([
            'data' => $questionnaire,
            'message' => 'Kuesioner berhasil dibuat'
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $questionnaire = Questionnaire::findOrFail($id);

        $validated = $request->validate([
            'type_id' => 'required|exists:questionnaire_types,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'periode_tahun' => 'required|integer|min:2000|max:2100',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $questionnaire->update($validated);
        return response()->json([
            'data' => $questionnaire,
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
