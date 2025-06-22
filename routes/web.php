<?php



use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;

use App\Http\Controllers\DashboardController;

use App\Http\Controllers\SensorController; // Pastikan controller ini ada



/*

|--------------------------------------------------------------------------

| Web Routes

|--------------------------------------------------------------------------

|

| Here is where you can register web routes for your application. These

| routes are loaded by the RouteServiceProvider and all of them will

| be assigned to the "web" middleware group. Make something great!

|

*/



// Route untuk halaman selamat datang (publik)

Route::get('/', function () {

    Route::get('/', [SensorController::class, 'index']);

});



// Grup untuk semua route yang memerlukan login pengguna

// Middleware 'auth' & 'verified' memastikan pengguna harus login dan terverifikasi email (jika fitur verifikasi aktif)

Route::middleware(['auth', 'verified'])->group(function () {



    // Route untuk Dashboard Utama

    // URL: /dashboard -> Nama: 'dashboard'

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');



    // Route untuk Halaman Monitoring

    // URL: /monitoring -> Nama: 'monitoring'

    // Ini adalah route yang dibutuhkan oleh link href="{{ route('monitoring') }}" pada file dashboard.blade.php Anda.

    Route::get('/monitoring', [SensorController::class, 'index'])->name('monitoring');



    // Route untuk halaman Profil Pengguna

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



    // Route untuk Aksi Toggle Aktuator (dieksekusi oleh JavaScript)

    // Ini adalah endpoint API-like untuk tombol ON/OFF

    Route::post('/sensor/{parameter}/toggle', [SensorController::class, 'toggle'])->name('sensor.toggle');



});





// Route bawaan Laravel Breeze/Jetstream untuk otentikasi (login, register, dll.)

require __DIR__.'/auth.php';