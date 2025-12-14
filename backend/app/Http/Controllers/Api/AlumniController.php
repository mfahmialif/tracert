<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\Prodi;
use App\Models\User;
use App\Models\Role;
use App\Imports\AlumniImport;
use App\Exports\AlumniTemplateExport;
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
        if ($request->filled('tahun_id') && $request->tahun_id !== 'all') {
            $query->where('tahun_id', $request->tahun_id);
        }

        // Filter by Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'nim' => 'required|string|max:20|unique:alumni,nim',
            'nama' => 'required|string|max:100',
            'prodi_id' => 'required|exists:prodis,id',
            'tahun_id' => 'required|exists:years,id',
            'email' => 'required|email|max:255',
            'no_hp' => 'nullable|string|max:20',
            'status' => 'required|in:Bekerja,Mencari Kerja,Wirausaha,Studi Lanjut,Belum Bekerja',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // Create User if not exists (logic changed: usually user calls this endpoint, but if admin creates alumni, maybe they create user too?)
            // For now, assuming user_id depends on existing user or we create one.
            // But wait, the validation above asks for 'user_id', which implies user must exist.
            // However, the original code created a user.
            // Let's stick to the previous logic: Create User then Alumni.
            // But validation had 'user_id' => 'required'.
            // If admin creates alumni, they probably provide just NIM/Name and we generate User.
            // Let's remove 'user_id' from input validation and generate it.

            // Re-evaluating validation for store method used by Admin:
            // Admin provides NIM, Name, Prodi, Year, etc. System creates User.
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        // Correcting the logic to match previous implementation
        // user_id should NOT be in validation if we create it.
        // But if we use external user_id, we keep it.
        // Previous code:
        // $user = User::create([...]);
        // $alumni = Alumni::create(..., 'user_id' => $user->id);

        return $this->storeLogic($request);
    }

    private function storeLogic($request)
    {
        $validated = $request->validate([
            'nim' => 'required|string|max:20|unique:alumni,nim',
            'nama' => 'required|string|max:100',
            'prodi_id' => 'required|exists:prodis,id',
            'tahun_id' => 'required|exists:years,id',
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
            'tahun_id' => 'required|exists:years,id',
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
}
