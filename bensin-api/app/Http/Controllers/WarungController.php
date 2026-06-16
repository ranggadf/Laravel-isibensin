<?php

namespace App\Http\Controllers;

use App\Models\Warung;
use Illuminate\Http\Request;

class WarungController extends Controller
{
    // 1️⃣ Ambil warung milik owner login
    public function index(Request $request)
    {
        $warung = Warung::where('user_id', $request->user()->id)->first();

        return response()->json($warung);
    }

    // 2️⃣ Buat warung baru
   public function store(Request $request)
{
    // cek apakah owner sudah punya warung
    $existingWarung = Warung::where('user_id', $request->user()->id)->first();

    if ($existingWarung) {
        return response()->json([
            'message' => 'Owner sudah memiliki warung'
        ], 400);
    }

    $request->validate([
    'nama_warung' => 'required',
    'alamat' => 'required',

    'latitude' => 'nullable',
    'longitude' => 'nullable',

    'stok_pertalite' => 'nullable|numeric',
    'stok_pertamax' => 'nullable|numeric',

    'harga_pertalite' => 'nullable|numeric',
    'harga_pertamax' => 'nullable|numeric',

    'foto' => 'nullable|image'
]);

    $fotoPath = null;

    // simpan file foto
    if ($request->hasFile('foto')) {
        $fotoPath = $request->file('foto')->store('warung', 'public');
    }

    $warung = Warung::create([
    'user_id' => $request->user()->id,
    'nama_warung' => $request->nama_warung,
    'alamat' => $request->alamat,
    'latitude' => $request->latitude,
    'longitude' => $request->longitude,
    'stok_pertalite' => $request->stok_pertalite ?? 0,
'stok_pertamax' => $request->stok_pertamax ?? 0,
    'harga_pertalite' => $request->harga_pertalite,
    'harga_pertamax' => $request->harga_pertamax,
    'foto' => $fotoPath
]);

    return response()->json([
        'message' => 'Warung berhasil dibuat',
        'data' => [
            'id' => $warung->id,
            'nama_warung' => $warung->nama_warung,
            'alamat' => $warung->alamat,
            'stok_pertalite' => $warung->stok_pertalite,
            'stok_pertamax' => $warung->stok_pertamax,
            'harga_pertalite' => $warung->harga_pertalite,
            'harga_pertamax' => $warung->harga_pertamax,
            'latitude' => $warung->latitude,
            'longitude' => $warung->longitude,
            'foto' => $warung->foto
                ? asset('storage/' . $warung->foto)
                : null,
        ]
    ], 201);
}
    // 3️⃣ Update warung
  public function update(Request $request)
{
    $warung = Warung::where('user_id', $request->user()->id)->first();

    if (!$warung) {
        return response()->json(['message' => 'Warung tidak ditemukan'], 404);
    }

 $request->validate([
    'nama_warung' => 'required',
    'alamat' => 'required',
    'latitude' => 'required',
    'longitude' => 'required',

    'stok_pertalite' => 'nullable|numeric',
    'stok_pertamax' => 'nullable|numeric',

    'harga_pertalite' => 'nullable|numeric',
    'harga_pertamax' => 'nullable|numeric',

    'foto' => 'nullable|image'
]);

    // ✅ Update field biasa
    $warung->nama_warung = $request->nama_warung;
    $warung->alamat = $request->alamat;
    $warung->latitude = $request->latitude;
    $warung->longitude = $request->longitude;
   $warung->stok_pertalite = $request->stok_pertalite ?? 0;
$warung->stok_pertamax = $request->stok_pertamax ?? 0;
    $warung->harga_pertalite = $request->harga_pertalite;
    $warung->harga_pertamax = $request->harga_pertamax;

    // ✅ Jika ada foto baru
    if ($request->hasFile('foto')) {

        // Hapus foto lama (optional tapi bagus)
        if ($warung->foto && \Storage::disk('public')->exists($warung->foto)) {
            \Storage::disk('public')->delete($warung->foto);
        }

        // Simpan foto baru
        $fotoPath = $request->file('foto')->store('warung', 'public');
        $warung->foto = $fotoPath;
    }

    $warung->save();

    return response()->json([
        'message' => 'Warung berhasil diupdate',
        'data' => [
            'id' => $warung->id,
            'nama_warung' => $warung->nama_warung,
            'alamat' => $warung->alamat,
            'stok_pertalite' => $warung->stok_pertalite,
            'stok_pertamax' => $warung->stok_pertamax,
            'harga_pertalite' => $warung->harga_pertalite,
            'harga_pertamax' => $warung->harga_pertamax,
            'latitude' => $warung->latitude,
            'longitude' => $warung->longitude,
            'foto' => $warung->foto 
                ? asset('storage/' . $warung->foto)
                : null,
        ]
    ]);
}
    // 4️⃣ Update stok saja (lebih aman)
    public function updateStok(Request $request)
    {
        $warung = Warung::where('user_id', $request->user()->id)->first();

        $request->validate([
            'stok_pertalite' => 'required|numeric',
            'stok_pertamax' => 'required|numeric',
        ]);

        $warung->update([
            'stok_pertalite' => $request->stok_pertalite,
            'stok_pertamax' => $request->stok_pertamax,
        ]);

        return response()->json([
            'message' => 'Stok berhasil diupdate',
            'data' => $warung
        ]);
    }
    public function getwarung()
{
    $warung = Warung::all();

    return response()->json($warung);
}

public function getWarungAdmin()
{
    $warung = Warung::with('user')->get();

    return response()->json(
        $warung->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->user->name ?? '-',
                'email' => $item->user->email ?? '-',
                'no_hp' => $item->user->no_hp ?? '-',

                'warung' => [
                    'id' => $item->id,
                    'nama_warung' => $item->nama_warung,
                    'alamat' => $item->alamat,
                    'stok_pertalite' => $item->stok_pertalite,
                    'stok_pertamax' => $item->stok_pertamax,
                    'harga_pertalite' => $item->harga_pertalite,
                    'harga_pertamax' => $item->harga_pertamax,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'foto' => $item->foto
    ? asset('storage/' . $item->foto)
    : null,
                ]
            ];
        })
    );
}
}