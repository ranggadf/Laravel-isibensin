<?php

namespace App\Http\Controllers;

use App\Models\Order;       // Model order utama
use App\Models\OrderItem;   // Item di dalam order
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ambil user login
use App\Models\Warung;      // data warung (stok BBM)
use Illuminate\Support\Facades\DB;   // transaksi database

class OrderController extends Controller
{
    // =====================
    // 1. CHECKOUT / BUAT ORDER
    // =====================
    public function store(Request $request)
    {
        // Ambil user yang sedang login
        $user = Auth::user();

        // Validasi input dari frontend
        $request->validate([
            'warung_id'   => 'required|exists:warungs,id',
            'total_harga'  => 'required|numeric',
            'items'        => 'required|array',
            'lat'          => 'required|numeric',
            'lng'          => 'required|numeric',
        ]);

        // Mulai transaksi agar aman (kalau error semua rollback)
        DB::beginTransaction();

        try {

            // Ambil data warung
            $warung = Warung::findOrFail($request->warung_id);

            // Batas maksimal liter per item
            $MAX_LITER = 4;

            // ================= VALIDASI ITEM =================
            foreach ($request->items as $item) {
                if ($item['qty'] > $MAX_LITER) {
                    return response()->json([
                        'message' => 'Maksimal pembelian 4 liter per item'
                    ], 422);
                }
            }

            // ================= BUAT ORDER =================
            $order = Order::create([
                'user_id'     => $user->id,
                'warung_id'   => $warung->id,
                'total_harga' => $request->total_harga,
                'ongkir'      => $request->ongkir ?? 0,
                'jarak'       => $request->jarak ?? 0,
                'status'      => 'pending',

                // lokasi customer
                'lat'         => $request->lat,
                'lng'         => $request->lng,

                // expired otomatis 30 detik
                'expired_at'  => now()->addSeconds(30),
            ]);

            // ================= SIMPAN ITEM ORDER =================
            foreach ($request->items as $item) {

                $jenis = strtolower($item['jenis_bbm']);
                $qty   = $item['qty'];

                // ================= CEK & KURANGI STOK =================
                if ($jenis === 'pertalite') {
                    if ($warung->stok_pertalite < $qty) {
                        throw new \Exception("Stok Pertalite tidak cukup");
                    }
                    $warung->stok_pertalite -= $qty;
                }

                if ($jenis === 'pertamax') {
                    if ($warung->stok_pertamax < $qty) {
                        throw new \Exception("Stok Pertamax tidak cukup");
                    }
                    $warung->stok_pertamax -= $qty;
                }

                // simpan item order
                OrderItem::create([
                    'order_id'  => $order->id,
                    'jenis_bbm' => $item['jenis_bbm'],
                    'qty'       => $qty,
                    'harga'     => $item['harga'],
                ]);
            }

            // simpan perubahan stok warung
            $warung->save();

            // commit transaksi (berhasil semua)
            DB::commit();

            return response()->json([
                'message'  => 'Pesanan berhasil dibuat',
                'order_id' => $order->id
            ], 201);

        } catch (\Exception $e) {

            // rollback jika error
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    // =====================
    // 2. ORDER CUSTOMER
    // =====================
    public function myOrders()
    {
        // auto expire order yang sudah lewat waktu
        Order::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now())
            ->update(['status' => 'expired']);

        // ambil semua order user
        return Order::where('user_id', Auth::id())
            ->with(['items', 'warung'])
            ->latest()
            ->get();
    }

    // =====================
    // 3. ORDER OWNER
    // =====================
    public function ownerOrders()
    {
        $user = Auth::user();

        // cari warung milik owner
        $warung = Warung::where('user_id', $user->id)->first();

        if (!$warung) {
            return response()->json([
                'message' => 'Warung tidak ditemukan'
            ], 404);
        }

        // auto expire untuk owner
        Order::where('warung_id', $warung->id)
            ->where('status', 'pending')
            ->where('expired_at', '<=', now())
            ->update(['status' => 'expired']);

        return Order::where('warung_id', $warung->id)
            ->with(['items', 'warung', 'user'])
            ->latest()
            ->get();
    }

    // =====================
    // 4. UPDATE STATUS ORDER
    // =====================
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,expired,dikonfirmasi,sedang diantar,selesai',
        ]);

        $order->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Status berhasil diupdate'
        ]);
    }

    // =====================
    // 5. RIWAYAT OWNER
    // =====================
    public function riwayatOwner()
    {
        $user = Auth::user();
        $warung = Warung::where('user_id', $user->id)->first();

        if (!$warung) {
            return response()->json([
                'message' => 'Warung tidak ditemukan'
            ], 404);
        }

        return Order::where('warung_id', $warung->id)
            ->whereIn('status', [
                'selesai',
                'ditolak',
                'expired',
                'sedang diantar'
            ])
            ->with(['items', 'user'])
            ->latest()
            ->get();
    }

    // =====================
    // 6. RIWAYAT CUSTOMER
    // =====================
    public function riwayatCustomer()
    {
        return Order::where('user_id', Auth::id())
            ->whereIn('status', [
                'expired',
                'dikonfirmasi',
                'sedang diantar',
                'selesai'
            ])
            ->with(['items', 'warung'])
            ->latest()
            ->get();
    }

    // =====================
    // 7. DETAIL ORDER
    // =====================
    public function show(Order $order)
    {
        // hanya owner warung yang bisa lihat detail
        if ($order->warung->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $order->load(['user', 'items', 'warung']);
    }

    // =====================
    // 8. HAPUS ORDER
    // =====================
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json([
            'message' => 'Pesanan berhasil dihapus'
        ]);
    }
}