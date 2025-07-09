<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\AntaresController; // Pastikan ini ada

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Rute untuk mengambil data dari Antares (untuk scheduler)
Route::get('/fetch-antares-data', [AntaresController::class, 'fetchAndStore'])->name('fetch.data');

// Rute untuk menguji koneksi ke Antares secara manual di browser
Route::get('/test-antares-fetch', [AntaresController::class, 'fetchAndStore'])->name('test.fetch');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring', [SensorController::class, 'index'])->name('monitoring');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/sensor/{parameter}/toggle', [SensorController::class, 'toggleActuator'])->name('sensor.toggle');
    Route::get('/sensor/data', [App\Http\Controllers\SensorController::class, 'getSensorData'])->name('sensor.data');
});

require __DIR__.'/auth.php';
