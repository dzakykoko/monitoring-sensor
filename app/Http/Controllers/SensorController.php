<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SensorController extends Controller
{
    /**
     * Membandingkan dua set data sensor untuk menentukan apakah ada perbedaan.
     */
    private function isDataDifferent($latestData, $lastHistoricalData)
    {
        if (!$lastHistoricalData) {
            return true; // Jika tidak ada data historis, anggap berbeda
        }

        // Membandingkan setiap nilai sensor secara individual, tidak termasuk GPS
        return $latestData['suhu'] !== $lastHistoricalData['suhu'] ||
               $latestData['kelembapan'] !== $lastHistoricalData['kelembapan'] ||
               $latestData['co2'] !== $lastHistoricalData['co2'] ||
               $latestData['nh3'] !== $lastHistoricalData['nh3'] ||
               $latestData['barometer'] !== $lastHistoricalData['barometer'];
    }

    /**
     * Fungsi pembantu untuk memparsing payload 'con' dari Antares.
     * Mengembalikan array data sensor.
     */
    private function parseAntaresConPayload($rawCon)
    {
        $parsedCon = json_decode($rawCon, true);
        if ($parsedCon && isset($parsedCon['data'])) {
            $rawDataPayload = $parsedCon['data'];
            $gpsLatitude = null;
            $gpsLongitude = null;

            // Ekstraksi GPS latitude dan longitude
            if (preg_match('/"gps_latitude":\s*(-?\d+\.\d+)/', $rawDataPayload, $latMatches)) {
                $gpsLatitude = (float) $latMatches[1];
            }
            if (preg_match('/"gps_longitude":\s*(-?\d+\.\d+)/', $rawDataPayload, $lonMatches)) {
                $gpsLongitude = (float) $lonMatches[1];
            }

            // Membersihkan payload untuk parsing data sensor (menghapus gps_latitude dan longitude dari string JSON)
            $cleanSensorJson = preg_replace('/, "gps_latitude":.*$/', '', $rawDataPayload);
            $sensorData = json_decode($cleanSensorJson, true);

            if ($sensorData) {
                return [
                    'suhu' => $sensorData['Temp'] ?? null,
                    'kelembapan' => $sensorData['Humd'] ?? null,
                    'co2' => $sensorData['CO2'] ?? null,
                    'nh3' => $sensorData['NH3'] ?? null,
                    'barometer' => $sensorData['barometer'] ?? null,
                    'gps_latitude' => $gpsLatitude,
                    'gps_longitude' => $gpsLongitude,
                ];
            }
        }
        return null;
    }


    public function index()
    {
        $antaresApiKey = env('ANTARES_API_KEY');
        $antaresBaseUrl = env('ANTARES_BASE_URL');
        $antaresDeviceId = env('ANTARES_DEVICE_ID');
        $antaresProjectName = env('ANTARES_PROJECT_NAME');

        if (!$antaresApiKey || !$antaresBaseUrl || !$antaresDeviceId || !$antaresProjectName) {
            \Log::error('Antares API credentials are not set in .env file.');
            session()->flash('error', 'Antares API credentials are not set. Please check your .env file.');
            return view('monitoring', [
                'latestData' => null,
                'historicalDataFormatted' => [
                    'labels' => [],
                    'suhu' => [],
                    'kelembapan' => [],
                    'co2' => [],
                    'nh3' => [],
                    'barometer' => [],
                ],
            ]);
        }

        $latestData = null;
        $historicalDataFormatted = [
            'labels' => [],
            'suhu' => [],
            'kelembapan' => [],
            'co2' => [],
            'nh3' => [],
            'barometer' => [],
        ];

        try {
            // Mengambil Data Terbaru dari Antares
            $latestAntaresUrl = "{$antaresBaseUrl}/{$antaresProjectName}/{$antaresDeviceId}/la";
            \Log::info('Fetching latest data from Antares URL: ' . $latestAntaresUrl);

            $latestResponse = Http::withHeaders([
                'X-M2M-Origin' => $antaresApiKey,
                'Content-Type' => 'application/json;ty=4',
                'Accept' => 'application/json'
            ])->get($latestAntaresUrl);

            if ($latestResponse->successful()) {
                $data = $latestResponse->json();
                if (isset($data['m2m:cin']['con'])) {
                    $parsed = $this->parseAntaresConPayload($data['m2m:cin']['con']);
                    if ($parsed) {
                        $latestData = (object) $parsed; // Konversi ke objek untuk konsistensi di view
                        // Tambahkan properti aktuator (jika ada, default false)
                        $latestData->status_aktuator_suhu = false;
                        $latestData->status_aktuator_kelembapan = false;
                        $latestData->status_aktuator_co2 = false;
                        $latestData->status_aktuator_nh3 = false;
                        $latestData->status_aktuator_barometer = false;
                    } else {
                        \Log::warning('Antares latest "con" data is not valid JSON or missing "data" key: ' . $data['m2m:cin']['con']);
                    }
                } else {
                    \Log::warning('Antares latest response does not contain "m2m:cin.con": ' . json_encode($data));
                }
            } else {
                \Log::error('Failed to fetch latest data from Antares. Status: ' . $latestResponse->status() . ' Body: ' . $latestResponse->body());
                session()->flash('error', 'Gagal mengambil data terbaru dari Antares. Status: ' . $latestResponse->status());
            }

            // Mengambil Data Historis dari Antares
            // Ambil 49 data historis agar total (dengan latestData) menjadi 50 jika latestData berbeda
            $historicalAntaresUrl = "{$antaresBaseUrl}/{$antaresProjectName}/{$antaresDeviceId}?fu=1&ty=4&lim=49";
            \Log::info('Fetching historical data from Antares URL: ' . $historicalAntaresUrl);

            $historicalResponse = Http::withHeaders([
                'X-M2M-Origin' => $antaresApiKey,
                'Content-Type' => 'application/json;ty=4',
                'Accept' => 'application/json'
            ])->get($historicalAntaresUrl);

            if ($historicalResponse->successful()) {
                $historicalAntaresData = $historicalResponse->json();
                $historicalCin = $historicalAntaresData['m2m:cnt']['cin'] ?? [];

                $labels = [];
                $suhu = [];
                $kelembapan = [];
                $co2 = [];
                $nh3 = [];
                $barometer = [];

                // Memproses data historis, membalikkan urutan agar dari yang paling lama ke yang terbaru
                foreach (array_reverse($historicalCin) as $item) {
                    if (isset($item['con'])) {
                        $parsed = $this->parseAntaresConPayload($item['con']);
                        if ($parsed) {
                            $createdTime = Carbon::parse($item['ct'])->format('H:i:s');
                            $labels[] = $createdTime;
                            $suhu[] = $parsed['suhu'];
                            $kelembapan[] = $parsed['kelembapan'];
                            $co2[] = $parsed['co2'];
                            $nh3[] = $parsed['nh3'];
                            $barometer[] = $parsed['barometer'];
                        } else {
                            \Log::warning('Historical "con" data is not valid JSON or missing "data" key in item: ' . $item['con']);
                        }
                    } else {
                        \Log::warning('Historical item missing "con" property: ' . json_encode($item));
                    }
                }

                // Tambahkan latestData ke historicalData jika berbeda
                if ($latestData) {
                    $lastHistoricalData = count($suhu) > 0 ? [
                        'suhu' => end($suhu),
                        'kelembapan' => end($kelembapan),
                        'co2' => end($co2),
                        'nh3' => end($nh3),
                        'barometer' => end($barometer),
                    ] : null;

                    // Pastikan latestData yang digunakan untuk perbandingan adalah array
                    if ($this->isDataDifferent((array)$latestData, $lastHistoricalData)) {
                        $labels[] = Carbon::now()->format('H:i:s');
                        $suhu[] = $latestData->suhu;
                        $kelembapan[] = $latestData->kelembapan;
                        $co2[] = $latestData->co2;
                        $nh3[] = $latestData->nh3;
                        $barometer[] = $latestData->barometer;

                        // Batasi jumlah entri menjadi 50 (49 historis + 1 terbaru)
                        if (count($labels) > 50) {
                            array_shift($labels);
                            array_shift($suhu);
                            array_shift($kelembapan);
                            array_shift($co2);
                            array_shift($nh3);
                            array_shift($barometer);
                        }
                    }
                }

                $historicalDataFormatted = [
                    'labels' => $labels,
                    'suhu' => $suhu,
                    'kelembapan' => $kelembapan,
                    'co2' => $co2,
                    'nh3' => $nh3,
                    'barometer' => $barometer,
                ];
            } else {
                \Log::error('Failed to fetch historical data from Antares. Status: ' . $historicalResponse->status() . ' Body: ' . $historicalResponse->body());
                session()->flash('error', 'Gagal mengambil data historis dari Antares. Status: ' . $historicalResponse->status());
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching data from Antares: ' . $e->getMessage());
            session()->flash('error', 'Terjadi kesalahan saat menghubungkan ke Antares API: ' . $e->getMessage());
        }

        return view('monitoring', compact('latestData', 'historicalDataFormatted'));
    }

    public function getSensorData()
    {
        $antaresApiKey = env('ANTARES_API_KEY');
        $antaresBaseUrl = env('ANTARES_BASE_URL');
        $antaresDeviceId = env('ANTARES_DEVICE_ID');
        $antaresProjectName = env('ANTARES_PROJECT_NAME');

        if (!$antaresApiKey || !$antaresBaseUrl || !$antaresDeviceId || !$antaresProjectName) {
            return response()->json(['error' => 'Antares API credentials are not set.'], 500);
        }

        $latestData = null;
        $historicalDataFormatted = [
            'labels' => [],
            'suhu' => [],
            'kelembapan' => [],
            'co2' => [],
            'nh3' => [],
            'barometer' => [],
        ];

        try {
            // Mengambil Data Terbaru
            $latestAntaresUrl = "{$antaresBaseUrl}/{$antaresProjectName}/{$antaresDeviceId}/la";
            $latestResponse = Http::withHeaders([
                'X-M2M-Origin' => $antaresApiKey,
                'Content-Type' => 'application/json;ty=4',
                'Accept' => 'application/json'
            ])->get($latestAntaresUrl);

            if ($latestResponse->successful()) {
                $data = $latestResponse->json();
                if (isset($data['m2m:cin']['con'])) {
                    $parsed = $this->parseAntaresConPayload($data['m2m:cin']['con']);
                    if ($parsed) {
                        $latestData = $parsed;
                    }
                }
            }

            // Mengambil Data Historis
            $historicalAntaresUrl = "{$antaresBaseUrl}/{$antaresProjectName}/{$antaresDeviceId}?fu=1&ty=4&lim=49";
            $historicalResponse = Http::withHeaders([
                'X-M2M-Origin' => $antaresApiKey,
                'Content-Type' => 'application/json;ty=4',
                'Accept' => 'application/json'
            ])->get($historicalAntaresUrl);

            if ($historicalResponse->successful()) {
                $historicalAntaresData = $historicalResponse->json();
                $historicalCin = $historicalAntaresData['m2m:cnt']['cin'] ?? [];

                $labels = [];
                $suhu = [];
                $kelembapan = [];
                $co2 = [];
                $nh3 = [];
                $barometer = [];

                foreach (array_reverse($historicalCin) as $item) {
                    if (isset($item['con'])) {
                        $parsed = $this->parseAntaresConPayload($item['con']);
                        if ($parsed) {
                            $createdTime = Carbon::parse($item['ct'])->format('H:i:s');
                            $labels[] = $createdTime;
                            $suhu[] = $parsed['suhu'];
                            $kelembapan[] = $parsed['kelembapan'];
                            $co2[] = $parsed['co2'];
                            $nh3[] = $parsed['nh3'];
                            $barometer[] = $parsed['barometer'];
                        }
                    }
                }

                // Tambahkan latestData ke historicalData jika berbeda
                if ($latestData) {
                    $lastHistoricalData = count($suhu) > 0 ? [
                        'suhu' => end($suhu),
                        'kelembapan' => end($kelembapan),
                        'co2' => end($co2),
                        'nh3' => end($nh3),
                        'barometer' => end($barometer),
                    ] : null;

                    if ($this->isDataDifferent($latestData, $lastHistoricalData)) {
                        $labels[] = Carbon::now()->format('H:i:s');
                        $suhu[] = $latestData['suhu'];
                        $kelembapan[] = $latestData['kelembapan'];
                        $co2[] = $latestData['co2'];
                        $nh3[] = $latestData['nh3'];
                        $barometer[] = $latestData['barometer'];

                        // Batasi jumlah entri menjadi 50
                        if (count($labels) > 50) {
                            array_shift($labels);
                            array_shift($suhu);
                            array_shift($kelembapan);
                            array_shift($co2);
                            array_shift($nh3);
                            array_shift($barometer);
                        }
                    }
                }

                $historicalDataFormatted = [
                    'labels' => $labels,
                    'suhu' => $suhu,
                    'kelembapan' => $kelembapan,
                    'co2' => $co2,
                    'nh3' => $nh3,
                    'barometer' => $barometer,
                ];
            }

            return response()->json([
                'latestData' => $latestData,
                'historicalData' => $historicalDataFormatted
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching sensor data: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch data'], 500);
        }
    }
}