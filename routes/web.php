<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SosialTahunanController;
use App\Http\Controllers\SosialSemesteranController;

// Distribusi
use App\Http\Controllers\DashboardDistribusiController;
use App\Http\Controllers\DistribusiTahunanController;
use App\Http\Controllers\DistribusiTriwulananController;
use App\Http\Controllers\DistribusiBulananController;

// Produksi
use App\Http\Controllers\ProduksiTahunanController;
use App\Http\Controllers\ProduksiCaturwulananController;

use App\Http\Controllers\SerutiController;
use App\Http\Controllers\DashboardNwaController;
use App\Http\Controllers\DashboardProduksiController;
use App\Http\Controllers\DashboardSosialController;
use App\Http\Controllers\PencacahController;
use App\Http\Controllers\PengawasController;

Route::get('/', fn() => view('home'))->name('home');

/* DASHBOARD */
Route::get('/dashboard-distribusi', [DashboardDistribusiController::class, 'index'])->name('dashboard.distribusi');
Route::get('/dashboard-nwa', [DashboardNwaController::class, 'index'])->name('dashboard.nwa');
Route::get('/dashboard-produksi', [DashboardProduksiController::class, 'index'])->name('dashboard.produksi');
Route::get('/dashboard-sosial', [DashboardSosialController::class, 'index'])->name('dashboard.sosial');

/* --- TIM SOSIAL --- */
Route::prefix('sosial')->name('sosial.')->group(function () {
    // CRUD Sosial Tahunan (tanpa halaman create karena pakai modal di index)
    Route::resource('tahunan', SosialTahunanController::class);

    // Resource lain (kalau butuh, biarkan default)
    Route::resource('seruti', SerutiController::class);

    Route::resource('semesteran', SosialSemesteranController::class);

    // Halaman statis lain
    Route::get('/kegiatan-semesteran/susenas', fn() => view('timSosial.susenas'))->name('susenas');
});

/* --- TIM DISTRIBUSI --- */
Route::prefix('tim-distribusi')->name('tim-distribusi.')->group(function () {
    
    // --- ROUTE TAHUNAN ---
    Route::prefix('tahunan')->name('tahunan.')->group(function () {
        Route::get('/search-petugas', [DistribusiTahunanController::class, 'searchPetugas'])->name('searchPetugas');
        Route::post('/bulk-delete', [DistribusiTahunanController::class, 'bulkDelete'])->name('bulkDelete');
        Route::resource('/', DistribusiTahunanController::class)->parameters(['' => 'tahunan']);
    });

    // --- ROUTE TRIWULANAN ---
    Route::prefix('triwulanan')->name('triwulanan.')->group(function () {
        Route::get('/search-petugas', [DistribusiTriwulananController::class, 'searchPetugas'])->name('searchPetugas');
        Route::post('/bulk-delete', [DistribusiTriwulananController::class, 'bulkDelete'])->name('bulkDelete');
        
        // Route untuk proses CRUD
        Route::post('/', [DistribusiTriwulananController::class, 'store'])->name('store');
        Route::get('/{distribusi_triwulanan}/edit', [DistribusiTriwulananController::class, 'edit'])->name('edit');
        Route::put('/{distribusi_triwulanan}', [DistribusiTriwulananController::class, 'update'])->name('update');
        Route::delete('/{distribusi_triwulanan}', [DistribusiTriwulananController::class, 'destroy'])->name('destroy');
        
        // Route utama untuk menampilkan data SPUNP atau SHKK
        Route::get('/{jenisKegiatan}', [DistribusiTriwulananController::class, 'index'])->name('index');
    });

    // --- ROUTE Bulanan ---
    Route::prefix('bulanan')->name('bulanan.')->group(function () {
        Route::get('/search-petugas', [DistribusiBulananController::class, 'searchPetugas'])->name('searchPetugas');
        Route::post('/bulk-delete', [DistribusiBulananController::class, 'bulkDelete'])->name('bulkDelete');
        
        // Route untuk proses CRUD
        Route::post('/', [DistribusiBulananController::class, 'store'])->name('store');
        // Gunakan snake_case untuk parameter agar konsisten
        Route::get('/{distribusi_bulanan}/edit', [DistribusiBulananController::class, 'edit'])->name('edit');
        Route::put('/{distribusi_bulanan}', [DistribusiBulananController::class, 'update'])->name('update');
        Route::delete('/{distribusi_bulanan}', [DistribusiBulananController::class, 'destroy'])->name('destroy');
        
        // Route utama untuk menampilkan data berdasarkan jenis kegiatan
        Route::get('/{jenisKegiatan}', [DistribusiBulananController::class, 'index'])->name('index');
    });
});

/* --- TIM PRODUKSI --- */
Route::prefix('tim-produksi')->name('tim-produksi.')->group(function () {
    
    // --- ROUTE PRODUKSI TAHUNAN ---
    Route::prefix('tahunan')->name('tahunan.')->group(function () {
        Route::get('/search-petugas', [ProduksiTahunanController::class, 'searchPetugas'])->name('searchPetugas');
        Route::post('/bulk-delete', [ProduksiTahunanController::class, 'bulkDelete'])->name('bulkDelete');
        Route::resource('/', ProduksiTahunanController::class)->parameters(['' => 'tahunan']);
    });

    // --- ROUTE PRODUKSI CATURWULANAN ---
    Route::prefix('caturwulanan')->name('caturwulanan.')->group(function () {
        Route::get('/search-petugas', [ProduksiCaturwulananController::class, 'searchPetugas'])->name('searchPetugas');
        Route::post('/bulk-delete', [ProduksiCaturwulananController::class, 'bulkDelete'])->name('bulkDelete');
        
        // Route untuk proses CRUD
        Route::post('/', [ProduksiCaturwulananController::class, 'store'])->name('store');
        Route::get('/{produksi_caturwulanan}/edit', [ProduksiCaturwulananController::class, 'edit'])->name('edit');
        Route::put('/{produksi_caturwulanan}', [ProduksiCaturwulananController::class, 'update'])->name('update');
        Route::delete('/{produksi_caturwulanan}', [ProduksiCaturwulananController::class, 'destroy'])->name('destroy');
        
        // Route utama untuk menampilkan data berdasarkan jenis kegiatan
        Route::get('/{jenisKegiatan}', [ProduksiCaturwulananController::class, 'index'])->name('index');
    });
});

/* --- TIM NWA --- */
Route::prefix('nwa')->name('nwa.')->group(function () {
    Route::get('/tahunan',           fn() => view('timNWA.tahunan.NWAtahunan'))->name('tahunan');
    Route::get('/triwulanan/sklnp',  fn() => view('timNWA.SKLNP.SKLNP'))->name('sklnp');
    Route::get('/triwulanan/snaper', fn() => view('timNWA.snaper.snaper'))->name('snaper');
    Route::get('/triwulanan/sktnp',  fn() => view('timNWA.SKTNP.SKTNP'))->name('sktnp');
});

/* --- REKAPITULASI --- */
Route::prefix('rekapitulasi')->name('rekapitulasi.')->group(function () {

    // Route untuk menampilkan halaman rekapitulasi utama
    Route::get('/pencacah', [PencacahController::class, 'index'])->name('pencacah.index');
    Route::get('/pencacah/detail/{nama}', [PencacahController::class, 'getDetailKegiatan'])->name('pencacah.detail');

    // [ PERBAIKAN ] Hapus prefix 'rekapitulasi' dari URL dan nama route
    Route::get('/pencacah/print-all', [PencacahController::class, 'printAll'])->name('pencacah.printAll');
    Route::post('/pencacah/print-selected', [PencacahController::class, 'printSelectedData'])->name('pencacah.printSelected');

    // Route untuk Pengawas
    Route::get('/pengawas', [PengawasController::class, 'index'])->name('pengawas.index');
    Route::get('/pengawas/detail/{nama}', [PengawasController::class, 'getDetailPencacah'])->name('pengawas.detail');

    // [ BARU ] Route untuk fungsi cetak pengawas
    Route::get('/pengawas/print-all', [PengawasController::class, 'printAll'])->name('pengawas.printAll');
    Route::post('/pengawas/print-selected', [PengawasController::class, 'printSelectedData'])->name('pengawas.printSelected');
});

/* --- MASTER --- */
Route::get('/master-petugas',  fn() => view('masterpetugas'))->name('master.petugas');
Route::get('/master-kegiatan', fn() => view('masterkegiatan'))->name('master.kegiatan');
Route::get('/user',            fn() => view('user'))->name('user');
