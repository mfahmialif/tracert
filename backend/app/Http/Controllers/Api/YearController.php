<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Year;
use Illuminate\Http\Request;

class YearController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min($request->input('per_page', 10), 100);
        $query = Year::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        if ($request->filled('active_only')) {
            $query->where('is_active', true);
        }

        return response()->json($query->orderBy('name', 'desc')->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:years,code',
            'name' => 'required|string|max:50',
            'smt' => 'required|in:Genap,Ganjil',
            'is_active' => 'boolean',
        ]);

        $year = Year::create($validated);

        return response()->json([
            'message' => 'Tahun Akademik berhasil ditambahkan',
            'data' => $year
        ], 201);
    }

    public function show($id)
    {
        return response()->json(Year::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $year = Year::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:years,code,' . $year->id,
            'name' => 'required|string|max:50',
            'smt' => 'required|in:Genap,Ganjil',
            'is_active' => 'boolean',
        ]);

        $year->update($validated);

        return response()->json([
            'message' => 'Tahun Akademik berhasil diperbarui',
            'data' => $year
        ]);
    }

    public function destroy($id)
    {
        $year = Year::findOrFail($id);
        $year->delete();

        return response()->json([
            'message' => 'Tahun Akademik berhasil dihapus'
        ]);
    }
}
