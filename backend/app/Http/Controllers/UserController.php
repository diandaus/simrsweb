<?php

namespace App\Http\Controllers;

use App\Models\UserWeb;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class UserController extends Controller
{
    public function searchPegawai(Request $request)
    {
        try {
            $search = $request->query('q', '');

            $query = Pegawai::select('nik', 'nama', 'jk', 'tmp_lahir', 'tgl_lahir', 'alamat', 'email');

            // Jika ada search query, filter berdasarkan nama atau NIK
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                      ->orWhere('nik', 'like', "%{$search}%");
                });
            }

            // Limit hasil (jika ada search: 50, jika load all: 200)
            $limit = !empty($search) ? 50 : 200;
            $pegawai = $query->orderBy('nama', 'asc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $pegawai
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari data pegawai: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index()
    {
        try {
            $users = UserWeb::select('id', 'username', 'nama', 'nip', 'email', 'no_telp', 'role', 'status', 'menu_permissions', 'foto_profil')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:user_web,username',
            'password' => 'required|string|min:4',
            'nama' => 'required|string',
            'nip' => 'nullable|string',
            'email' => 'nullable|email',
            'no_telp' => 'nullable|string',
            'role' => 'nullable|in:admin,user',
            'menu_permissions' => 'nullable|array',
        ]);

        try {
            $user = UserWeb::create([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'nama' => $request->nama,
                'nip' => $request->nip,
                'email' => $request->email,
                'no_telp' => $request->no_telp,
                'role' => $request->role ?? 'user',
                'status' => 'active',
                'menu_permissions' => $request->menu_permissions,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nama' => $user->nama,
                    'nip' => $user->nip,
                    'email' => $user->email,
                    'no_telp' => $user->no_telp,
                    'role' => $user->role,
                    'status' => $user->status,
                    'menu_permissions' => $user->menu_permissions,
                    'foto_profil' => $user->foto_profil,
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'password' => 'nullable|string|min:4',
            'nama' => 'nullable|string',
            'nip' => 'nullable|string',
            'email' => 'nullable|email',
            'no_telp' => 'nullable|string',
            'role' => 'nullable|in:admin,user',
            'status' => 'nullable|in:active,inactive',
            'menu_permissions' => 'nullable|array',
        ]);

        try {
            $user = UserWeb::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Update fields
            if ($request->has('nama')) {
                $user->nama = $request->nama;
            }
            if ($request->has('nip')) {
                $user->nip = $request->nip;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('no_telp')) {
                $user->no_telp = $request->no_telp;
            }
            if ($request->has('role')) {
                $user->role = $request->role;
            }
            if ($request->has('status')) {
                $user->status = $request->status;
            }
            if ($request->has('menu_permissions')) {
                $user->menu_permissions = $request->menu_permissions;
            }

            // Update password if provided
            if ($request->has('password') && $request->password) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diupdate',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nama' => $user->nama,
                    'nip' => $user->nip,
                    'email' => $user->email,
                    'no_telp' => $user->no_telp,
                    'role' => $user->role,
                    'status' => $user->status,
                    'menu_permissions' => $user->menu_permissions,
                    'foto_profil' => $user->foto_profil,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = UserWeb::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus user: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nip' => 'nullable|string',
        ]);

        try {
            $user = $request->user();

            // Hapus foto lama jika ada
            if ($user->foto_profil) {
                $oldPath = str_replace('/storage/', '', $user->foto_profil);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Upload foto baru
            $file = $request->file('foto');
            $fileName = 'foto_profil_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('foto_profil', $fileName, 'public');

            // Generate full URL untuk foto menggunakan request scheme dan host
            $baseUrl = $request->getSchemeAndHttpHost();
            $fotoUrl = $baseUrl . '/storage/' . $path;
            
            // Update foto_profil di database dengan full URL
            $user->foto_profil = $fotoUrl;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diupload',
                'foto_url' => $fotoUrl
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload foto profil: ' . $e->getMessage()
            ], 500);
        }
    }
}
