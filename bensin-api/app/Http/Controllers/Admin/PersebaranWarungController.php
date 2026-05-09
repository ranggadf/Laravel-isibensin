<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PersebaranWarung;

class PersebaranWarungController extends Controller
{
    /**
     * Ambil semua data warung
     */
    public function index()
    {
        $data = PersebaranWarung::all();
        return response()->json($data);
    }

    /**
     * Simpan warung baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_warung' => 'required|string|max:255',
            'lat' => 'required',
            'long' => 'required',
            'alamat_warung' => 'nullable|string',
        ]);

        $warung = PersebaranWarung::create([
            'nama_warung' => $request->nama_warung,
            'lat' => $request->lat,
            'long' => $request->long,
            'alamat_warung' => $request->alamat_warung,
        ]);

        return response()->json([
            'message' => 'Warung berhasil ditambahkan',
            'data' => $warung
        ], 201);
    }

    /**
     * Detail warung
     */
    public function show($id)
    {
        $warung = PersebaranWarung::findOrFail($id);
        return response()->json($warung);
    }

    /**
     * Update warung
     */
    public function update(Request $request, $id)
    {
        $warung = PersebaranWarung::findOrFail($id);

        $request->validate([
            'nama_warung' => 'required|string|max:255',
            'lat' => 'required',
            'long' => 'required',
            'alamat_warung' => 'nullable|string',
        ]);

        $warung->update([
            'nama_warung' => $request->nama_warung,
            'lat' => $request->lat,
            'long' => $request->long,
            'alamat_warung' => $request->alamat_warung,
        ]);

        return response()->json([
            'message' => 'Warung berhasil diperbarui',
            'data' => $warung
        ]);
    }

    /**
     * Hapus warung
     */
    public function destroy($id)
    {
        $warung = PersebaranWarung::findOrFail($id);
        $warung->delete();

        return response()->json([
            'message' => 'Warung berhasil dihapus'
        ]);
    }
}