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

    // ✅ VALIDASI INPUT
    $request->validate([
        'warung_id'   => 'required|exists:warungs,id',
        'total_harga' => 'required|numeric',
        'items'       => 'required|array',
        'lat'         => 'required|numeric',
        'lng'         => 'required|numeric',
    ]);

    DB::beginTransaction();

    try {
        $warung = Warung::findOrFail($request->warung_id);

        // ✅ SIMPAN PESANAN
        $order = Order::create([
            'user_id'      => $user->id,
            'warung_id'    => $warung->id,
            'total_harga'  => $request->total_harga,
            'ongkir'       => $request->ongkir ?? 0,
            'jarak'        => $request->jarak ?? 0,
            'status'       => 'pending',
            'lat'          => $request->lat,
            'lng'          => $request->lng,
        ]);

        // ✅ SIMPAN ITEM & KURANGI STOK
        foreach ($request->items as $item) {
            $jenis = strtolower($item['jenis_bbm']); // pertalite / pertamax
            $qty = $item['qty'];

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

            OrderItem::create([
                'order_id' => $order->id,
                'jenis_bbm'=> $item['jenis_bbm'],
                'qty'      => $qty,
                'harga'    => $item['harga'],
            ]);
        }

        // 🔥 SIMPAN PERUBAHAN STOK SEKALI
        $warung->save();

        DB::commit();

        return response()->json([
            'message'  => 'Pesanan berhasil dibuat',
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
    'status' => 'required|in:pending,ditolak,dikonfirmasi,sedang diantar,selesai',
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

// 🔹 Riwayat Pesanan Owner
public function riwayatOwner()
{
    $user = Auth::user();
    $warung = Warung::where('user_id', $user->id)->first();

    if (!$warung) {
        return response()->json([
            'message' => 'Warung tidak ditemukan'
        ], 404);
    }

    $riwayat = Order::where('warung_id', $warung->id)
        ->whereIn('status', ['ditolak','dikonfirmasi','sedang diantar','selesai'])
        ->with(['items','user'])
        ->latest()
        ->get();

    return response()->json($riwayat);
}

// 🔹 Riwayat Pesanan Customer
public function riwayatCustomer()
{
    $riwayat = Order::where('user_id', Auth::id())
        ->whereIn('status', ['ditolak','dikonfirmasi','sedang diantar','selesai'])
        ->with(['items','warung'])
        ->latest()
        ->get();

    return response()->json($riwayat);
}

public function show(Order $order)
{
    // Cek apakah pesanan milik owner
  if ($order->warung->user_id !== auth()->id()) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Load relasi
    $order->load(['user', 'items', 'warung']);

    return response()->json($order);
}
}