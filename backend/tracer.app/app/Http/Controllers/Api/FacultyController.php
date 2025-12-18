<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use Illuminate\Http\Request;

class FacultyController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min($request->input('per_page', 10), 100);
        $query = Faculty::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:faculties,code',
        ]);

        $faculty = Faculty::create($validated);

        return response()->json([
            'message' => 'Fakultas berhasil ditambahkan',
            'data' => $faculty
        ], 201);
    }

    public function show($id)
    {
        return response()->json(Faculty::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $faculty = Faculty::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:faculties,code,' . $faculty->id,
        ]);

        $faculty->update($validated);

        return response()->json([
            'message' => 'Fakultas berhasil diperbarui',
            'data' => $faculty
        ]);
    }

    public function destroy($id)
    {
        $faculty = Faculty::findOrFail($id);
        $faculty->delete();

        return response()->json([
            'message' => 'Fakultas berhasil dihapus'
        ]);
    }
}
