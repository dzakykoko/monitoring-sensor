<!-- resources/views/welcome.blade.php -->

<x-guest-layout>
    <div class="text-center">
        <h1 class="text-3xl font-bold mb-4 text-gray-800 dark:text-white">
            Selamat Datang di Sistem Monitoring Kualitas Udara
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
            Sistem ini menampilkan data suhu, kelembapan, dan kontrol aktuator secara real-time.
        </p>
        <a href="{{ route('login') }}">
            <button class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded transition">
                Login untuk Monitoring
            </button>
        </a>
    </div>
</x-guest-layout>
