<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        // PERBAIKAN: Memanggil seeder dengan nama yang sudah kita perbaiki, yaitu SensorSeeder
        $this->call([
            SensorSeeder::class,
        ]);
    }
}
