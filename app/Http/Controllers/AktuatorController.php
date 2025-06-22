<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Models\DataSensor;

class AktuatorController extends Controller
{
    public function toggle(Request $request, Sensor $sensor)
    {
        // Ambil data terakhir dari sensor
        $latest = $sensor->data()->latest('waktu')->first();

        if ($latest) {
            // Toggle status (0 -> 1, atau 1 -> 0)
            $latest->status_aktuator = !$latest->status_aktuator;
            $latest->save();
        }

        return redirect()->route('dashboard');
    }
}
