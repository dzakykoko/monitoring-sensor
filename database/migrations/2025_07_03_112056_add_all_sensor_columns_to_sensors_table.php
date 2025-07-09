<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan semua kolom yang hilang untuk data sensor dan status aktuator.
     */
    public function up(): void
    {
        Schema::table('sensors', function (Blueprint $table) {
            // Menambahkan kolom untuk data sensor (setelah kolom 'status')
            $table->decimal('suhu', 8, 2)->nullable()->after('status');
            $table->decimal('kelembapan', 8, 2)->nullable()->after('suhu');
            $table->decimal('co2', 8, 2)->nullable()->after('kelembapan');
            $table->decimal('nh3', 8, 2)->nullable()->after('co2');
            $table->decimal('barometer', 8, 2)->nullable()->after('nh3');
            $table->decimal('latitude', 10, 8)->nullable()->after('barometer');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            
            // Menambahkan kolom untuk status aktuator
            $table->boolean('status_aktuator_suhu')->default(false)->after('longitude');
            $table->boolean('status_aktuator_kelembapan')->default(false)->after('status_aktuator_suhu');
            $table->boolean('status_aktuator_co2')->default(false)->after('status_aktuator_kelembapan');
            $table->boolean('status_aktuator_nh3')->default(false)->after('status_aktuator_co2');
            $table->boolean('status_aktuator_barometer')->default(false)->after('status_aktuator_nh3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sensors', function (Blueprint $table) {
            $table->dropColumn([
                'suhu', 'kelembapan', 'co2', 'nh3', 'barometer', 'latitude', 'longitude',
                'status_aktuator_suhu', 'status_aktuator_kelembapan', 'status_aktuator_co2',
                'status_aktuator_nh3', 'status_aktuator_barometer',
            ]);
        });
    }
};
