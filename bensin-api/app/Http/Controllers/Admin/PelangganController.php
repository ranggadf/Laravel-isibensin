<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PelangganController extends Controller
{
    /**
     * Ambil semua data pelanggan (role = 1)
     */
    public function index()
    {
        $pelanggan = User::where('role', 1)
            ->select('id', 'nama', 'email', 'no_hp')
            ->get();

        return response()->json($pelanggan);
    }

    /**
     * Tambah pelanggan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'no_hp'     => 'required|string|max:20',
        ]);

        $pelanggan = User::create([
            'nama'     => $request->nama,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'no_hp'     => $request->no_hp,
            'role'     => 1, // khusus pelanggan
        ]);

        return response()->json([
            'message' => 'Pelanggan berhasil ditambahkan',
            'data'    => $pelanggan
        ], 201);
    }

    /**
     * Detail pelanggan berdasarkan ID
     */
    public function show($id)
    {
        $pelanggan = User::where('role', 1)->findOrFail($id);

        return response()->json($pelanggan);
    }

    /**
     * Update data pelanggan
     */
    public function update(Request $request, $id)
    {
        $pelanggan = User::where('role', 1)->findOrFail($id);

        $request->validate([
            'nama'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'no_hp'  => 'required|string|max:20',
        ]);

        $pelanggan->update([
            'nama'  => $request->nama,
            'email' => $request->email,
            'no_hp'  => $request->no_hp,
        ]);

        return response()->json([
            'message' => 'Data pelanggan berhasil diperbarui',
            'data'    => $pelanggan
        ]);
    }

    /**
     * Hapus pelanggan
     */
    public function destroy($id)
    {
        $pelanggan = User::where('role', 1)->findOrFail($id);

        $pelanggan->delete();

        return response()->json([
            'message' => 'Pelanggan berhasil dihapus'
        ]);
    }
}