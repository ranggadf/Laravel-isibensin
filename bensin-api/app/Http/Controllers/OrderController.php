<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Warung;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // ✅ 1. SIMPAN PESANAN (CHECKOUT)
    public function store(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'warung_id' => 'required',
        'total_harga' => 'required',
        'items' => 'required|array'
    ]);

    DB::beginTransaction();

    try {

        $warung = Warung::findOrFail($request->warung_id);

        $order = Order::create([
            'user_id' => $user->id,
            'warung_id' => $warung->id,
            'total_harga' => $request->total_harga,
            'ongkir' => $request->ongkir ?? 0,
            'jarak' => $request->jarak ?? 0,
            'status' => 'pending'
        ]);

        foreach ($request->items as $item) {

            $jenis = strtolower($item['jenis_bbm']); // pertalite / pertamax
            $qty = $item['qty'];

            // 🔥 CEK & KURANGI STOK SESUAI JENIS
            if ($jenis === 'pertalite') {

                if ($warung->stok_pertalite < $qty) {
                    throw new \Exception("Stok Pertalite tidak cukup");
                }

                $warung->stok_pertalite -= $qty;

            } elseif ($jenis === 'pertamax') {

                if ($warung->stok_pertamax < $qty) {
                    throw new \Exception("Stok Pertamax tidak cukup");
                }

                $warung->stok_pertamax -= $qty;
            }

            // simpan item
            OrderItem::create([
                'order_id' => $order->id,
                'jenis_bbm' => $item['jenis_bbm'],
                'qty' => $qty,
                'harga' => $item['harga'],
            ]);
        }

        // 🔥 SAVE SEKALI (lebih efisien)
        $warung->save();

        DB::commit();

        return response()->json([
            'message' => 'Pesanan berhasil dibuat',
            'order_id' => $order->id
        ], 201);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'message' => $e->getMessage()
        ], 400);
    }
}
    // ✅ 2. PESANAN CUSTOMER
 public function myOrders()
{
    return response()->json(
        Order::where('user_id', Auth::id())
            ->with(['items', 'warung']) // ✅ BENAR
            ->latest()
            ->get()
    );
}

    // ✅ 3. PESANAN OWNER (WARUNG)
  public function ownerOrders()
{
    $user = Auth::user();

    // 🔥 ambil warung milik owner
    $warung = Warung::where('user_id', $user->id)->first();

    if (!$warung) {
        return response()->json([
            'message' => 'Warung tidak ditemukan'
        ], 404);
    }

    return response()->json(
        Order::where('warung_id', $warung->id)
            ->with(['items', 'warung', 'user']) // 🔥 biar lengkap
            ->latest()
            ->get()
    );
}

    // ✅ 4. UPDATE STATUS PESANAN
  public function updateStatus(Request $request, $id)
{
    $order = Order::findOrFail($id);

    $request->validate([
        'status' => 'required|in:pending,dikonfirmasi,ditolak,diproses,selesai'
    ]);

    $order->update([
        'status' => $request->status
    ]);

    return response()->json([
        'message' => 'Status berhasil diupdate'
    ]);
}

    public function index()
{
    return Order::with(['items', 'warung'])
        ->latest()
        ->get();
}
}