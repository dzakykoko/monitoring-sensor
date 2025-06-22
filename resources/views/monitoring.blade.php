<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Kualitas Udara</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Library ApexCharts (sudah ada di code Anda) --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif; /* Menggunakan font Inter */
            background-color: #f0f2f5;
            color: #333;
            overflow-x: hidden; /* Mencegah horizontal scroll */
        }
        .container {
            max-width: 1200px;
        }
        .rounded-lg {
            border-radius: 0.5rem; /* Tailwind default for rounded-lg */
        }
        .shadow-md {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        /* Custom styles for the gauge text/value if needed */
        .apexcharts-datalabels-group text {
            fill: #333 !important; /* Adjust gauge value color */
        }
        .apexcharts-text tspan {
            font-weight: bold; /* Make gauge title bold */
        }
        .actuator-btn {
            transition: background-color 0.3s ease-in-out;
            border-radius: 0.5rem; /* Apply rounded corners */
            padding: 0.75rem 1.25rem; /* Adequate padding */
        }
        .actuator-btn:active {
            transform: translateY(1px); /* Slight press effect */
        }

        /* Responsive adjustments for columns */
        @media (max-width: 768px) {
            .grid-cols-1-md-3-lg-5 {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); /* Auto-fit for mobile */
            }
        }
        @media (min-width: 768px) {
            .md\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .lg\:grid-cols-5 {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center">

    <div class="container mx-auto p-4 md:p-8 flex flex-col items-center w-full">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8 rounded-lg p-4 bg-white shadow-md w-full">Dashboard Monitoring Kualitas Udara</h1>

        <h2 class="text-2xl font-semibold text-gray-700 mt-8 mb-6 text-center w-full">Current Sensor Readings (Gauges)</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8 w-full">
            <div id="gauge-suhu" class="bg-white p-4 rounded-lg shadow-md flex flex-col items-center justify-center"></div>
            <div id="gauge-kelembapan" class="bg-white p-4 rounded-lg shadow-md flex flex-col items-center justify-center"></div>
            <div id="gauge-co2" class="bg-white p-4 rounded-lg shadow-md flex flex-col items-center justify-center"></div>
            <div id="gauge-nh3" class="bg-white p-4 rounded-lg shadow-md flex flex-col items-center justify-center"></div>
            <div id="gauge-barometer" class="bg-white p-4 rounded-lg shadow-md flex flex-col items-center justify-center"></div>
        </div>

        <h2 class="text-2xl font-semibold text-gray-700 mt-8 mb-6 text-center w-full">Historical Sensor Data (Graph)</h2>
        <div class="bg-white p-4 rounded-lg shadow-md mb-8 w-full">
            <div id="chart-historical"></div>
        </div>

        <h2 class="text-2xl font-semibold text-gray-700 mt-8 mb-6 text-center w-full">Actuator Control</h2>
        <div class="bg-white p-4 rounded-lg shadow-md w-full">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 text-center">
                {{-- Aktuator Suhu --}}
                <div class="p-2">
                    <h3 class="font-medium text-gray-700 mb-2">Suhu</h3>
                    <button id="btn-suhu" data-parameter="suhu"
                        class="actuator-btn w-full
                        {{ $latestData?->status_aktuator_suhu ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                        p-3 rounded-lg text-white font-bold transition-colors">
                        {{ $latestData?->status_aktuator_suhu ? 'ON' : 'OFF' }}
                    </button>
                </div>
                {{-- Aktuator Kelembapan --}}
                <div class="p-2">
                    <h3 class="font-medium text-gray-700 mb-2">Kelembapan</h3>
                    <button id="btn-kelembapan" data-parameter="kelembapan"
                        class="actuator-btn w-full
                        {{ $latestData?->status_aktuator_kelembapan ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                        p-3 rounded-lg text-white font-bold transition-colors">
                        {{ $latestData?->status_aktuator_kelembapan ? 'ON' : 'OFF' }}
                    </button>
                </div>
                {{-- Aktuator CO2 --}}
                <div class="p-2">
                    <h3 class="font-medium text-gray-700 mb-2">CO2</h3>
                    <button id="btn-co2" data-parameter="co2"
                        class="actuator-btn w-full
                        {{ $latestData?->status_aktuator_co2 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                        p-3 rounded-lg text-white font-bold transition-colors">
                        {{ $latestData?->status_aktuator_co2 ? 'ON' : 'OFF' }}
                    </button>
                </div>
                {{-- Aktuator NH3 --}}
                <div class="p-2">
                    <h3 class="font-medium text-gray-700 mb-2">NH3</h3>
                    <button id="btn-nh3" data-parameter="nh3"
                        class="actuator-btn w-full
                        {{ $latestData?->status_aktuator_nh3 ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                        p-3 rounded-lg text-white font-bold transition-colors">
                        {{ $latestData?->status_aktuator_nh3 ? 'ON' : 'OFF' }}
                    </button>
                </div>
                {{-- Aktuator Barometer --}}
                <div class="p-2">
                    <h3 class="font-medium text-gray-700 mb-2">Barometer</h3>
                    <button id="btn-barometer" data-parameter="barometer"
                        class="actuator-btn w-full
                        {{ $latestData?->status_aktuator_barometer ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600' }}
                        p-3 rounded-lg text-white font-bold transition-colors">
                        {{ $latestData?->status_aktuator_barometer ? 'ON' : 'OFF' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mengambil data dari variabel PHP yang sudah di-pass oleh Controller
            const initialData = @json($latestData);
            const historicalData = @json($historicalDataFormatted);

            // Fungsi untuk menghitung persentase untuk gauge ApexCharts
            function calculatePercentage(value, min, max) {
                if (value === null || value === undefined) return 0;
                return ((value - min) / (max - min)) * 100;
            }

            // Opsi dasar untuk Gauge (RadialBar Chart)
            function createGaugeOptions(title, seriesValue, min, max, unit = '') {
                // Pastikan seriesValue adalah persentase
                const percentageValue = calculatePercentage(seriesValue, min, max);

                return {
                    chart: {
                        type: 'radialBar',
                        height: 200, // Ketinggian gauge yang disesuaikan
                        fontFamily: 'Inter, sans-serif',
                    },
                    series: [percentageValue],
                    plotOptions: {
                        radialBar: {
                            hollow: {
                                size: '70%',
                            },
                            dataLabels: {
                                show: true,
                                name: {
                                    show: true,
                                    fontSize: '16px',
                                    fontWeight: 600,
                                    offsetY: -10,
                                    color: '#555',
                                },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontWeight: 700,
                                    formatter: function (val) {
                                        // Tampilkan nilai aktual sensor, bukan persentase
                                        return seriesValue + unit;
                                    },
                                    offsetY: 8,
                                    color: '#333',
                                },
                            }
                        }
                    },
                    labels: [title],
                    stroke: {
                        lineCap: 'round',
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'dark',
                            type: 'horizontal',
                            shadeIntensity: 0.5,
                            gradientToColors: ['#FF0000'], // Warna akhir gradien (misalnya merah)
                            inverseColors: true,
                            opacityFrom: 1,
                            opacityTo: 1,
                            stops: [0, 100]
                        },
                    },
                    colors: [
                        function({ value, seriesIndex, w }) {
                            // Warna dinamis berdasarkan nilai actual
                            if (title === 'Suhu') {
                                if (seriesValue < 15) return '#00BFFF'; // Biru muda
                                if (seriesValue >= 15 && seriesValue < 30) return '#27AE60'; // Hijau
                                return '#E74C3C'; // Merah
                            }
                            if (title === 'Kelembapan') {
                                if (seriesValue < 40 || seriesValue > 70) return '#E74C3C'; // Merah
                                return '#27AE60'; // Hijau
                            }
                            if (title === 'CO2') {
                                if (seriesValue < 800) return '#27AE60'; // Hijau
                                if (seriesValue >= 800 && seriesValue < 1500) return '#F0C800'; // Kuning
                                return '#E74C3C'; // Merah
                            }
                            if (title === 'NH3') {
                                if (seriesValue < 10) return '#27AE60';
                                return '#E74C3C';
                            }
                            if (title === 'Barometer') {
                                if (seriesValue >= 980 && seriesValue <= 1020) return '#27AE60';
                                return '#F0C800';
                            }
                            return '#008FFB'; // Default blue
                        }
                    ],
                    // responsive: [ // Responsifitas ApexCharts secara internal
                    //     {
                    //         breakpoint: 768,
                    //         options: {
                    //             chart: { height: 180 }
                    //         }
                    //     },
                    //     {
                    //         breakpoint: 480,
                    //         options: {
                    //             chart: { height: 150 }
                    //         }
                    //     }
                    // ]
                };
            }

            // --- INISIALISASI GAUGE ---
            // Pastikan elemen div sudah ada di HTML dengan ID yang benar
            const gaugeSuhu = new ApexCharts(document.querySelector("#gauge-suhu"), createGaugeOptions('Suhu', initialData?.suhu, 0, 50, '°C'));
            const gaugeKelembapan = new ApexCharts(document.querySelector("#gauge-kelembapan"), createGaugeOptions('Kelembapan', initialData?.kelembapan, 0, 100, '%'));
            const gaugeCo2 = new ApexCharts(document.querySelector("#gauge-co2"), createGaugeOptions('CO2', initialData?.co2, 0, 2000, 'ppm'));
            const gaugeNh3 = new ApexCharts(document.querySelector("#gauge-nh3"), createGaugeOptions('NH3', initialData?.nh3, 0, 500, 'ppm')); // Perlu sesuaikan max NH3
            const gaugeBarometer = new ApexCharts(document.querySelector("#gauge-barometer"), createGaugeOptions('Barometer', initialData?.barometer, 900, 1100, 'hPa')); // Perlu sesuaikan max Barometer
            
            gaugeSuhu.render();
            gaugeKelembapan.render();
            gaugeCo2.render();
            gaugeNh3.render();
            gaugeBarometer.render();

            // --- INISIALISASI GRAFIK HISTORIS ---
            const chartOptions = {
                chart: {
                    type: 'line',
                    height: 350,
                    animations: { enabled: true, easing: 'linear', dynamicAnimation: { speed: 1000 }},
                    toolbar: { show: true },
                    zoom: { enabled: true }
                },
                series: [
                    { name: 'Suhu (°C)', data: historicalData.suhu },
                    { name: 'Kelembapan (%)', data: historicalData.kelembapan },
                    { name: 'CO2 (ppm)', data: historicalData.co2 },
                    { name: 'NH3 (ppm)', data: historicalData.nh3 },
                    { name: 'Barometer (hPa)', data: historicalData.barometer }
                ],
                xaxis: {
                    type: 'category',
                    categories: historicalData.labels,
                    labels: { rotate: -45 },
                    tickPlacement: 'on'
                },
                yaxis: {
                    title: { text: 'Value' }
                },
                stroke: { curve: 'smooth', width: 2 },
                markers: { size: 0 },
                tooltip: {
                    x: { format: 'HH:mm:ss' }
                },
                grid: {
                    borderColor: '#e7e7e7',
                    row: { colors: ['#f3f3f3', 'transparent'], opacity: 0.5 },
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    floating: true,
                    offsetY: -25,
                    offsetX: -5
                },
                colors: ['#FF4560', '#008FFB', '#00E396', '#FEB019', '#775DD0'] // Warna untuk setiap series
            };
            const historicalChart = new ApexCharts(document.querySelector("#chart-historical"), chartOptions);
            historicalChart.render();
            
            // --- FUNGSI REAL-TIME UPDATE UNTUK GAUGE DAN GRAFIK (OPSIONAL, MEMBUTUHKAN API) ---
            // Karena data sudah disiapkan oleh PHP saat halaman dimuat,
            // untuk update real-time TANPA refresh halaman, Anda perlu API endpoint terpisah.
            // Jika Anda hanya mengandalkan refresh halaman, Anda bisa menghapus setInterval ini.
            async function fetchAndUpdateData() {
                try {
                    // Contoh fetch ke API endpoint (Anda harus membuatnya di routes/api.php)
                    // Contoh di routes/api.php:
                    // Route::get('/api/latest-sensor-data', function() {
                    //     return App\Models\DataSensor::latest()->first();
                    // });
                    // Route::get('/api/historical-sensor-data', function() {
                    //     $historicalCollection = App\Models\DataSensor::orderBy('created_at', 'desc')->take(100)->get()->reverse();
                    //     return [
                    //         'labels' => $historicalCollection->pluck('created_at')->map(fn($date) => \Carbon\Carbon::parse($date)->format('H:i:s'))->toArray(),
                    //         'suhu' => $historicalCollection->pluck('suhu')->toArray(),
                    //         'kelembapan' => $historicalCollection->pluck('kelembapan')->toArray(),
                    //         'co2' => $historicalCollection->pluck('co2')->toArray(),
                    //         'nh3' => $historicalCollection->pluck('nh3')->toArray(),
                    //         'barometer' => $historicalCollection->pluck('barometer')->toArray(),
                    //     ];
                    // });


                    const latestResponse = await fetch('/api/latest-sensor-data'); // Ganti dengan URL API Anda
                    const latestDataFetched = await latestResponse.json();

                    // Update Gauges
                    gaugeSuhu.updateSeries([calculatePercentage(latestDataFetched.suhu, 0, 50)]);
                    gaugeKelembapan.updateSeries([calculatePercentage(latestDataFetched.kelembapan, 0, 100)]);
                    gaugeCo2.updateSeries([calculatePercentage(latestDataFetched.co2, 0, 2000)]);
                    gaugeNh3.updateSeries([calculatePercentage(latestDataFetched.nh3, 0, 500)]);
                    gaugeBarometer.updateSeries([calculatePercentage(latestDataFetched.barometer, 900, 1100)]);

                    // PENTING: Untuk mengupdate nilai aktual pada gauge title, Anda perlu
                    // mengubah konfigurasi gauge di createGaugeOptions
                    // atau mengupdate option title secara manual:
                    // gaugeSuhu.updateOptions({plotOptions: {radialBar: {dataLabels: {value: {formatter: (val) => latestDataFetched.suhu + '°C'}}}}}, false, true);

                    // Update Historical Chart (jika Anda ingin real-time updates pada grafik juga)
                    const historicalResponse = await fetch('/api/historical-sensor-data');
                    const historicalDataFetched = await historicalResponse.json();

                    historicalChart.updateOptions({
                        xaxis: {
                            categories: historicalDataFetched.labels
                        }
                    });
                    historicalChart.updateSeries([
                        { name: 'Suhu (°C)', data: historicalDataFetched.suhu },
                        { name: 'Kelembapan (%)', data: historicalDataFetched.kelembapan },
                        { name: 'CO2 (ppm)', data: historicalDataFetched.co2 },
                        { name: 'NH3 (ppm)', data: historicalDataFetched.nh3 },
                        { name: 'Barometer (hPa)', data: historicalDataFetched.barometer }
                    ]);


                } catch (error) {
                    console.error("Error fetching or updating data:", error);
                }
            }
            // Uncomment baris di bawah ini jika Anda sudah membuat API endpoint
            // dan ingin update data setiap 5 detik
            // setInterval(fetchAndUpdateData, 5000);


            // --- LOGIKA AKTUATOR ---
            const actuatorButtons = document.querySelectorAll('.actuator-btn');
            actuatorButtons.forEach(button => {
                button.addEventListener('click', async function() {
                    const parameter = this.dataset.parameter;
                    
                    try {
                        // Perhatikan URL: /api/toggle-actuator/${parameter}
                        // Ini berarti Anda membutuhkan route POST di Laravel
                        const response = await fetch(`/api/toggle-actuator/${parameter}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (!response.ok) {
                            const errorText = await response.text();
                            throw new Error(`HTTP error! status: ${response.status}, message: ${errorText}`);
                        }
                        
                        const result = await response.json();

                        if(result.success) {
                            // Update tampilan tombol aktuator
                            if(result.new_status === 'ON') {
                                this.textContent = 'ON';
                                this.classList.remove('bg-red-500', 'hover:bg-red-600');
                                this.classList.add('bg-green-500', 'hover:bg-green-600');
                            } else {
                                this.textContent = 'OFF';
                                this.classList.remove('bg-green-500', 'hover:bg-green-600');
                                this.classList.add('bg-red-500', 'hover:bg-red-600');
                            }
                            // Opsional: Anda bisa memanggil updateData() di sini
                            // untuk merefresh gauge dan grafik setelah toggle aktuator
                            // await fetchAndUpdateData();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Gagal mengubah status aktuator:', error);
                        alert('Gagal mengubah status aktuator. Pastikan API endpoint sudah benar dan controller berfungsi.');
                    }
                });
            });
        });
    </script>
</body>
</html>