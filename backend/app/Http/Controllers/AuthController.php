<?php

namespace App\Http\Controllers;

use App\Models\UserWeb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->username;
        $password = $request->password;

        // Cari user di tabel user_web
        $user = UserWeb::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Username tidak ditemukan'
            ], 401);
        }

        // Check status
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'User tidak aktif. Hubungi administrator.'
            ], 401);
        }

        // Verify password
        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password salah'
            ], 401);
        }

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nip' => $user->nip,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'no_telp' => $user->no_telp,
                    'role' => $user->role,
                    'menu_permissions' => $user->menu_permissions,
                    'foto_profil' => $user->foto_profil,
                ]
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ], 200);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nip' => $user->nip,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'no_telp' => $user->no_telp,
                    'role' => $user->role,
                    'menu_permissions' => $user->menu_permissions,
                    'foto_profil' => $user->foto_profil,
                ]
            ]
        ], 200);
    }
}
