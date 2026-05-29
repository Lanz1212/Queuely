<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Public\Kiosk;
use App\Livewire\Public\Display;
use App\Livewire\Public\Tracking;

/**
 * Routing Aplikasi
 * Mendefinisikan URL yang dapat diakses oleh publik dan pengguna sistem.
 */

// Mengarahkan halaman utama langsung ke halaman login
Route::get('/', function () {
    return redirect()->route('login');
});

// Halaman login untuk Admin dan Operator
Route::get('/login', Login::class)->name('login');

// Proses logout dan menghentikan sesi pengguna
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Halaman antarmuka publik
Route::get('/kiosk', Kiosk::class)->name('kiosk');         // Anjungan mandiri pengambilan nomor
Route::get('/display', Display::class)->name('display');   // Layar monitor pemanggilan antrean
Route::get('/track/{qr_code_hash}', Tracking::class)->name('tracking'); // Pelacakan status antrean via QR
