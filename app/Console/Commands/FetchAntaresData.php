<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http; // <-- Tambahkan ini
use Illuminate\Support\Facades\Log;  // <-- Tambahkan ini
use App\Models\Sensor;              // <-- Tambahkan ini
use App\Models\DataSensor;          // <-- Tambahkan ini

class FetchAntaresData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-antares-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch the latest sensor data from Antares platform.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // ==================================================================
        // LOGIKA DARI fetchAndStore() DITEMPATKAN LANGSUNG DI SINI
        // ==================================================================

        $this->info('Starting to fetch data from Antares...');

        // --- KONFIGURASI WAJIB ---
        $antaresAccessKey = 'e1bae4fe2a02c052:56e9fe0264f0476f'; // Pastikan ini benar
        $appName = 'ta_lora';
        
        $sensors = Sensor::all();

        if ($sensors->isEmpty()) {
            $this->warn('No sensors registered in the database. Stopping process.');
            return 0; // Hentikan command
        }

        foreach ($sensors as $sensor) {
            $deviceName = $sensor->device_name;
            Log::info("Processing device: {$deviceName}");

            $url = "https://platform.antares.id:8443/~/antares-cse/antares-id/{$appName}/{$deviceName}/la";

            try {
                $response = Http::withHeaders([
                    'X-M2M-Origin' => $antaresAccessKey,
                    'Content-Type' => 'application/json;ty=4',
                    'Accept' => 'application/json',
                ])->get($url);

                if ($response->successful()) {
                    $data = $response->json()['m2m:cin'];
                    $contentData = json_decode($data['con'], true);

                    if (empty($contentData)) {
                        Log::warning("Data content (con) for device '{$deviceName}' is empty or invalid.");
                        continue;
                    }

                    DataSensor::create([
                        'sensor_id'        => $sensor->id,
                        'suhu'             => $contentData['suhu'] ?? null,
                        'kelembapan'       => $contentData['kelembapan'] ?? null,
                        'co2'              => $contentData['co2'] ?? null,
                        'nh3'              => $contentData['nh3'] ?? null,
                        'barometer'        => $contentData['barometer'] ?? null,
                        'wind_direction'   => $contentData['wind_direction'] ?? null,
                        'gps_latitude'     => $contentData['gps_latitude'] ?? null,
                        'gps_longitude'    => $contentData['gps_longitude'] ?? null,
                        'status_aktuator_suhu'         => $contentData['status_aktuator_suhu'] ?? false,
                        'status_aktuator_co2'          => $contentData['status_aktuator_co2'] ?? false,
                        'status_aktuator_kelembapan'   => $contentData['status_aktuator_kelembapan'] ?? false,
                        'status_aktuator_barometer'    => $contentData['status_aktuator_barometer'] ?? false,
                        'status_aktuator_nh3'          => $contentData['status_aktuator_nh3'] ?? false,
                    ]);

                    Log::info("SUCCESS: Data from sensor ID: {$sensor->id} ({$deviceName}) has been saved.");
                    $this->info("SUCCESS: Data for {$deviceName} saved.");

                } else {
                    Log::error("Failed to fetch data for device '{$deviceName}'. Status: " . $response->status() . ", Body: " . $response->body());
                    $this->error("FAILED to fetch data for {$deviceName}. Check logs.");
                }

            } catch (\Exception $e) {
                Log::error("Exception while fetching data for device '{$deviceName}': " . $e->getMessage());
                $this->error("An exception occurred for {$deviceName}. Check logs.");
                continue;
            }
        }

        $this->info('Finished fetching data from Antares.');
        return 0; // Sinyal bahwa command berhasil
    }
}
