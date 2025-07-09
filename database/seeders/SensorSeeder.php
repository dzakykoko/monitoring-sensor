<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sensor;
use Carbon\Carbon;

// PASTIKAN NAMA KELAS ADALAH SensorSeeder
class SensorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menggunakan updateOrCreate untuk memastikan data ada dan terisi dengan benar.
        Sensor::updateOrCreate(
            ['device_name' => 'ESP32-01'], // Kunci untuk mencari data
            [
                // Nilai yang akan diisi atau diperbarui
                'location' => 'Laboratorium Sidang',
                'status' => 1,
                'suhu' => 25.5,
                'kelembapan' => 60.0,
                'co2' => 450.0,
                'nh3' => 5.0,
                'barometer' => 1012.5,
                'latitude' => -6.9175,
                'longitude' => 107.6191,
                'status_aktuator_suhu' => false,
                'status_aktuator_kelembapan' => false,
                'status_aktuator_co2' => false,
                'status_aktuator_nh3' => false,
                'status_aktuator_barometer' => false,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
