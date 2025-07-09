<?php

// PASTIKAN NAMESPACE-NYA BENAR: App\Models
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'device_name',
        'location',
        'status',
        'Temp',
        'Humd',
        'CO2',
        'NH3',
        'barometer',
        'latitude',
        'longitude',
        'status_aktuator_suhu',
        'status_aktuator_kelembapan',
        'status_aktuator_co2',
        'status_aktuator_nh3',
        'status_aktuator_barometer',
    ];

    /**
     * The attributes that should be cast.
     * @var array<string, string>
     */
    protected $casts = [
        'status_aktuator_suhu' => 'boolean',
        'status_aktuator_kelembapan' => 'boolean',
        'status_aktuator_co2' => 'boolean',
        'status_aktuator_nh3' => 'boolean',
        'status_aktuator_barometer' => 'boolean',
    ];
}
