<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Sensor;
use Carbon\Carbon;

class DataSensor extends Model
{
    use HasFactory;

    protected $table = 'data_sensors';

    protected $fillable = [
        'sensor_id',
        'suhu',
        'co2',
        'kelembapan',
        'barometer',
        'nh3',
        'wind_direction',
        'gps_latitude',
        'gps_longitude',
        'status_aktuator_suhu',
        'status_aktuator_co2',
        'status_aktuator_kelembapan',
        'status_aktuator_barometer',
        'status_aktuator_nh3',
        'created_at'
    ];

    protected $casts = [
        'status_aktuator_suhu' => 'boolean',
        'status_aktuator_co2' => 'boolean',
        'status_aktuator_kelembapan' => 'boolean',
        'status_aktuator_barometer' => 'boolean',
        'status_aktuator_nh3' => 'boolean',
    ];

    /**
     * Relasi: Setiap data sensor milik satu sensor
     */
    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }

    /**
     * Accessor: Format waktu sebagai objek Carbon
     */
    public function getWaktuAttribute($value)
    {
        return Carbon::parse($this->created_at);
    }
}