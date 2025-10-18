<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardDistribusiController;

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

// NWAb
use App\Http\Controllers\DashboardNwaController;
use App\Http\Controllers\DashboardProduksiController;
use App\Http\Controllers\DashboardSosialController;
use App\Http\Controllers\PencacahController;
use App\Http\Controllers\PengawasController;
use App\Http\Controllers\NwaTahunanController;
use App\Http\Controllers\NwaTriwulananController;
use App\Http\Controllers\MasterPetugasController;

Route::get('/', fn() => view('home'))->name('home');

/* DASHBOARD */
Route::get('/dashboard-distribusi', [DashboardDistribusiController::class, 'index'])->name('dashboard.distribusi');
Route::get('/dashboard-nwa', [DashboardNwaController::class, 'index'])->name('dashboard.nwa');
Route::get('/dashboard-produksi', [DashboardProduksiController::class, 'index'])->name('dashboard.produksi');
Route::get('/dashboard-sosial', [DashboardSosialController::class, 'index'])->name('dashboard.sosial');

/* --- TIM SOSIAL --- */
Route::prefix('sosial')->name('sosial.')->group(function () {
    Route::resource('tahunan', SosialTahunanController::class);
    Route::resource('seruti', SosialTriwulanController::class);
    Route::resource('semesteran', SosialSemesteranController::class);

    Route::get('/semesteran/{kategori?}', [SosialSemesteranController::class, 'index'])->name('semesteran.index');
    Route::post('/semesteran', [SosialSemesteranController::class, 'store'])->name('semesteran.store');
    Route::put('/semesteran/{id}', [SosialSemesteranController::class, 'update'])->name('semesteran.update');
    Route::delete('/semesteran/{id}', [SosialSemesteranController::class, 'destroy'])->name('semesteran.destroy');
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

/* ---  TIM NWA --- */
Route::prefix('nwa')->name('nwa.')->middleware('web')->group(function () {
    // Route untuk NWA Tahunan (sudah benar)
    Route::resource('tahunan', NwaTahunanController::class);

    // Route untuk menampilkan halaman utama dan tabel data (method index)
    Route::get('/triwulanan/{jenis}', [NwaTriwulananController::class, 'index'])->name('triwulanan.index');

    // Route untuk menyimpan data baru dari modal 'Tambah' (method store)
    Route::post('/triwulanan/{jenis}', [NwaTriwulananController::class, 'store'])->name('triwulanan.store');

    // Route untuk memperbarui data dari modal 'Edit' (method update)
    // {nwa_triwulanan} adalah parameter untuk ID data yang akan di-update
    Route::put('/triwulanan/{jenis}/{nwa_triwulanan}', [NwaTriwulananController::class, 'update'])->name('triwulanan.update');

    // Route untuk menghapus data (method destroy)
    Route::delete('/triwulanan/{jenis}/{nwa_triwulanan}', [NwaTriwulananController::class, 'destroy'])->name('triwulanan.destroy');
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
Route::get('/master-petugas', [MasterPetugasController::class, 'index'])->name('master.petugas.index');
Route::resource('master-petugas', MasterPetugasController::class)
    ->parameters(['master-petugas' => 'petugas'])
    ->names('master.petugas');

Route::post('/master-petugas/bulk-delete', [MasterPetugasController::class, 'bulkDelete'])->name('master.petugas.bulkDelete');
Route::get('/master-petugas/export', [MasterPetugasController::class, 'export'])->name('master.petugas.export');



Route::get('/master-kegiatan', fn() => view('masterkegiatan'))->name('master.kegiatan');
Route::get('/user', fn() => view('user'))->name('user');
