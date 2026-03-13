<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PemilikController extends Controller
{
    /**
     * Ambil semua data pemilik warung (role = 2)
     */
    public function index()
    {
        $pemilik = User::where('role', 2)
            ->select('id', 'nama', 'email', 'no_hp')
            ->get();

        return response()->json($pemilik);
    }

    /**
     * Tambah pemilik warung baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'no_hp'    => 'required|string|max:20',
        ]);

        $pemilik = User::create([
            'nama'     => $request->nama,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'no_hp'    => $request->no_hp,
            'role'     => 2, // 🔥 role pemilik warung
        ]);

        return response()->json([
            'message' => 'Pemilik warung berhasil ditambahkan',
            'data'    => $pemilik
        ], 201);
    }

    /**
     * Detail pemilik berdasarkan ID
     */
    public function show($id)
    {
        $pemilik = User::where('role', 2)->findOrFail($id);

        return response()->json($pemilik);
    }

    /**
     * Update data pemilik warung
     */
    public function update(Request $request, $id)
    {
        $pemilik = User::where('role', 2)->findOrFail($id);

        $request->validate([
            'nama'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'no_hp' => 'required|string|max:20',
        ]);

        $pemilik->update([
            'nama'  => $request->nama,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        return response()->json([
            'message' => 'Data pemilik warung berhasil diperbarui',
            'data'    => $pemilik
        ]);
    }

    /**
     * Hapus pemilik warung
     */
    public function destroy($id)
    {
        $pemilik = User::where('role', 2)->findOrFail($id);

        $pemilik->delete();

        return response()->json([
            'message' => 'Pemilik warung berhasil dihapus'
        ]);
    }
}