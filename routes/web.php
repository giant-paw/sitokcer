<?php

use Illuminate\Support\Facades\Route;

// DASHBOARD
use App\Http\Controllers\Dashboard\DashboardDistribusiController;
use App\Http\Controllers\Dashboard\DashboardNwaController;
use App\Http\Controllers\Dashboard\DashboardProduksiController;
use App\Http\Controllers\Dashboard\DashboardSosialController;

// SOSIAL
use App\Http\Controllers\Sosial\SosialTahunanController;
use App\Http\Controllers\Sosial\SosialSemesteranController;
use App\Http\Controllers\Sosial\SosialTriwulanController;

// DISTRIBUSI
use App\Http\Controllers\Distribusi\DistribusiTahunanController;
use App\Http\Controllers\Distribusi\DistribusiTriwulananController;
use App\Http\Controllers\Distribusi\DistribusiBulananController;

// Produksi
use App\Http\Controllers\Produksi\ProduksiTahunanController;
use App\Http\Controllers\Produksi\ProduksiCaturwulananController;
use App\Http\Controllers\Produksi\ProduksiTriwulananController;
use App\Http\Controllers\Produksi\ProduksiBulananController;

// NWA
use App\Http\Controllers\Nwa\NwaTahunanController;
use App\Http\Controllers\Nwa\NwaTriwulananController;

// REKAPITULASI
use App\Http\Controllers\Rekapitulasi\PencacahController;
use App\Http\Controllers\Rekapitulasi\PengawasController;

// MASTER PETUGAS
use App\Http\Controllers\Master\MasterPetugasController;

// MASTER KEGIATAN
use App\Http\Controllers\Master\MasterKegiatanController;

// HOME
Route::get('/', fn() => view('home'))->name('home');

/* DASHBOARD */
Route::get('/dashboard-distribusi', [DashboardDistribusiController::class, 'index'])->name('dashboard.distribusi');
Route::get('/dashboard-nwa', [DashboardNwaController::class, 'index'])->name('dashboard.nwa');
Route::get('/dashboard-produksi', [DashboardProduksiController::class, 'index'])->name('dashboard.produksi');
Route::get('/dashboard-sosial', [DashboardSosialController::class, 'index'])->name('dashboard.sosial');

/* --- TIM SOSIAL --- */
Route::prefix('sosial')->name('sosial.')->group(function () {
    // --- Rute untuk Sosial Tahunan ---
    Route::resource('tahunan', SosialTahunanController::class);
    Route::post('tahunan/bulk-delete', [SosialTahunanController::class, 'bulkDelete'])->name('tahunan.bulkDelete');

    // --- Rute untuk Seruti (Triwulanan) ---
    Route::resource('seruti', SosialTriwulanController::class);
    // Pastikan nama controller di rute bulk-delete sesuai. Diasumsikan 'SosialTriwulanController'
    Route::post('seruti/bulk-delete', [SosialTriwulanController::class, 'bulkDelete'])->name('seruti.bulkDelete');

    // --- [PERBAIKAN] Rute untuk Kegiatan Semesteran (Sakernas & Susenas) ---
    Route::prefix('semesteran')->name('semesteran.')->group(function () {
        Route::get('/{kategori}', [SosialSemesteranController::class, 'index'])->name('index');
        Route::post('/{kategori}', [SosialSemesteranController::class, 'store'])->name('store');
        Route::put('/{kategori}/{semesteran}', [SosialSemesteranController::class, 'update'])->name('update');
        Route::delete('/{kategori}/{semesteran}', [SosialSemesteranController::class, 'destroy'])->name('destroy');
        Route::post('/{kategori}/bulk-delete', [SosialSemesteranController::class, 'bulkDelete'])->name('bulkDelete');
    });
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

    // --- ROUTE PRODUKSI TRIWULANAN ---
    Route::prefix('triwulanan')->name('triwulanan.')->group(function () {
        Route::get('/search-petugas', [ProduksiTriwulananController::class, 'searchPetugas'])->name('searchPetugas');
        Route::post('/bulk-delete', [ProduksiTriwulananController::class, 'bulkDelete'])->name('bulkDelete');

        // Route untuk proses CRUD
        Route::post('/', [ProduksiTriwulananController::class, 'store'])->name('store');
        Route::get('/{produksi_triwulanan}/edit', [ProduksiTriwulananController::class, 'edit'])->name('edit');
        Route::put('/{produksi_triwulanan}', [ProduksiTriwulananController::class, 'update'])->name('update');
        Route::delete('/{produksi_triwulanan}', [ProduksiTriwulananController::class, 'destroy'])->name('destroy');

        Route::get('/{jenisKegiatan}', [ProduksiTriwulananController::class, 'index'])->name('index');
    });

    // --- ROUTE Produksi Bulanan ---
    Route::prefix('bulanan')->name('bulanan.')->group(function () {
        Route::get('/search-petugas', [ProduksiBulananController::class, 'searchPetugas'])->name('searchPetugas');
        Route::post('/bulk-delete', [ProduksiBulananController::class, 'bulkDelete'])->name('bulkDelete');

        // Route untuk proses CRUD
        Route::post('/', [ProduksiBulananController::class, 'store'])->name('store');

        Route::get('/{distribusi_bulanan}/edit', [ProduksiBulananController::class, 'edit'])->name('edit');
        Route::put('/{distribusi_bulanan}', [ProduksiBulananController::class, 'update'])->name('update');
        Route::delete('/{distribusi_bulanan}', [ProduksiBulananController::class, 'destroy'])->name('destroy');

        // Route utama untuk menampilkan data berdasarkan jenis kegiatan
        Route::get('/{jenisKegiatan}', [ProduksiBulananController::class, 'index'])->name('index');
    });
});

Route::prefix('nwa')->name('nwa.')->middleware('web')->group(function () {
    // --- NWA Tahunan ---
    Route::resource('tahunan', NwaTahunanController::class);
    // [TAMBAHKAN INI] Rute untuk bulk delete Tahunan
    Route::post('tahunan/bulk-delete', [NwaTahunanController::class, 'bulkDelete'])->name('tahunan.bulkDelete');


    // --- NWA Triwulanan ---
    // Route untuk menampilkan halaman utama dan tabel data (method index)
    Route::get('/triwulanan/{jenis}', [NwaTriwulananController::class, 'index'])->name('triwulanan.index');

    // Route untuk menyimpan data baru dari modal 'Tambah' (method store)
    Route::post('/triwulanan/{jenis}', [NwaTriwulananController::class, 'store'])->name('triwulanan.store');

    // Route untuk memperbarui data dari modal 'Edit' (method update)
    Route::put('/triwulanan/{jenis}/{triwulanan}', [NwaTriwulananController::class, 'update'])->name('triwulanan.update');

    // Route untuk menghapus data (method destroy)
    Route::delete('/triwulanan/{jenis}/{triwulanan}', [NwaTriwulananController::class, 'destroy'])->name('triwulanan.destroy');

    // [TAMBAHKAN INI] Rute untuk bulk delete Triwulanan
    Route::post('/triwulanan/{jenis}/bulk-delete', [NwaTriwulananController::class, 'bulkDelete'])->name('triwulanan.bulkDelete');
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


/* --- MASTER PETUGAS --- */
Route::prefix('master-petugas')->name('master.petugas.')->group(function () {
    
   
    Route::resource('/', MasterPetugasController::class)
         ->parameters(['' => 'petugas']); 


    Route::post('/bulk-delete', [MasterPetugasController::class, 'bulkDelete'])->name('bulkDelete');
    Route::get('/export-csv', [MasterPetugasController::class, 'export'])->name('export');
    Route::get('/search-petugas', [MasterPetugasController::class, 'search'])->name('search');

});

/* --- MASTER KEGIATAN --- */
Route::prefix('master-kegiatan')
    ->name('master.kegiatan.')
    ->controller(MasterKegiatanController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{kegiatan}/edit', 'edit')->name('edit');
        Route::put('/{kegiatan}', 'update')->name('update');
        Route::delete('/{kegiatan}', 'destroy')->name('destroy');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulkDelete');
    });

Route::get('/master/kegiatan/search', [MasterKegiatanController::class, 'search'])->name('master.kegiatan.search');

Route::get('/user', fn() => view('user'))->name('user');
