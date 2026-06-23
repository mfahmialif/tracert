<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role')->withCount('alumni')->latest('id');

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($builder) use ($search) {
                $builder->where('username', 'like', "%{$search}%")
                    ->orWhereHas('role', fn ($role) => $role->where('role_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->integer('role_id'));
        }

        return response()->json($query->paginate(min($request->integer('per_page', 15), 100)));
    }

    public function roles()
    {
        return response()->json([
            'data' => Role::withCount('users')->orderBy('role_id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => ['required', 'integer', Rule::in([Role::SUPERADMIN, Role::ADMIN])],
        ], [
            'role_id.in' => 'Akun alumni harus dibuat melalui modul Alumni agar profilnya lengkap.',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
        ]);

        return response()->json([
            'message' => 'User berhasil dibuat.',
            'data' => $user->load('role'),
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => ['required', 'integer', Rule::exists('roles', 'role_id')],
        ]);

        if ($request->user()->is($user) && (int) $validated['role_id'] !== Role::SUPERADMIN) {
            throw ValidationException::withMessages([
                'role_id' => 'Anda tidak dapat menurunkan level akun yang sedang digunakan.',
            ]);
        }

        if ($user->isSuperAdmin() && (int) $validated['role_id'] !== Role::SUPERADMIN) {
            $this->ensureAnotherSuperadminExists($user);
        }

        if ((int) $validated['role_id'] === Role::ALUMNI && ! $user->alumni()->exists()) {
            throw ValidationException::withMessages([
                'role_id' => 'Level alumni hanya dapat dipakai oleh user yang memiliki profil alumni.',
            ]);
        }

        $user->username = $validated['username'];
        $user->role_id = $validated['role_id'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if (! $request->user()->is($user)) {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'User dan level akses berhasil diperbarui.',
            'data' => $user->load('role'),
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            throw ValidationException::withMessages([
                'user' => 'Anda tidak dapat menghapus akun yang sedang digunakan.',
            ]);
        }

        if ($user->isSuperAdmin()) {
            $this->ensureAnotherSuperadminExists($user);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus.']);
    }

    private function ensureAnotherSuperadminExists(User $user): void
    {
        $hasAnotherSuperadmin = User::where('role_id', Role::SUPERADMIN)
            ->where('id', '!=', $user->id)
            ->exists();

        if (! $hasAnotherSuperadmin) {
            throw ValidationException::withMessages([
                'role_id' => 'Minimal satu akun superadmin harus tetap tersedia.',
            ]);
        }
    }
}
