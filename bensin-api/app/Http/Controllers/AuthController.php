<?php

namespace App\Http\Controllers;

use App\Models\User; // Model User untuk akses tabel users
use Illuminate\Http\Request; // Untuk menangkap request dari client
use Illuminate\Support\Facades\Hash; // Untuk hashing password (keamanan)

class AuthController extends Controller
{
    // =========================
    // REGISTER USER BARU
    // =========================
    public function register(Request $request)
    {
        // Validasi input dari user
        $request->validate([
            'nama' => 'required', // wajib diisi
            'email' => 'required|email|unique:users,email', // email harus unik
            'no_hp' => 'required', // nomor HP wajib
            'password' => 'required|min:6', // password minimal 6 karakter
            'role' => 'required|in:1,2' // role hanya boleh 1 atau 2
            // 1 = pelanggan, 2 = pemilik warung
        ]);

        // Menyimpan user baru ke database
        User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'no_hp' => $request->no_hp,

            // password di-hash agar aman (tidak tersimpan plaintext)
            'password' => Hash::make($request->password),

            'role' => $request->role
        ]);

        // Response JSON ke client
        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil'
        ], 201);
    }

    // =========================
    // LOGIN USER
    // =========================
    public function login(Request $request)
    {
        // Validasi input login
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Cek apakah user ada & password cocok
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Hapus token lama (biar hanya 1 sesi aktif)
        $user->tokens()->delete();

        // Membuat token baru (Laravel Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        // Kirim response sukses + token
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'token' => $token,

            // data user dikirim ke frontend
            'data' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'role' => $user->role
            ]
        ], 200);
    }

    // =========================
    // UPDATE PROFIL USER
    // =========================
    public function updateUser(Request $request)
    {
        // Ambil user yang sedang login (dari token)
        $user = $request->user();

        // Validasi input update
        $request->validate([
            'nama' => 'required',
            // email harus unik, tapi boleh tetap email sendiri
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6', // password opsional
            'no_hp' => 'required'
        ]);

        // Update data user
        $user->nama = $request->nama;
        $user->email = $request->email;
        $user->no_hp = $request->no_hp;

        // Jika password diisi, maka update password
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        // Simpan perubahan ke database
        $user->save();

        // Response sukses
        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => $user
        ]);
    }
}