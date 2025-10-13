<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

// Rute untuk semua dashboard dengan path folder yang benar
Route::get('/dashboard-distribusi', function () {
    return view('Dashboard.dashboardDsitribusi'); // Diubah di sini
})->name('dashboard.distribusi');

Route::get('/dashboard-nwa', function () {
    return view('Dashboard.dashboardNWA'); // Diubah di sini
})->name('dashboard.nwa');

Route::get('/dashboard-produksi', function () {
    return view('Dashboard.dashboardProduksi'); // Diubah di sini
})->name('dashboard.produksi');

Route::get('/dashboard-sosial', function () {
    return view('Dashboard.dashboardSosial'); // Diubah di sini
})->name('dashboard.sosial');

// --- RUTE BARU UNTUK MENU TIM SOSIAL ---
// Kita buat grup baru agar URL-nya rapi (contoh: /sosial/triwulanan)
Route::prefix('sosial')->name('sosial.')->group(function () {
    
    // /sosial/triwulanan
    Route::get('/triwulanan', function () {
        return view('sosial.sosial-triwulanan');
    })->name('triwulanan');

    // /sosial/kegiatan-triwulanan/seruti
    Route::get('/kegiatan-triwulanan/seruti', function () {
        return view('sosial.seruti');
    })->name('seruti');

    // /sosial/kegiatan-semesteran/sakernas
    Route::get('/kegiatan-semesteran/sakernas', function () {
        return view('sosial.sakernas');
    })->name('sakernas');

    // /sosial/kegiatan-semesteran/susenas
    Route::get('/kegiatan-semesteran/susenas', function () {
        return view('sosial.susenas');
    })->name('susenas');

});