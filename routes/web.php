<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Public\Kiosk;
use App\Livewire\Public\Display;
use App\Livewire\Public\Tracking;

Route::get('/', function () {
    return redirect()->route('kiosk');
});

Route::get('/kiosk', Kiosk::class)->name('kiosk');
Route::get('/display', Display::class)->name('display');
Route::get('/track/{qr_code_hash}', Tracking::class)->name('tracking');
