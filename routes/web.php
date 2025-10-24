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

// USER
use App\Http\Controllers\User\UserController;

// HOME
Route::get('/', fn() => view('home'))->name('home');

/* DASHBOARD */
Route::get('/dashboard-distribusi', [DashboardDistribusiController::class, 'index'])->name('dashboard.distribusi');
Route::get('/dashboard-nwa', [DashboardNwaController::class, 'index'])->name('dashboard.nwa');
Route::get('/dashboard-produksi', [DashboardProduksiController::class, 'index'])->name('dashboard.produksi');
Route::get('/dashboard-sosial', [DashboardSosialController::class, 'index'])->name('dashboard.sosial');

/* --- TIM SOSIAL --- */
Route::prefix('sosial')->name('sosial.')->group(function () {
    // --- ROUTE SOSIAL TAHUNAN ---
    Route::prefix('tahunan')->name('tahunan.')->group(function () {

        // Rute-rute yang tidak punya parameter / spesifik
        Route::get('/', [SosialTahunanController::class, 'index'])->name('index');
        Route::post('/', [SosialTahunanController::class, 'store'])->name('store');
        Route::get('/search-petugas', [SosialTahunanController::class, 'searchPetugas'])->name('searchPetugas');
        Route::post('/bulk-delete', [SosialTahunanController::class, 'bulkDelete'])->name('bulkDelete');
        // Tambahkan searchKegiatan jika ada di controller
        // Route::get('/search-kegiatan', [SosialTahunanController::class, 'searchKegiatan'])->name('searchKegiatan');


        // Rute-rute yang menggunakan {id}
        // Ini akan cocok dengan controller baru ($id)
        Route::get('/{id}/edit', [SosialTahunanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SosialTahunanController::class, 'update'])->name('update');
        Route::delete('/{id}', [SosialTahunanController::class, 'destroy'])->name('destroy');

        // HAPUS RUTE RESOURCE YANG LAMA
        // Route::resource('/', SosialTahunanController::class)->parameters(['' => 'tahunan']); // <-- HAPUS/KOMENTARI
    });

    Route::prefix('triwulanan')->name('triwulanan.')->group(function () {

        // Rute-rute yang tidak punya parameter / spesifik
        Route::post('/', [SosialTriwulanController::class, 'store'])->name('store');
        Route::post('/bulk-delete', [SosialTriwulanController::class, 'bulkDelete'])->name('bulkDelete');
        Route::get('/search-petugas', [SosialTriwulanController::class, 'searchPetugas'])->name('searchPetugas');
        Route::get('/search-kegiatan', [SosialTriwulanController::class, 'searchKegiatan'])->name('searchKegiatan');

        // Rute-rute yang menggunakan {id}
        Route::get('/{id}/edit', [SosialTriwulanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SosialTriwulanController::class, 'update'])->name('update');
        Route::delete('/{id}', [SosialTriwulanController::class, 'destroy'])->name('destroy');

        // Rute index HARUS diletakkan PALING AKHIR
        // Menerima parameter {jenisKegiatan}, default 'seruti' akan ditangani controller
        Route::get('/{jenisKegiatan?}', [SosialTriwulanController::class, 'index'])->name('index');

        // HAPUS RUTE RESOURCE YANG LAMA UNTUK SERUTI
        // Route::resource('seruti', SosialTriwulanController::class); // <-- HAPUS/KOMENTARI
    });

    // --- ROUTE SOSIAL SEMESTERAN (SAKERNAS/SUSENAS) (SUDAH BENAR) ---
    Route::prefix('semesteran')->name('semesteran.')->group(function () {

        // Rute-rute yang tidak punya parameter / spesifik
        Route::post('/', [SosialSemesteranController::class, 'store'])->name('store');
        Route::post('/bulk-delete', [SosialSemesteranController::class, 'bulkDelete'])->name('bulkDelete');
        Route::get('/search-petugas', [SosialSemesteranController::class, 'searchPetugas'])->name('searchPetugas');
        Route::get('/search-kegiatan', [SosialSemesteranController::class, 'searchKegiatan'])->name('searchKegiatan');

        // Rute-rute yang menggunakan {id}
        Route::get('/{id}/edit', [SosialSemesteranController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SosialSemesteranController::class, 'update'])->name('update');
        Route::delete('/{id}', [SosialSemesteranController::class, 'destroy'])->name('destroy');

        // Rute index HARUS diletakkan PALING AKHIR
        Route::get('/{jenisKegiatan}', [SosialSemesteranController::class, 'index'])
            ->where('jenisKegiatan', 'sakernas|susenas')
            ->name('index');
    });
});/* --- TIM SOSIAL --- */
Route::prefix('sosial')->name('sosial.')->group(function () {
    // --- ROUTE SOSIAL TAHUNAN ---
    Route::prefix('tahunan')->name('tahunan.')->group(function () {

        // Rute-rute yang tidak punya parameter / spesifik
        Route::get('/', [SosialTahunanController::class, 'index'])->name('index');
        Route::post('/', [SosialTahunanController::class, 'store'])->name('store');
        Route::get('/search-petugas', [SosialTahunanController::class, 'searchPetugas'])->name('searchPetugas');
        Route::post('/bulk-delete', [SosialTahunanController::class, 'bulkDelete'])->name('bulkDelete');
        // Tambahkan searchKegiatan jika ada di controller
        // Route::get('/search-kegiatan', [SosialTahunanController::class, 'searchKegiatan'])->name('searchKegiatan');


        // Rute-rute yang menggunakan {id}
        // Ini akan cocok dengan controller baru ($id)
        Route::get('/{id}/edit', [SosialTahunanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SosialTahunanController::class, 'update'])->name('update');
        Route::delete('/{id}', [SosialTahunanController::class, 'destroy'])->name('destroy');

        // HAPUS RUTE RESOURCE YANG LAMA
        // Route::resource('/', SosialTahunanController::class)->parameters(['' => 'tahunan']); // <-- HAPUS/KOMENTARI
    });

    Route::prefix('triwulanan')->name('triwulanan.')->group(function () {

        // Rute-rute yang tidak punya parameter / spesifik
        Route::post('/', [SosialTriwulanController::class, 'store'])->name('store');
        Route::post('/bulk-delete', [SosialTriwulanController::class, 'bulkDelete'])->name('bulkDelete');
        Route::get('/search-petugas', [SosialTriwulanController::class, 'searchPetugas'])->name('searchPetugas');
        Route::get('/search-kegiatan', [SosialTriwulanController::class, 'searchKegiatan'])->name('searchKegiatan');

        // Rute-rute yang menggunakan {id}
        Route::get('/{id}/edit', [SosialTriwulanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SosialTriwulanController::class, 'update'])->name('update');
        Route::delete('/{id}', [SosialTriwulanController::class, 'destroy'])->name('destroy');

        // Rute index HARUS diletakkan PALING AKHIR
        // Menerima parameter {jenisKegiatan}, default 'seruti' akan ditangani controller
        Route::get('/{jenisKegiatan?}', [SosialTriwulanController::class, 'index'])->name('index');

        // HAPUS RUTE RESOURCE YANG LAMA UNTUK SERUTI
        // Route::resource('seruti', SosialTriwulanController::class); // <-- HAPUS/KOMENTARI
    });

    // --- ROUTE SOSIAL SEMESTERAN (SAKERNAS/SUSENAS) (SUDAH BENAR) ---
    Route::prefix('semesteran')->name('semesteran.')->group(function () {

        // Rute-rute yang tidak punya parameter / spesifik
        Route::post('/', [SosialSemesteranController::class, 'store'])->name('store');
        Route::post('/bulk-delete', [SosialSemesteranController::class, 'bulkDelete'])->name('bulkDelete');
        Route::get('/search-petugas', [SosialSemesteranController::class, 'searchPetugas'])->name('searchPetugas');
        Route::get('/search-kegiatan', [SosialSemesteranController::class, 'searchKegiatan'])->name('searchKegiatan');

        // Rute-rute yang menggunakan {id}
        Route::get('/{id}/edit', [SosialSemesteranController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SosialSemesteranController::class, 'update'])->name('update');
        Route::delete('/{id}', [SosialSemesteranController::class, 'destroy'])->name('destroy');

        // Rute index HARUS diletakkan PALING AKHIR
        Route::get('/{jenisKegiatan}', [SosialSemesteranController::class, 'index'])
            ->where('jenisKegiatan', 'sakernas|susenas')
            ->name('index');
    });
});
/* --- TIM DISTRIBUSI --- */
Route::prefix('tim-distribusi')->name('tim-distribusi.')->group(function () {

    // ============ TAHUNAN ============
    Route::prefix('tahunan')->name('tahunan.')->group(function () {
        Route::get('/', [DistribusiTahunanController::class, 'index'])->name('index');
        Route::post('/', [DistribusiTahunanController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [DistribusiTahunanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DistribusiTahunanController::class, 'update'])->name('update');
        Route::delete('/{id}', [DistribusiTahunanController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [DistribusiTahunanController::class, 'bulkDelete'])->name('bulkDelete');
        Route::get('/search-petugas', [DistribusiTahunanController::class, 'searchPetugas'])->name('searchPetugas');
        Route::get('/export', [DistribusiTahunanController::class, 'export'])->name('export');
    }); // ← TUTUP group tahunan di sini!

    // ============ TRIWULANAN============
    Route::prefix('triwulanan')->name('triwulanan.')->group(function () {
        Route::get('/search-petugas', [DistribusiTriwulananController::class, 'searchPetugas'])
            ->name('searchPetugas');

        Route::post('/bulk-delete', [DistribusiTriwulananController::class, 'bulkDelete'])
            ->name('bulkDelete');

        Route::get('/{jenisKegiatan}/export', [DistribusiTriwulananController::class, 'export'])
            ->name('export')
            ->where('jenisKegiatan', 'spunp|shkk');

        Route::get('/{distribusi_triwulanan}/edit', [DistribusiTriwulananController::class, 'edit'])
            ->name('edit');

        Route::put('/{distribusi_triwulanan}', [DistribusiTriwulananController::class, 'update'])
            ->name('update');

        Route::delete('/{distribusi_triwulanan}', [DistribusiTriwulananController::class, 'destroy'])
            ->name('destroy');

        Route::post('/', [DistribusiTriwulananController::class, 'store'])
            ->name('store');

        Route::get('/{jenisKegiatan}', [DistribusiTriwulananController::class, 'index'])
            ->name('index')
            ->where('jenisKegiatan', 'spunp|shkk'); //zamm
    });

    // ============ BULANAN ============
    Route::prefix('bulanan')->name('bulanan.')->group(function () {

        // Route statis tanpa parameter (PALING ATAS)
        Route::get('/search-petugas', [DistribusiBulananController::class, 'searchPetugas'])
            ->name('searchPetugas');

        Route::post('/bulk-delete', [DistribusiBulananController::class, 'bulkDelete'])
            ->name('bulkDelete');

        Route::post('/store', [DistribusiBulananController::class, 'store'])
            ->name('store');

        // Route dengan jenis kegiatan (LEBIH SPESIFIK DULU)
        Route::get('/{jenisKegiatan}/export', [DistribusiBulananController::class, 'export'])
            ->name('export')
            ->where('jenisKegiatan', 'vhts|hkd|shpb|shp|shpj|shpbg');

        // Route CRUD dengan ID angka (HARUS PAKAI CONSTRAINT)
        Route::get('/{distribusi_bulanan}/edit', [DistribusiBulananController::class, 'edit'])
            ->name('edit')
            ->where('distribusi_bulanan', '[0-9]+');

        Route::put('/{distribusi_bulanan}', [DistribusiBulananController::class, 'update'])
            ->name('update')
            ->where('distribusi_bulanan', '[0-9]+');

        Route::delete('/{distribusi_bulanan}', [DistribusiBulananController::class, 'destroy'])
            ->name('destroy')
            ->where('distribusi_bulanan', '[0-9]+');

        // Route index (PALING BAWAH - catch all)
        Route::get('/{jenisKegiatan}', [DistribusiBulananController::class, 'index'])
            ->name('index')
            ->where('jenisKegiatan', 'vhts|hkd|shpb|shp|shpj|shpbg');
    });
});

/* --- TIM PRODUKSI --- */
Route::prefix('tim-produksi')->name('tim-produksi.')->group(function () {

    // --- ROUTE PRODUKSI TAHUNAN ---
    Route::prefix('tahunan')->name('tahunan.')->group(function () {

        Route::get('/', [ProduksiTahunanController::class, 'index'])->name('index');
        Route::post('/', [ProduksiTahunanController::class, 'store'])->name('store');
        Route::get('/search-petugas', [ProduksiTahunanController::class, 'searchPetugas'])->name('searchPetugas');
        Route::post('/bulk-delete', [ProduksiTahunanController::class, 'bulkDelete'])->name('bulkDelete');

        Route::get('/export', [ProduksiTahunanController::class, 'export'])->name('export');

        Route::get('/{id}/edit', [ProduksiTahunanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProduksiTahunanController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProduksiTahunanController::class, 'destroy'])->name('destroy');

        // HAPUS RUTE RESOURCE YANG LAMA
        // Route::resource('/', ProduksiTahunanController::class)->parameters(['' => 'tahunan']); // <-- HAPUS/KOMENTARI
    });

    // --- ROUTE PRODUKSI CATURWULANAN ---
    Route::prefix('caturwulanan')->name('caturwulanan.')->group(function () {
        Route::post('/bulk-delete', [ProduksiCaturwulananController::class, 'bulkDelete'])->name('bulkDelete');
        Route::get('/{jenisKegiatan}/export', [ProduksiCaturwulananController::class, 'export'])->name('export')
            ->where('jenisKegiatan', 'ubinan padi palawija|updating utp palawija');


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
        Route::post('/bulk-delete', [ProduksiTriwulananController::class, 'bulkDelete'])->name('bulkDelete');

        Route::get('/{jenisKegiatan}/export', [ProduksiTriwulananController::class, 'export'])->name('export')
            ->where('jenisKegiatan', 'sktr|tpi|sphbst|sphtbf|sphth|airbersih');

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
        Route::get('/{jenisKegiatan}/export', [ProduksiBulananController::class, 'export'])->name('export')
            ->where('jenisKegiatan', 'ksapadi|ksajagung|lptb|sphsbs|sppalawija|perkebunan|ibs');
        // Export route
        Route::get('/{jenisKegiatan}/export', [ProduksiBulananController::class, 'export'])->name('export')
            ->where('jenisKegiatan', 'ksapadi|ksajagung|lptb|sphsbs|sppalawija|perkebunan|ibs');

        // Route untuk proses CRUD
        Route::post('/', [ProduksiBulananController::class, 'store'])->name('store');

        Route::get('/{produksi_bulanan}/edit', [ProduksiBulananController::class, 'edit'])->name('edit');
        Route::put('/{produksi_bulanan}', [ProduksiBulananController::class, 'update'])->name('update');
        Route::delete('/{produksi_bulanan}', [ProduksiBulananController::class, 'destroy'])->name('destroy');

        // Route utama untuk menampilkan data berdasarkan jenis kegiatan
        Route::get('/{jenisKegiatan}', [ProduksiBulananController::class, 'index'])->name('index');
    });
});

Route::prefix('nwa')->name('nwa.')->middleware('web')->group(function () {

    // --- ROUTE NWA TAHUNAN (DIROMBAK) ---
    Route::prefix('tahunan')->name('tahunan.')->group(function () {

        // Rute-rute yang tidak punya parameter / spesifik
        Route::get('/', [NwaTahunanController::class, 'index'])->name('index');
        Route::post('/', [NwaTahunanController::class, 'store'])->name('store');
        Route::post('/bulk-delete', [NwaTahunanController::class, 'bulkDelete'])->name('bulkDelete');

        // Rute search (jika ada, tambahkan di sini, misal: search-petugas)
        // Route::get('/search-petugas', [NwaTahunanController::class, 'searchPetugas'])->name('searchPetugas');

        // Rute-rute yang menggunakan {id}
        // Ini akan cocok dengan controller baru ($id)
        Route::get('/{id}/edit', [NwaTahunanController::class, 'edit'])->name('edit');
        Route::put('/{id}', [NwaTahunanController::class, 'update'])->name('update');
        Route::delete('/{id}', [NwaTahunanController::class, 'destroy'])->name('destroy');

        // JANGAN GUNAKAN Route::resource DI SINI
        // Route::resource('/', NwaTahunanController::class)->parameters(['' => 'tahunan']); // <-- HAPUS INI
    });


    // --- ROUTE NWA TRIWULANAN (DIROMBAK) ---
    Route::prefix('triwulanan')->name('triwulanan.')->group(function () {

        // Rute-rute yang tidak punya parameter / spesifik
        Route::post('/', [NwaTriwulananController::class, 'store'])->name('store');
        Route::post('/bulk-delete', [NwaTriwulananController::class, 'bulkDelete'])->name('bulkDelete');

        // Rute-rute yang menggunakan {id}
        // Menggunakan {id} polos agar cocok dengan controller yang dirombak
        Route::get('/{id}/edit', [NwaTriwulananController::class, 'edit'])->name('edit');
        Route::put('/{id}', [NwaTriwulananController::class, 'update'])->name('update');
        Route::delete('/{id}', [NwaTriwulananController::class, 'destroy'])->name('destroy');

        // Rute index HARUS diletakkan PALING AKHIR
        // agar tidak "menangkap" request untuk 'bulk-delete' atau '{id}/edit'
        Route::get('/{jenisKegiatan}', [NwaTriwulananController::class, 'index'])->name('index');
    });
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
Route::prefix('master-petugas')
    ->name('master.petugas.')
    ->controller(MasterPetugasController::class)
    ->group(function () {

        Route::post('/bulk-delete', 'bulkDelete')->name('bulkDelete');
        Route::get('/export', 'export')->name('export');
        Route::get('/search', 'search')->name('search');

        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');

        Route::get('/{petugas}/edit', 'edit')->name('edit');
        Route::put('/{petugas}', 'update')->name('update');
        Route::delete('/{petugas}', 'destroy')->name('destroy');
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
