<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Models\DataSensor;
use Illuminate\Support\Facades\Http; // Pastikan ini ada
use Illuminate\Support\Facades\Log;  // Pastikan ini ada

class AntaresController extends Controller
{
    /**
     * Method ini akan MENARIK data terbaru dari Antares
     * lalu menyimpannya ke database.
     */
    public function fetchAndStore()
    {
        // --- KONFIGURASI WAJIB ---
        // Pastikan semua kredensial ini sudah benar dan sesuai dengan dashboard Antares Anda
        $antaresAccessKey = 'e1bae4fe2a02c052:56e9fe0264f0476f'; 
        $appName = 'ta_lora';
        
        $sensors = Sensor::all();

        if ($sensors->isEmpty()) {
            Log::info('Tidak ada sensor yang terdaftar di database. Proses fetch dihentikan.');
            // Untuk debug, kita tampilkan pesan langsung
            return "Tidak ada sensor yang terdaftar di database. Proses dihentikan.";
        }

        // Loop akan berjalan untuk setiap sensor yang ada di tabel 'sensors' Anda
        foreach ($sensors as $sensor) {
            $deviceName = $sensor->device_name;

            Log::info("Mencoba mengambil data untuk perangkat: {$deviceName}");

            $url = "https://platform.antares.id:8443/~/antares-cse/antares-id/{$appName}/{$deviceName}/la";

            try {
                // Lakukan HTTP GET Request ke Antares
                $response = Http::withHeaders([
                    'X-M2M-Origin' => $antaresAccessKey,
                    'Content-Type' => 'application/json;ty=4',
                    'Accept' => 'application/json',
                ])->get($url);

                // ==========================================================
                // BARIS DEBUG: Ini akan menghentikan skrip dan menampilkan respons dari Antares
                // ==========================================================
                dd($response->json()); 

                // Kode di bawah ini tidak akan berjalan karena ada dd() di atas.
                // Ini hanya untuk referensi.
                if ($response->successful()) {
                    // ... (logika penyimpanan data)
                }

            } catch (\Exception $e) {
                // Jika ada error koneksi (misal: timeout, tidak bisa terhubung), tampilkan juga
                dd("Terjadi Exception: " . $e->getMessage());
            }
        }

        return "Proses selesai."; // Baris ini tidak akan pernah tercapai karena ada dd() di atas
    }
}
