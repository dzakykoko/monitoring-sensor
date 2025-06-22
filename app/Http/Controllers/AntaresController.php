namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Models\DataSensor;

class AntaresController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'nama_sensor' => 'required|string',
            'suhu' => 'required|numeric',
            'kelembapan' => 'required|numeric',
            'status_aktuator' => 'required|boolean',
        ]);

        // Cari sensor, jika belum ada maka buat baru
        $sensor = Sensor::firstOrCreate([
            'nama_sensor' => $request->nama_sensor
        ]);

        // Simpan data sensor
        $data = new DataSensor();
        $data->sensor_id = $sensor->id;
        $data->suhu = $request->suhu;
        $data->kelembapan = $request->kelembapan;
        $data->status_aktuator = $request->status_aktuator;
        $data->save();

        return response()->json(['message' => 'Data diterima'], 201);
    }
}
