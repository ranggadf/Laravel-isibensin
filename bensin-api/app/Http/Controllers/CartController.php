<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;       // Model Cart (keranjang utama)
use App\Models\CartItem;   // Model item di dalam cart

class CartController extends Controller
{
    // =========================
    // 🔹 AMBIL CART USER (GET CART)
    // =========================
    public function index(Request $request)
    {
        // Ambil cart user yang sedang login
        // Jika belum ada, maka akan dibuat otomatis
        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id
        ]);

        // Load relasi items + warung (biar data lengkap)
        return response()->json(
            $cart->load('items.warung')
        );
    }

    // =========================
    // 🔹 TAMBAH ITEM KE CART
    // =========================
    public function store(Request $request)
    {
        // Ambil atau buat cart user login
        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id
        ]);

        // Simpan item baru ke tabel cart_items
        $item = CartItem::create([
            'cart_id' => $cart->id, // relasi ke cart utama
            'warung_id' => $request->warung_id, // warung tujuan
            'jenis_bbm' => $request->jenis_bbm, // jenis bahan bakar
            'qty' => $request->qty, // jumlah liter / item
            'harga' => $request->harga, // harga per item
        ]);

        // Return data item yang baru dibuat
        return response()->json($item);
    }

    // =========================
    // 🔹 HAPUS 1 ITEM CART
    // =========================
    public function destroy($id)
    {
        // Cari item berdasarkan ID lalu hapus
        CartItem::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Item dihapus'
        ]);
    }

    // =========================
    // 🔹 KOSONGKAN SELURUH CART
    // =========================
    public function clear(Request $request)
    {
        // Ambil cart user login
        $cart = Cart::where('user_id', $request->user()->id)->first();

        // Jika cart ada, hapus semua item di dalamnya
        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'message' => 'Cart dikosongkan'
        ]);
    }
}