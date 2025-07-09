<x-app-layout>
    {{-- Slot untuk header halaman --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Monitoring Kualitas Udara') }}
        </h2>
    </x-slot>

    {{-- Konten utama halaman --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Card untuk Gauge --}}
            <div class="mb-8">
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-6 text-center">Current Sensor Readings</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
                    <div id="gauge-suhu" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md"></div>
                    <div id="gauge-kelembapan" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md"></div>
                    <div id="gauge-co2" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md"></div>
                    <div id="gauge-nh3" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md"></div>
                    <div id="gauge-barometer" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md"></div>
                </div>
            </div>

            {{-- Card untuk GPS Location --}}
            <div class="mb-8 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-6 text-center">Current GPS Location</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-center">
                    <div class="p-2 border rounded-lg bg-gray-100 dark:bg-gray-700">
                        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Latitude</h3>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $latestData->gps_latitude ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="p-2 border rounded-lg bg-gray-100 dark:bg-gray-700">
                        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Longitude</h3>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                            {{ $latestData->gps_longitude ?? 'N/A' }}
                        </p>
                    </div>
                </div>
                @if (($latestData->gps_latitude ?? null) && ($latestData->gps_longitude ?? null))
                    <div class="text-center mt-4">
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $latestData->gps_latitude }},{{ $latestData->gps_longitude }}"
                           target="_blank" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold">
                            Lihat di Google Maps
                        </a>
                    </div>
                @endif
            </div>

            {{-- Card untuk Grafik Historis --}}
            <div class="mb-8 bg-white dark:bg-gray-800 p-4 rounded-lg shadow-md">
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-300 mb-6 text-center">Historical Sensor Data (Graph)</h2>
                <!-- Legenda Warna -->
    
                <div id="chart-historical"></div>
            </div>
        </div>
    </div>

    {{-- JavaScript --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mengambil data dari variabel PHP
            const latestData = @json($latestData ?? null);
            const historicalData = @json($historicalDataFormatted ?? null);

            // Cek jika data kosong atau tidak valid
            if (!latestData || (latestData.suhu === null && latestData.gps_latitude === null)) {
                console.error("Data sensor (latestData) ditemukan, tetapi nilainya kosong (null). Ini kemungkinan besar karena properti '$fillable' di file model 'app/Models/Sensor.php' tidak lengkap saat seeder dijalankan atau masalah parsing Antares.");
                document.getElementById('chart-historical').innerHTML = '<p class="text-center text-red-500 font-bold py-4">Data Gagal Dimuat! <br> Periksa log server Laravel untuk detail error API Antares.</p>';
                document.querySelectorAll('[id^="gauge-"]').forEach(el => el.style.display = 'none');
                const gpsCard = document.querySelector('.max-w-7xl .mb-8:nth-child(3)');
                if (gpsCard) gpsCard.style.display = 'none';
                return;
            }

            // Fungsi untuk membuat opsi Gauge
            function createGaugeOptions(title, seriesValue, min, max, unit = '') {
                const percentageValue = seriesValue !== null ? ((seriesValue - min) / (max - min)) * 100 : 0;
                return {
                    chart: { type: 'radialBar', height: 200, sparkline: { enabled: true } },
                    series: [percentageValue],
                    plotOptions: {
                        radialBar: {
                            hollow: { size: '70%' },
                            dataLabels: {
                                show: true,
                                name: { show: true, fontSize: '16px', fontWeight: 600, offsetY: -10, color: '#999' },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontWeight: 700,
                                    offsetY: 8,
                                    color: '#111',
                                    formatter: (val) => (seriesValue !== null ? seriesValue : 'N/A') + unit,
                                }
                            }
                        }
                    },
                    labels: [title],
                    stroke: { lineCap: 'round' }
                };
            }

            // Inisialisasi Gauge
            if (latestData.suhu !== null) {
                new ApexCharts(document.querySelector("#gauge-suhu"), createGaugeOptions('Suhu', latestData.suhu, 0, 50, '°C')).render();
            } else { document.querySelector("#gauge-suhu").innerHTML = '<p class="text-center text-gray-500">N/A</p>'; }

            if (latestData.kelembapan !== null) {
                new ApexCharts(document.querySelector("#gauge-kelembapan"), createGaugeOptions('Kelembapan', latestData.kelembapan, 0, 100, '%')).render();
            } else { document.querySelector("#gauge-kelembapan").innerHTML = '<p class="text-center text-gray-500">N/A</p>'; }

            if (latestData.co2 !== null) {
                new ApexCharts(document.querySelector("#gauge-co2"), createGaugeOptions('CO2', latestData.co2, 0, 2000, ' ppm')).render();
            } else { document.querySelector("#gauge-co2").innerHTML = '<p class="text-center text-gray-500">N/A</p>'; }

            if (latestData.nh3 !== null) {
                new ApexCharts(document.querySelector("#gauge-nh3"), createGaugeOptions('NH3', latestData.nh3, 0, 500, ' ppm')).render();
            } else { document.querySelector("#gauge-nh3").innerHTML = '<p class="text-center text-gray-500">N/A</p>'; }

            if (latestData.barometer !== null) {
                new ApexCharts(document.querySelector("#gauge-barometer"), createGaugeOptions('Barometer', latestData.barometer, 900, 1100, ' hPa')).render();
            } else { document.querySelector("#gauge-barometer").innerHTML = '<p class="text-center text-gray-500">N/A</p>'; }

            // Opsi untuk Grafik Historis dengan beberapa sumbu Y
            const chartOptions = {
                chart: { type: 'line', height: 400, toolbar: { show: true }, zoom: { enabled: true } },
                series: [
                    { name: 'Suhu (°C)', data: historicalData.suhu, yAxisIndex: 0 },
                    { name: 'Kelembapan (%)', data: historicalData.kelembapan, yAxisIndex: 1 },
                    { name: 'CO2 (ppm)', data: historicalData.co2, yAxisIndex: 2 },
                    { name: 'NH3 (ppm)', data: historicalData.nh3, yAxisIndex: 3 },
                    { name: 'Barometer (hPa)', data: historicalData.barometer, yAxisIndex: 4 }
                ],
                xaxis: { 
                    type: 'category', 
                    categories: historicalData.labels, 
                    labels: { rotate: -45, style: { colors: '#999' } }, 
                    tickPlacement: 'on' 
                },
                yaxis: [
                    { title: { text: 'Suhu (°C)' }, min: 0, max: 50, labels: { style: { colors: '#FF4560' } } },
                    { title: { text: 'Kelembapan (%)' }, min: 0, max: 100, opposite: true, labels: { style: { colors: '#008FFB' } } },
                    { title: { text: 'CO2 (ppm)' }, min: 0, max: 2000, labels: { style: { colors: '#00E396' } } },
                    { title: { text: 'NH3 (ppm)' }, min: 0, max: 500, opposite: true, labels: { style: { colors: '#FEB019' } } },
                    { title: { text: 'Barometer (hPa)' }, min: 900, max: 1100, labels: { style: { colors: '#775DD0' } } }
                ],
                stroke: { curve: 'smooth', width: 2 },
                markers: { size: 0 },
                tooltip: { x: { format: 'HH:mm:ss' } },
                grid: { borderColor: '#e7e7e7', row: { colors: ['transparent', 'transparent'], opacity: 0.5 } },
                legend: { position: 'top', horizontalAlign: 'center', floating: false, offsetY: 0, itemMargin: { horizontal: 10, vertical: 5 }, labels: { colors: '#999' } },
                colors: ['#FF4560', '#008FFB', '#00E396', '#FEB019', '#775DD0']
            };

            // Inisialisasi Grafik Historis
            let historicalChart = new ApexCharts(document.querySelector("#chart-historical"), chartOptions);
            historicalChart.render();

            // Fungsi untuk memperbarui data
            async function updateSensorData() {
                try {
                    const response = await fetch('/sensor/data', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    if (!response.ok) throw new Error('Failed to fetch sensor data');
                    const data = await response.json();

                    // Perbarui Gauge
                    if (data.latestData) {
                        if (data.latestData.suhu !== null) {
                            new ApexCharts(document.querySelector("#gauge-suhu"), createGaugeOptions('Suhu', data.latestData.suhu, 0, 50, '°C')).render();
                        } else { document.querySelector("#gauge-suhu").innerHTML = '<p class="text-center text-gray-500">N/A</p>'; }
                        if (data.latestData.kelembapan !== null) {
                            new ApexCharts(document.querySelector("#gauge-kelembapan"), createGaugeOptions('Kelembapan', data.latestData.kelembapan, 0, 100, '%')).render();
                        } else { document.querySelector("#gauge-kelembapan").innerHTML = '<p class="text-center text-gray-500">N/A</p>'; }
                        if (data.latestData.co2 !== null) {
                            new ApexCharts(document.querySelector("#gauge-co2"), createGaugeOptions('CO2', data.latestData.co2, 0, 2000, ' ppm')).render();
                        } else { document.querySelector("#gauge-co2").innerHTML = '<p class="text-center text-gray-500">N/A</p>'; }
                        if (data.latestData.nh3 !== null) {
                            new ApexCharts(document.querySelector("#gauge-nh3"), createGaugeOptions('NH3', data.latestData.nh3, 0, 500, ' ppm')).render();
                        } else { document.querySelector("#gauge-nh3").innerHTML = '<p class="text-center text-gray-500">N/A</p>'; }
                        if (data.latestData.barometer !== null) {
                            new ApexCharts(document.querySelector("#gauge-barometer"), createGaugeOptions('Barometer', data.latestData.barometer, 900, 1100, ' hPa')).render();
                        } else { document.querySelector("#gauge-barometer").innerHTML = '<p class="text-center text-gray-500">N/A</p>'; }

                        // Perbarui GPS
                        document.querySelector('.max-w-7xl .mb-8:nth-child(3) .grid .p-2:nth-child(1) p').textContent = data.latestData.gps_latitude ?? 'N/A';
                        document.querySelector('.max-w-7xl .mb-8:nth-child(3) .grid .p-2:nth-child(2) p').textContent = data.latestData.gps_longitude ?? 'N/A';
                        const googleMapsLink = document.querySelector('.max-w-7xl .mb-8:nth-child(3) a');
                        if (data.latestData.gps_latitude && data.latestData.gps_longitude) {
                            googleMapsLink.href = `https://www.google.com/maps/search/?api=1&query=${data.latestData.gps_latitude},${data.latestData.gps_longitude}`;
                            googleMapsLink.style.display = 'block';
                        } else {
                            googleMapsLink.style.display = 'none';
                        }
                    }

                    // Perbarui Grafik Historis
                    if (data.historicalData) {
                        historicalChart.updateSeries([
                            { name: 'Suhu (°C)', data: data.historicalData.suhu, yAxisIndex: 0 },
                            { name: 'Kelembapan (%)', data: data.historicalData.kelembapan, yAxisIndex: 1 },
                            { name: 'CO2 (ppm)', data: data.historicalData.co2, yAxisIndex: 2 },
                            { name: 'NH3 (ppm)', data: data.historicalData.nh3, yAxisIndex: 3 },
                            { name: 'Barometer (hPa)', data: data.historicalData.barometer, yAxisIndex: 4 }
                        ]);
                        historicalChart.updateOptions({
                            xaxis: { categories: data.historicalData.labels }
                        });
                    }
                } catch (error) {
                    console.error('Error updating sensor data:', error);
                }
            }

            // Polling setiap 30 detik
            setInterval(updateSensorData, 30000);

            // Logika untuk tombol Aktuator
            document.querySelectorAll('.actuator-btn').forEach(button => {
                button.addEventListener('click', async function() {
                    const parameter = this.dataset.parameter;
                    try {
                        const response = await fetch(`/sensor/${parameter}/toggle`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        if (!response.ok) { throw new Error(`Server Error: ${response.status}`); }
                        const result = await response.json();
                        if (result.success) {
                            if (result.new_status === 1) {
                                this.textContent = 'ON';
                                this.classList.remove('bg-red-500', 'hover:bg-red-600');
                                this.classList.add('bg-green-500', 'hover:bg-green-600');
                            } else {
                                this.textContent = 'OFF';
                                this.classList.remove('bg-green-500', 'hover:bg-green-600');
                                this.classList.add(' bg-red-500', 'hover:bg-red-600');
                            }
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Gagal mengubah status aktuator:', error);
                        alert('Gagal mengubah status aktuator. Cek console untuk detail.');
                    }
                });
            });
        });
    </script>
    @endpush
</x-app-layout>