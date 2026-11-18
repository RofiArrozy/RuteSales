<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RouteVisitController;
use App\Http\Controllers\SalesRouteController;
use App\Http\Controllers\SalesUserController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - RuteSales Application
|--------------------------------------------------------------------------
| Pola Arsitektur MVC (Model-View-Controller) dengan definisi Routes yang jelas
| dan pembatasan hak akses berbasis peran (Admin & Sales).
*/

// Halaman utama: dialihkan ke dashboard (atau diarahkan ke login jika belum auth)
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return auth()->user()->isAdmin()
        ? redirect()->route('dashboard')
        : redirect()->route('my-visits.index');
});

// Autentikasi Pengguna & Demo Quick Login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/quick-login/{id}', [AuthController::class, 'quickLogin'])->name('quick.login');

// Group Route yang Terproteksi Autentikasi
Route::middleware('auth')->group(function () {

    // ==========================================
    // AREA KHUSUS ADMIN (Role: Admin)
    // ==========================================
    Route::middleware('admin')->group(function () {
        // Dashboard Metrik Utama
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Modul 1: Pengelolaan Data Toko/Warung Mitra (CRUD)
        Route::resource('stores', StoreController::class);

        // Modul 2: Pengelolaan Data Sales & Pengguna (CRUD)
        Route::resource('sales', SalesUserController::class);

        // Modul 3: Pengelolaan Data Rute Kunjungan Multi-Toko
        Route::prefix('rute')->name('rute.')->group(function () {
            Route::get('/', [SalesRouteController::class, 'index'])->name('index');
            Route::get('/create', [SalesRouteController::class, 'create'])->name('create');
            Route::post('/', [SalesRouteController::class, 'store'])->name('store');

            // URI Dinamis Spesifik Sesuai Persyaratan Proyek: /rute/detail/{id}
            Route::get('/detail/{id}', [SalesRouteController::class, 'detail'])->name('detail');

            Route::get('/{id}/edit', [SalesRouteController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SalesRouteController::class, 'update'])->name('update');
            Route::delete('/{id}', [SalesRouteController::class, 'destroy'])->name('destroy');
        });

        // Laporan Rekapitulasi Berdasarkan Periode
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // ==========================================
    // AREA LAPANGAN / KUNJUNGAN SAYA (Sales & Admin)
    // ==========================================
    Route::prefix('my-visits')->name('my-visits.')->group(function () {
        Route::get('/', [RouteVisitController::class, 'myVisits'])->name('index');
        Route::get('/{id}', [RouteVisitController::class, 'show'])->name('show');
        Route::post('/{id}/checkin', [RouteVisitController::class, 'checkin'])->name('checkin');
    });

    // Alias & Form Check-In Fleksibel
    Route::post('/rute/checkin/{id}', [RouteVisitController::class, 'checkin'])->name('rute.checkin');

    Route::get('/routes/{id}', function ($id) {
        return redirect()->route('rute.detail', $id);
    });

    Route::get('/visits/{id}', function ($id) {
        return redirect()->route('my-visits.show', $id);
    });
});
