<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AntaresController;
use App\Http\Controllers\SensorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Route untuk menerima data sensor dari Antares (POST) ---
// Ini adalah route utama yang akan menyimpan data.
Route::post('/antares', [AntaresController::class, 'store']);

// --- Route SEMENTARA untuk verifikasi dari Antares (GET) ---
// Route ini hanya untuk menjawab tes koneksi dari Antares.
// Ia akan mengembalikan pesan sukses agar webhook bisa dibuat.
Route::get('/antares', function () {
    return response()->json(['message' => 'Webhook URL is active and ready for POST requests.']);
});
// -------------------------------------------------------------


// Route untuk toggle aktuator dari dashboard
Route::post('/toggle-actuator/{parameter}', [SensorController::class, 'toggleActuator']);

// Tes endpoint sederhana (optional)
Route::get('/ping', function () {
    return response()->json(['status' => 'API aktif']);
});
