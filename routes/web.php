<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Auth\Login;
use App\Livewire\Public\Kiosk;
use App\Livewire\Public\Display;
use App\Livewire\Public\Tracking;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', Login::class)->name('login');

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

Route::get('/kiosk', Kiosk::class)->name('kiosk');
Route::get('/display', Display::class)->name('display');
Route::get('/track/{qr_code_hash}', Tracking::class)->name('tracking');
