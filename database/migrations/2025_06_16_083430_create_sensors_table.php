<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('sensors', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Nama sensor (misal: Sensor 1, Sensor 2, dll.)
            $table->string('nama_sensor')->unique();

            // Nilai suhu dalam derajat Celsius
            $table->float('suhu')->nullable();

            // Nilai kelembapan dalam persen
            $table->float('kelembapan')->nullable();

            // Status ON/OFF (1 = ON, 0 = OFF)
            $table->boolean('status')->default(0);

            // Timestamp untuk created_at dan updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sensors');
    }
};