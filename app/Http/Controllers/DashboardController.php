<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Models\DataSensor;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $sensors = Sensor::with(['data' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(10);
            }])->get();

            $data = DataSensor::orderBy('created_at', 'desc')->limit(50)->get();

        } catch (\Exception $e) {
            $sensors = collect();
            $data = collect();
        }

        return view('dashboard', compact('sensors', 'data'));
    }
}
