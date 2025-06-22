<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Menggunakan model Anda: 'Sensor'
use App\Models\Sensor;

class SensorController extends Controller
{
    /**
     * Menampilkan halaman monitoring sensor dengan data lengkap.
     */
    public function index()
    {
        // --- PERBAIKAN ---
        // 1. Mengambil data sensor paling baru untuk gauge
        $latestData = Sensor::latest('created_at')->first();

        // 2. Mengambil 100 data historis untuk grafik
        $historicalCollection = Sensor::orderBy('created_at', 'desc')->take(100)->get()->reverse();

        // 3. Memformat data historis agar siap digunakan oleh ApexCharts
        $historicalDataFormatted = [
            'labels' => $historicalCollection->pluck('created_at')->map(function ($date) {
                return $date->format('H:i:s');
            }),
            'suhu'       => $historicalCollection->pluck('suhu'),
            'kelembapan' => $historicalCollection->pluck('kelembapan'),
            'co2'        => $historicalCollection->pluck('co2'),
            'nh3'        => $historicalCollection->pluck('nh3'),
            'barometer'  => $historicalCollection->pluck('barometer'),
        ];

        // 4. Mengirim KEDUA variabel ($latestData dan $historicalDataFormatted) ke view
        return view('monitoring', compact('latestData', 'historicalDataFormatted'));
    }

    /**
     * Mengubah status aktuator dari form web biasa.
     * (Metode ini tidak diubah)
     */
    public function toggle($parameter)
    {
        $sensor = Sensor::first();
        if ($sensor && in_array($parameter, ['status_aktuator_suhu', 'status_aktuator_kelembapan', 'status_aktuator_co2', 'status_aktuator_barometer', 'status_aktuator_nh3'])) {
            $sensor->$parameter = !$sensor->$parameter;
            $sensor->save();
            return back()->with('success', 'Status aktuator ' . str_replace('status_aktuator_', '', $parameter) . ' berhasil diperbarui!');
        }
        return back()->with('error', 'Parameter tidak valid!');
    }


    // --- METODE BARU UNTUK API DASHBOARD ---
    /**
     * Mengubah status (toggle ON/OFF) untuk aktuator via API.
     */
    public function toggleActuator(string $parameter)
    {
        $columnName = match ($parameter) {
            'suhu'       => 'status_aktuator_suhu',
            'kelembapan' => 'status_aktuator_kelembapan',
            'co2'        => 'status_aktuator_co2',
            'nh3'        => 'status_aktuator_nh3',
            'barometer'  => 'status_aktuator_barometer',
            default      => null,
        };

        if (is_null($columnName)) {
            return response()->json(['success' => false, 'message' => 'Parameter aktuator tidak valid.'], 400);
        }

        $latestData = Sensor::latest('created_at')->first();

        if (!$latestData) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data sensor ditemukan.'], 404);
        }

        $latestData->$columnName = !$latestData->$columnName;
        $latestData->save();

        return response()->json([
            'success'    => true,
            'message'    => 'Status aktuator berhasil diubah.',
            'new_status' => (bool)$latestData->$columnName
        ]);
    }
}
