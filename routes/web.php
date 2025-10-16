<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SosialTahunanController;
use App\Http\Controllers\SosialSemesteranController;
use App\Http\Controllers\DistribusiTahunanController;
use App\Http\Controllers\DistribusiTriwulananController;
use App\Http\Controllers\DashboardDistribusiController;
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
    
    Route::get('/bulanan/vhts', fn() => view('timDistribusi.VHTS'))->name('vhts');
    Route::get('/bulanan/hkd', fn() => view('timDistribusi.HKD'))->name('hkd');
    Route::get('/bulanan/shpb', fn() => view('timDistribusi.SHPB'))->name('shpb');
    Route::get('/bulanan/shp', fn() => view('timDistribusi.SHP'))->name('shp');
    Route::get('/bulanan/shpj', fn() => view('timDistribusi.SHPJ'))->name('shpj');
    Route::get('/bulanan/shpgb', fn() => view('timDistribusi.SHPBG'))->name('shpgb');
    Route::get('/bulanan/hd', fn() => view('timDistribusi.HD'))->name('hd');
});

/* --- TIM PRODUKSI --- */
Route::prefix('produksi')->name('produksi.')->group(function () {
    Route::resource('tahunan', ProduksiTahunanController::class);

    Route::get('/kegiatan-caturwulan/ubinan-padi-palawija', fn() => view('timProduksi.ubinanpadi.ubinanpadipalawija'))->name('ubinanpadipalawija');
    Route::get('/kegiatan-caturwulan/update-utp-palawija', fn() => view('timProduksi.update.updateingutppalawija'))->name('updateingutppalawija');

    // Menyesuaikan path untuk setiap file yang sekarang ada di dalam foldernya sendiri
    Route::get('/kegiatan-triwulan/sktr', fn() => view('timProduksi.SKTR.SKTR'))->name('sktr');
    Route::get('/kegiatan-triwulan/tpi', fn() => view('timProduksi.TPI.TPI'))->name('tpi');
    Route::get('/kegiatan-triwulan/sphbst', fn() => view('timProduksi.SPHBST.SPHBST'))->name('sphbst');
    Route::get('/kegiatan-triwulan/sphtbf', fn() => view('timProduksi.SPHTBF.SPHTBF'))->name('sphtbf');
    Route::get('/kegiatan-triwulan/sphth',  fn() => view('timProduksi.SPHTH.SPHTH'))->name('sphth');
    Route::get('/kegiatan-triwulan/air-bersih', fn() => view('timProduksi.airBersih.airbersih'))->name('airbersih');
    Route::get('/kegiatan-bulanan/ksapadi',    fn() => view('timProduksi.KSAPadi.KSAPadi'))->name('ksapadi');
    Route::get('/kegiatan-bulanan/ksajagung',  fn() => view('timProduksi.KSAjagung.KSAjagung'))->name('ksajagung');
    Route::get('/kegiatan-bulanan/lptb',       fn() => view('timProduksi.LPTB.LPTB'))->name('lptb');
    Route::get('/kegiatan-bulanan/sphsbs',     fn() => view('timProduksi.SPHSBS.SPHSBS'))->name('sphsbs');
    Route::get('/kegiatan-bulanan/sppalawija', fn() => view('timProduksi.SPpalawija.SPpalawija'))->name('sppalawija');
    Route::get('/kegiatan-bulanan/perkebunan', fn() => view('timProduksi.perkebunan.perkebunanbulanan'))->name('perkebunanbulanan');
    Route::get('/kegiatan-bulanan/ibs',        fn() => view('timProduksi.IBSbulanan.IBSbulanan'))->name('ibsbulanan');
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
