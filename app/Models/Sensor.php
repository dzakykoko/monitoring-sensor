<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\DataSensor; // Tambahan penting agar relasi dikenali

class Sensor extends Model
{
    use HasFactory;

    // Nama tabel (optional karena Laravel default-nya plural lowercase)
    protected $table = 'sensors';

    // Kolom yang boleh diisi
    protected $fillable = ['nama_sensor'];

    /**
     * Relasi: Satu Sensor memiliki banyak data sensor
     */
    public function data()
    {
        return $this->hasMany(DataSensor::class);
    }
}
