<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('data_sensors', function (Blueprint $table) {
             $table->unsignedBigInteger('sensor_id')->nullable()->after('id');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_sensors', function (Blueprint $table) {
             $table->dropColumn('sensor_id');
            //
        });
    }
};
