<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use Illuminate\Http\Request;

class ProdiController extends Controller
{
    public function index(Request $request)
    {
        $query = Prodi::with('faculty');

        if ($request->filled('faculty_id')) {
            $query->where('faculty_id', $request->faculty_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->input('per_page', 10), 100);
        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:prodis,code',
            'strata' => 'required|in:S1,S2,S3',
        ]);

        $prodi = Prodi::create($validated);

        return response()->json([
            'message' => 'Prodi berhasil ditambahkan',
            'data' => $prodi
        ], 201);
    }

    public function show($id)
    {
        return response()->json(Prodi::with('faculty')->findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $prodi = Prodi::findOrFail($id);

        $validated = $request->validate([
            'faculty_id' => 'required|exists:faculties,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:prodis,code,' . $prodi->id,
            'strata' => 'required|in:S1,S2,S3',
        ]);

        $prodi->update($validated);

        return response()->json([
            'message' => 'Prodi berhasil diperbarui',
            'data' => $prodi
        ]);
    }

    public function destroy($id)
    {
        $prodi = Prodi::findOrFail($id);
        $prodi->delete();

        return response()->json([
            'message' => 'Prodi berhasil dihapus'
        ]);
    }
}
