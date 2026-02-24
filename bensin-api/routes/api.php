<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WarungController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (Tidak Perlu Login)
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

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