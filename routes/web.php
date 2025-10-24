<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Dashboard\DashboardDistribusiController;
use App\Http\Controllers\Dashboard\DashboardNwaController;
use App\Http\Controllers\Dashboard\DashboardProduksiController;
use App\Http\Controllers\Dashboard\DashboardSosialController;
use App\Http\Controllers\Sosial\SosialTahunanController;
use App\Http\Controllers\Sosial\SosialSemesteranController;
use App\Http\Controllers\Sosial\SosialTriwulanController;
use App\Http\Controllers\Distribusi\DistribusiTahunanController;
use App\Http\Controllers\Distribusi\DistribusiTriwulananController;
use App\Http\Controllers\Distribusi\DistribusiBulananController;
use App\Http\Controllers\Produksi\ProduksiTahunanController;
use App\Http\Controllers\Produksi\ProduksiCaturwulananController;
use App\Http\Controllers\Produksi\ProduksiTriwulananController;
use App\Http\Controllers\Produksi\ProduksiBulananController;
use App\Http\Controllers\Nwa\NwaTahunanController;
use App\Http\Controllers\Nwa\NwaTriwulananController;
use App\Http\Controllers\Rekapitulasi\PencacahController;
use App\Http\Controllers\Rekapitulasi\PengawasController;
use App\Http\Controllers\Master\MasterPetugasController;
use App\Http\Controllers\Master\MasterKegiatanController;
use App\Http\Controllers\User\UserController; 
use App\Http\Controllers\ProfileController;



Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/home');
    }
    return view('welcome');
});

// Rute otentikasi (login, register, dll) dari Breeze
require __DIR__ . '/auth.php';

Route::middleware('auth')->group(function () {

    // Halaman "Home" internal aplikasi Anda (setelah login)
    Route::get('/home', fn () => view('home'))->name('home');

    // Rute /dashboard bawaan Breeze kita redirect ke 'home' internal Anda
    Route::get('/dashboard', function () {
        return redirect()->route('home');
    })->name('dashboard');

    // Rute Profile (dari Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // -----------------------------------------------------------------
    // SEMUA RUTE APLIKASI ANDA YANG LAMA MASUK DI SINI
    // -----------------------------------------------------------------

    /* DASHBOARD */
    Route::get('/dashboard-distribusi', [DashboardDistribusiController::class, 'index'])->name('dashboard.distribusi');
    Route::get('/dashboard-nwa', [DashboardNwaController::class, 'index'])->name('dashboard.nwa');
    Route::get('/dashboard-produksi', [DashboardProduksiController::class, 'index'])->name('dashboard.produksi');
    Route::get('/dashboard-sosial', [DashboardSosialController::class, 'index'])->name('dashboard.sosial');

    /* --- TIM SOSIAL --- */
    Route::prefix('sosial')->name('sosial.')->group(function () {
        Route::prefix('tahunan')->name('tahunan.')->group(function () {
            Route::get('/', [SosialTahunanController::class, 'index'])->name('index');
            Route::post('/', [SosialTahunanController::class, 'store'])->name('store');
            Route::get('/search-petugas', [SosialTahunanController::class, 'searchPetugas'])->name('searchPetugas');
            Route::post('/bulk-delete', [SosialTahunanController::class, 'bulkDelete'])->name('bulkDelete');
            Route::get('/export', [SosialTahunanController::class, 'export'])->name('export');
            Route::get('/{id}/edit', [SosialTahunanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SosialTahunanController::class, 'update'])->name('update');
            Route::delete('/{id}', [SosialTahunanController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('triwulanan')->name('triwulanan.')->group(function () {
            Route::post('/', [SosialTriwulanController::class, 'store'])->name('store');
            Route::post('/bulk-delete', [SosialTriwulanController::class, 'bulkDelete'])->name('bulkDelete');
            Route::get('/search-petugas', [SosialTriwulanController::class, 'searchPetugas'])->name('searchPetugas');
            Route::get('/search-kegiatan', [SosialTriwulanController::class, 'searchKegiatan'])->name('searchKegiatan');
            Route::get('/{jenisKegiatan}/export', [SosialTriwulanController::class, 'export'])->name('export')
                ->where('jenisKegiatan', 'seruti');
            Route::get('/{id}/edit', [SosialTriwulanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SosialTriwulanController::class, 'update'])->name('update');
            Route::delete('/{id}', [SosialTriwulanController::class, 'destroy'])->name('destroy');
            Route::get('/{jenisKegiatan?}', [SosialTriwulanController::class, 'index'])->name('index');
        });

        Route::prefix('semesteran')->name('semesteran.')->group(function () {
            Route::post('/', [SosialSemesteranController::class, 'store'])->name('store');
            Route::post('/bulk-delete', [SosialSemesteranController::class, 'bulkDelete'])->name('bulkDelete');
            Route::get('/search-petugas', [SosialSemesteranController::class, 'searchPetugas'])->name('searchPetugas');
            Route::get('/search-kegiatan', [SosialSemesteranController::class, 'searchKegiatan'])->name('searchKegiatan');
            Route::get('/{jenisKegiatan}/export', [SosialSemesteranController::class, 'export'])->name('export')
                ->where('jenisKegiatan', 'sakernas|susenas');
            Route::get('/{id}/edit', [SosialSemesteranController::class, 'edit'])->name('edit');
            Route::put('/{id}', [SosialSemesteranController::class, 'update'])->name('update');
            Route::delete('/{id}', [SosialSemesteranController::class, 'destroy'])->name('destroy');
            Route::get('/{jenisKegiatan}', [SosialSemesteranController::class, 'index'])
                ->where('jenisKegiatan', 'sakernas|susenas')
                ->name('index');
        });
    });

    /* --- TIM DISTRIBUSI --- */
    Route::prefix('tim-distribusi')->name('tim-distribusi.')->group(function () {
        Route::prefix('tahunan')->name('tahunan.')->group(function () {
            Route::get('/', [DistribusiTahunanController::class, 'index'])->name('index');
            Route::post('/', [DistribusiTahunanController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [DistribusiTahunanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [DistribusiTahunanController::class, 'update'])->name('update');
            Route::delete('/{id}', [DistribusiTahunanController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-delete', [DistribusiTahunanController::class, 'bulkDelete'])->name('bulkDelete');
            Route::get('/search-petugas', [DistribusiTahunanController::class, 'searchPetugas'])->name('searchPetugas');
            Route::get('/search-kegiatan', [App\Http\Controllers\Distribusi\DistribusiTahunanController::class, 'searchKegiatan'])->name('searchKegiatan');
            Route::get('/export', [DistribusiTahunanController::class, 'export'])->name('export');
        });
        Route::prefix('triwulanan')->name('triwulanan.')->group(function () {
            Route::get('/search-petugas', [DistribusiTriwulananController::class, 'searchPetugas'])
                ->name('searchPetugas');
            Route::get('/search-kegiatan', [SosialTriwulanController::class, 'searchKegiatan'])->name('searchKegiatan');
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
                ->where('jenisKegiatan', 'spunp|shkk');
        });
        Route::prefix('bulanan')->name('bulanan.')->group(function () {
            Route::get('/search-petugas', [DistribusiBulananController::class, 'searchPetugas'])
                ->name('searchPetugas');
            Route::post('/bulk-delete', [DistribusiBulananController::class, 'bulkDelete'])
                ->name('bulkDelete');
            Route::post('/store', [DistribusiBulananController::class, 'store'])
                ->name('store');
            Route::get('/{jenisKegiatan}/export', [DistribusiBulananController::class, 'export'])
                ->name('export')
                ->where('jenisKegiatan', 'vhts|hkd|shpb|shp|shpj|shpbg');
            Route::get('/{distribusi_bulanan}/edit', [DistribusiBulananController::class, 'edit'])
                ->name('edit')
                ->where('distribusi_bulanan', '[0-9]+');
            Route::put('/{distribusi_bulanan}', [DistribusiBulananController::class, 'update'])
                ->name('update')
                ->where('distribusi_bulanan', '[0-9]+');
            Route::delete('/{distribusi_bulanan}', [DistribusiBulananController::class, 'destroy'])
                ->name('destroy')
                ->where('distribusi_bulanan', '[0-9]+');
            Route::get('/{jenisKegiatan}', [DistribusiBulananController::class, 'index'])
                ->name('index')
                ->where('jenisKegiatan', 'vhts|hkd|shpb|shp|shpj|shpbg');
        });
    });

    /* --- TIM PRODUKSI --- */
    Route::prefix('tim-produksi')->name('tim-produksi.')->group(function () {
        Route::prefix('tahunan')->name('tahunan.')->group(function () {
            Route::get('/', [ProduksiTahunanController::class, 'index'])->name('index');
            Route::post('/', [ProduksiTahunanController::class, 'store'])->name('store');
            Route::get('/search-petugas', [ProduksiTahunanController::class, 'searchPetugas'])->name('searchPetugas');
            Route::post('/bulk-delete', [ProduksiTahunanController::class, 'bulkDelete'])->name('bulkDelete');
            Route::get('/export', [ProduksiTahunanController::class, 'export'])->name('export');
            Route::get('/{id}/edit', [ProduksiTahunanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [ProduksiTahunanController::class, 'update'])->name('update');
            Route::delete('/{id}', [ProduksiTahunanController::class, 'destroy'])->name('destroy');
        });
        Route::prefix('caturwulanan')->name('caturwulanan.')->group(function () {
            Route::post('/bulk-delete', [ProduksiCaturwulananController::class, 'bulkDelete'])->name('bulkDelete');
            Route::get('/{jenisKegiatan}/export', [ProduksiCaturwulananController::class, 'export'])->name('export')
                ->where('jenisKegiatan', 'ubinan padi palawija|updating utp palawija');
            Route::post('/', [ProduksiCaturwulananController::class, 'store'])->name('store');
            Route::get('/{produksi_caturwulanan}/edit', [ProduksiCaturwulananController::class, 'edit'])->name('edit');
            Route::put('/{produksi_caturwulanan}', [ProduksiCaturwulananController::class, 'update'])->name('update');
            Route::delete('/{produksi_caturwulanan}', [ProduksiCaturwulananController::class, 'destroy'])->name('destroy');
            Route::get('/{jenisKegiatan}', [ProduksiCaturwulananController::class, 'index'])->name('index');
        });
        Route::prefix('triwulanan')->name('triwulanan.')->group(function () {
            Route::post('/bulk-delete', [ProduksiTriwulananController::class, 'bulkDelete'])->name('bulkDelete');
            Route::get('/{jenisKegiatan}/export', [ProduksiTriwulananController::class, 'export'])->name('export')
                ->where('jenisKegiatan', 'sktr|tpi|sphbst|sphtbf|sphth|airbersih');
            Route::post('/', [ProduksiTriwulananController::class, 'store'])->name('store');
            Route::get('/{produksi_triwulanan}/edit', [ProduksiTriwulananController::class, 'edit'])->name('edit');
            Route::put('/{produksi_triwulanan}', [ProduksiTriwulananController::class, 'update'])->name('update');
            Route::delete('/{produksi_triwulanan}', [ProduksiTriwulananController::class, 'destroy'])->name('destroy');
            Route::get('/{jenisKegiatan}', [ProduksiTriwulananController::class, 'index'])->name('index');
        });
        Route::prefix('bulanan')->name('bulanan.')->group(function () {
            Route::get('/search-petugas', [ProduksiBulananController::class, 'searchPetugas'])->name('searchPetugas');
            Route::post('/bulk-delete', [ProduksiBulananController::class, 'bulkDelete'])->name('bulkDelete');
            Route::get('/{jenisKegiatan}/export', [ProduksiBulananController::class, 'export'])->name('export')
                ->where('jenisKegiatan', 'ksapadi|ksajagung|lptb|sphsbs|sppalawija|perkebunan|ibs');
            Route::post('/', [ProduksiBulananController::class, 'store'])->name('store');
            Route::get('/{produksi_bulanan}/edit', [ProduksiBulananController::class, 'edit'])->name('edit');
            Route::put('/{produksi_bulanan}', [ProduksiBulananController::class, 'update'])->name('update');
            Route::delete('/{produksi_bulanan}', [ProduksiBulananController::class, 'destroy'])->name('destroy');
            Route::get('/{jenisKegiatan}', [ProduksiBulananController::class, 'index'])->name('index');
        });
    });

    /* --- TIM NWA --- */
    Route::prefix('nwa')->name('nwa.')->middleware('web')->group(function () {
        Route::prefix('tahunan')->name('tahunan.')->group(function () {
            Route::get('/', [NwaTahunanController::class, 'index'])->name('index');
            Route::post('/', [NwaTahunanController::class, 'store'])->name('store');
            Route::post('/bulk-delete', [NwaTahunanController::class, 'bulkDelete'])->name('bulkDelete');
            Route::get('/export', [NwaTahunanController::class, 'export'])->name('export');
            Route::get('/{id}/edit', [NwaTahunanController::class, 'edit'])->name('edit');
            Route::put('/{id}', [NwaTahunanController::class, 'update'])->name('update');
            Route::delete('/{id}', [NwaTahunanController::class, 'destroy'])->name('destroy');
        });
        Route::prefix('triwulanan')->name('triwulanan.')->group(function () {
            Route::post('/', [NwaTriwulananController::class, 'store'])->name('store');
            Route::post('/bulk-delete', [NwaTriwulananController::class, 'bulkDelete'])->name('bulkDelete');
            Route::get('/{jenisKegiatan}/export', [NwaTriwulananController::class, 'export'])->name('export')
                ->where('jenisKegiatan', 'sklnp|snaper|sktnp');
            Route::get('/{id}/edit', [NwaTriwulananController::class, 'edit'])->name('edit');
            Route::put('/{id}', [NwaTriwulananController::class, 'update'])->name('update');
            Route::delete('/{id}', [NwaTriwulananController::class, 'destroy'])->name('destroy');
            Route::get('/{jenisKegiatan}', [NwaTriwulananController::class, 'index'])->name('index');
        });
    });

    /* --- REKAPITULASI --- */
    Route::prefix('rekapitulasi')->name('rekapitulasi.')->group(function () {
        Route::get('/pencacah', [PencacahController::class, 'index'])->name('pencacah.index');
        Route::get('/pencacah/detail/{nama}', [PencacahController::class, 'getDetailKegiatan'])->name('pencacah.detail');
        Route::get('/pencacah/print-all', [PencacahController::class, 'printAll'])->name('pencacah.printAll');
        Route::post('/pencacah/print-selected', [PencacahController::class, 'printSelectedData'])->name('pencacah.printSelected');
        Route::get('/pengawas', [PengawasController::class, 'index'])->name('pengawas.index');
        Route::get('/pengawas/detail/{nama}', [PengawasController::class, 'getDetailPencacah'])->name('pengawas.detail');
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


    /* --- MANAJEMEN USER (ADMIN ONLY) --- */
    // Grup ini dilindungi oleh Gate 'access-admin-areas' yang kita buat
    Route::middleware('can:access-admin-areas')
        ->prefix('users')
        ->name('users.')
        ->controller(UserController::class)
        ->group(function () {

            Route::get('/', 'index')->name('index');
        });

});