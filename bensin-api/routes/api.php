<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WarungController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Admin\PelangganController;
use App\Http\Controllers\Admin\PemilikController;
use App\Http\Controllers\Admin\PersebaranWarungController;
/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Tidak Perlu Login)
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
 // Admin Routes
    Route::post('/admin/login', [AdminAuthController::class, 'login']);
    Route::post('/admin/register', [AdminAuthController::class, 'register']);


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Wajib Login - Sanctum)
|--------------------------------------------------------------------------
*/


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/warung', [WarungController::class, 'index']);
    Route::post('/warung', [WarungController::class, 'store']);
    Route::put('/warung', [WarungController::class, 'update']);
    Route::put('/warung/stok', [WarungController::class, 'updateStok']);
});


Route::middleware('auth:sanctum')->group(function () {

    // Ambil data user yang login
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });


    // Logout
    Route::post('/logout', function (Request $request) {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    });

   
});

// Admin Routes - Pelanggan
Route::prefix('admin')->group(function () {

    Route::get('/pelanggan', [PelangganController::class, 'index']);
    Route::post('/pelanggan', [PelangganController::class, 'store']);
    Route::get('/pelanggan/{id}', [PelangganController::class, 'show']);
    Route::put('/pelanggan/{id}', [PelangganController::class, 'update']);
    Route::delete('/pelanggan/{id}', [PelangganController::class, 'destroy']);

});


// Admin Routes - Pemilik Warung
Route::prefix('admin')->group(function () {
    Route::apiResource('pemilik', PemilikController::class);
});

// Admin Routes - Persebaran Warung
Route::prefix('admin')->group(function () {
    Route::apiResource('persebaran', PersebaranWarungController::class);
});