<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;

class CartController extends Controller
{
    // =========================
    // 🔹 GET CART
    // =========================
    public function index(Request $request)
    {
        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id
        ]);

        return response()->json(
            $cart->load('items')
        );
    }

    // =========================
    // 🔹 TAMBAH ITEM
    // =========================
    public function store(Request $request)
    {
        $cart = Cart::firstOrCreate([
            'user_id' => $request->user()->id
        ]);

        $item = CartItem::create([
            'cart_id' => $cart->id,
            'warung_id' => $request->warung_id,
            'jenis_bbm' => $request->jenis_bbm,
            'qty' => $request->qty,
            'harga' => $request->harga,
        ]);

        return response()->json($item);
    }

    // =========================
    // 🔹 HAPUS ITEM
    // =========================
    public function destroy($id)
    {
        CartItem::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Item dihapus'
        ]);
    }

    // =========================
    // 🔹 KOSONGKAN CART
    // =========================
    public function clear(Request $request)
    {
        $cart = Cart::where('user_id', $request->user()->id)->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'message' => 'Cart dikosongkan'
        ]);
    }
}
