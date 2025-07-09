<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/monitoring'; // Anda bisa sesuaikan ini, misalnya ke halaman dashboard

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Konfigurasi untuk pembatasan request API (biarkan default)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // BAGIAN PALING PENTING ADA DI SINI
        $this->routes(function () {
            // Instruksi untuk memuat file routes/api.php
            // Semua rute di sini akan otomatis memiliki awalan /api
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Instruksi untuk memuat file routes/web.php
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
