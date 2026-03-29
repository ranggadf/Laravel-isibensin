<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WarungController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\Admin\PemilikController;
use App\Http\Controllers\Admin\PersebaranWarungController;
use App\Http\Controllers\OrderController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Tidak Perlu Login)
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Admin Auth
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/register', [AdminAuthController::class, 'register']);

// Data warung bisa diakses customer tanpa login
Route::get('/warung', [WarungController::class, 'getwarung']);


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Login Required - Sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // WARUNG (Owner)
     Route::get('/owner/warung', [WarungController::class, 'index']);
    Route::post('/warung', [WarungController::class, 'store']);
   
    Route::put('/warung', [WarungController::class, 'update']);
    Route::put('/warung/stok', [WarungController::class, 'updateStok']);

    // USER
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    Route::put('/user', [AuthController::class, 'updateUser']);

    Route::post('/logout', function (Request $request) {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    });

});


/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    // Pelanggan
    Route::get('/pelanggan', [PelangganController::class, 'index']);
    Route::post('/pelanggan', [PelangganController::class, 'store']);
    Route::get('/pelanggan/{id}', [PelangganController::class, 'show']);
    Route::put('/pelanggan/{id}', [PelangganController::class, 'update']);
    Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy']);

    // Pemilik Warung
    Route::apiResource('pemilik', PemilikController::class);

    // Persebaran Warung
    Route::apiResource('persebaran', PersebaranWarungController::class);

});

// 🔥 ROUTES PESANAN (OrderController)

Route::middleware('auth:sanctum')->group(function () {

   Route::get('/orders', [OrderController::class, 'index']);
Route::get('/my-orders', [OrderController::class, 'myOrders']);
Route::get('/owner/orders', [OrderController::class, 'ownerOrders']);
Route::post('/orders', [OrderController::class, 'store']);
Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
});