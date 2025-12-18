<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuestionnaireType;
use Illuminate\Http\Request;

class QuestionnaireTypeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min($request->input('per_page', 15), 100);
        $types = QuestionnaireType::withCount('questionnaires')->paginate($perPage);
        return response()->json($types);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $type = QuestionnaireType::create($validated);
        return response()->json(['data' => $type, 'message' => 'Tipe kuesioner berhasil dibuat'], 201);
    }

    public function update(Request $request, $id)
    {
        $type = QuestionnaireType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $type->update($validated);
        return response()->json(['data' => $type, 'message' => 'Tipe kuesioner berhasil diupdate']);
    }

    public function destroy($id)
    {
        $type = QuestionnaireType::findOrFail($id);

        // Check if type has questionnaires
        if ($type->questionnaires()->count() > 0) {
            return response()->json([
                'message' => 'Tidak dapat menghapus tipe yang masih memiliki kuesioner'
            ], 422);
        }

        $type->delete();
        return response()->json(['message' => 'Tipe kuesioner berhasil dihapus']);
    }
}
