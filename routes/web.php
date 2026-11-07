<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Halaman internal yang dilindungi middleware auth
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    // Tambahkan rute sementara untuk menu sales
    Route::get('/route-visits', function () {
        return "Modul Kunjungan Sales (Dalam Pengembangan)";
    })->name('route-visits.index');

    // Tambahkan rute sementara untuk menu admin
    Route::get('/sales', fn() => 'Modul Sales (Segera Hadir)')->name('sales.index');
    Route::get('/stores', fn() => 'Modul Toko (Segera Hadir)')->name('stores.index');
    Route::get('/routes', fn() => 'Modul Rute (Segera Hadir)')->name('routes.index');
    Route::get('/reports', fn() => 'Modul Laporan (Segera Hadir)')->name('reports.index');
});
