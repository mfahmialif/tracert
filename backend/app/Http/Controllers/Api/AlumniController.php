<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\Prodi;
use App\Models\User;
use App\Models\Role;
use App\Imports\AlumniImport;
use App\Exports\AlumniTemplateExport;
use App\Exports\AlumniExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Hash;

class AlumniController extends Controller
{
    public function index(Request $request)
    {
        $query = Alumni::with('prodi');
        $perPage = min($request->input('per_page', 10), 100);
        $query = Alumni::with(['user', 'prodi', 'year']); // Eager load relationships

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        // Filter by Prodi
        if ($request->filled('prodi_id') && $request->prodi_id !== 'all') {
            $query->where('prodi_id', $request->prodi_id);
        }

        // Filter by Year
        if ($request->filled('year_id') && $request->year_id !== 'all') {
            $query->where('year_id', $request->year_id);
        }

        // Filter by Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Sorting
        if ($request->filled('sort_by')) {
            $sortBy = $request->sort_by;
            $sortOrder = $request->input('sort_order', 'asc');

            if ($sortBy === 'year.name') {
                $query->join('years', 'alumni.year_id', '=', 'years.id')
                    ->orderBy('years.name', $sortOrder)
                    ->select('alumni.*'); // Avoid column collision
            } elseif (in_array($sortBy, ['nim', 'nama', 'status', 'created_at'])) {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        return $this->storeLogic($request);
    }

    private function storeLogic($request)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:20|unique:alumni,nim',
            'nama' => 'required|string|max:100',
            'prodi_id' => 'required|exists:prodis,id',
            'year_id' => 'required|exists:years,id',
            'email' => 'required|email|max:255',
            'no_hp' => 'nullable|string|max:20',
            'status' => 'required|in:Bekerja,Mencari Kerja,Wirausaha,Studi Lanjut,Belum Bekerja',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $user = User::create([
                'username' => $validated['nim'],
                'password' => Hash::make($validated['nim']),
                'role_id' => Role::ALUMNI,
            ]);

            $alumniData = array_merge($validated, ['user_id' => $user->id]);
            $alumni = Alumni::create($alumniData);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message' => 'Alumni berhasil ditambahkan',
                'data' => $alumni
            ], 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan alumni: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $alumni = Alumni::with('prodi')->findOrFail($id);
        return response()->json($alumni);
    }

    public function update(Request $request, $id)
    {
        $alumni = Alumni::findOrFail($id);

        $validated = $request->validate([
            'nim' => 'required|string|max:20|unique:alumni,nim,' . $alumni->alumni_id . ',alumni_id',
            'nama' => 'required|string|max:100',
            'prodi_id' => 'required|exists:prodis,id',
            'year_id' => 'required|exists:years,id',
            'email' => 'required|email|max:255',
            'no_hp' => 'nullable|string|max:20',
            'status' => 'required|in:Bekerja,Mencari Kerja,Wirausaha,Studi Lanjut,Belum Bekerja',
        ]);

        $alumni->update($validated);

        return response()->json([
            'message' => 'Alumni berhasil diperbarui',
            'data' => $alumni
        ]);
    }

    public function destroy($id)
    {
        $alumni = Alumni::findOrFail($id);
        $alumni->delete();

        return response()->json([
            'message' => 'Alumni berhasil dihapus'
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new AlumniImport();
            Excel::import($import, $request->file('file'));

            return response()->json([
                'message' => 'Import berhasil',
                'imported' => $import->getRowCount(),
                'updated' => $import->getUpdateCount(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import gagal: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new AlumniTemplateExport(), 'template_import_alumni.xlsx');
    }

    public function export(Request $request)
    {
        $query = Alumni::with(['prodi', 'year']);

        // Apply same filters as index method
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        // Filter by Prodi
        if ($request->filled('prodi_id') && $request->prodi_id !== 'all') {
            $query->where('prodi_id', $request->prodi_id);
        }

        // Filter by Year
        if ($request->filled('year_id') && $request->year_id !== 'all') {
            $query->where('year_id', $request->year_id);
        }

        // Filter by Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if ($sortBy === 'year.name') {
            $query->join('years', 'alumni.year_id', '=', 'years.id')
                ->orderBy('years.name', $sortOrder)
                ->select('alumni.*');
        } elseif (in_array($sortBy, ['nim', 'nama', 'status', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $filename = 'alumni_' . date('YmdHis') . '.xlsx';
        return Excel::download(new AlumniExport($query), $filename);
    }
}
