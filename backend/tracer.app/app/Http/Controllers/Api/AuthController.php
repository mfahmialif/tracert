<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial yang diberikan tidak valid.'],
            ]);
        }

        Auth::login($user);

        $user->load(['role', 'alumni.prodi']);

        return response()->json([
            'message' => 'Login berhasil',
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout berhasil']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load(['role', 'alumni.prodi']);

        return response()->json([
            'user' => $this->formatUser($user),
        ]);
    }

    private function formatUser($user)
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'role' => [
                'id' => $user->role->role_id,
                'name' => $user->role->role_name,
            ],
            'alumni' => $user->alumni ? [
                'id' => $user->alumni->alumni_id,
                'nim' => $user->alumni->nim,
                'nama' => $user->alumni->nama,
                'prodi' => $user->alumni->prodi ? [
                    'id' => $user->alumni->prodi->prodi_id,
                    'nama' => $user->alumni->prodi->nama_prodi,
                ] : null,
                'tahun_lulus' => $user->alumni->tahun_lulus,
            ] : null,
        ];
    }
}
