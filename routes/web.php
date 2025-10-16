<?php

use App\Http\Controllers\SerutiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SosialTahunanController; 
use App\Http\Controllers\SosialTriwulananController;
use App\Http\Controllers\SosialSemesteranController;
use App\Http\Controllers\DistribusiTahunanController;

Route::get('/', fn() => view('home'))->name('home');

/* DASHBOARD */
Route::get('/dashboard-distribusi', fn() => view('Dashboard.dashboardDistribusi')) 
    ->name('dashboard.distribusi');
Route::get('/dashboard-nwa', fn() => view('Dashboard.dashboardNWA'))->name('dashboard.nwa');
Route::get('/dashboard-produksi', fn() => view('Dashboard.dashboardProduksi'))->name('dashboard.produksi');
Route::get('/dashboard-sosial', fn() => view('Dashboard.dashboardSosial'))->name('dashboard.sosial');

/* --- TIM SOSIAL --- */
Route::prefix('sosial')->name('sosial.')->group(function () {
    Route::resource('tahunan', SosialTahunanController::class);
    Route::resource('seruti', SerutiController::class);
    Route::resource('semesteran', SosialSemesteranController::class);
    // Rute lain tetap
    Route::get('/kegiatan-semesteran/susenas', fn() => view('timSosial.susenas'))->name('susenas');
});

/* --- TIM DISTRIBUSI --- */
Route::prefix('tim-distribusi')->name('tim-distribusi.')->group(function () {
    Route::get('tahunan/search-petugas', [DistribusiTahunanController::class, 'searchPetugas'])->name('tahunan.searchPetugas');
    Route::post('tahunan/bulk-delete', [DistribusiTahunanController::class, 'bulkDelete'])->name('tahunan.bulkDelete');
    Route::resource('tahunan', DistribusiTahunanController::class);
    


    Route::get('/kegiatan-triwulan/spunp', fn() => view('timDistribusi.SPUNP'))->name('spunp');
    Route::get('/kegiatan-triwulan/shkk', fn() => view('timDistribusi.SHKK'))->name('shkk');
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
    // File ini tidak berada di dalam subfolder, jadi path-nya tetap
    Route::get('/tahunan', fn() => view('timProduksi.produksitahunan'))->name('tahunan');

    // Menggunakan path 'ubinanpadi.namafile'
    Route::get('/kegiatan-caturwulan/ubinan-padi-palawija', fn() => view('timProduksi.ubinanpadi.ubinanpadipalawija'))->name('ubinanpadipalawija');
    Route::get('/kegiatan-caturwulan/update-utp-palawija', fn() => view('timProduksi.update.updateingutppalawija'))->name('updateingutppalawija');
    
    // Menyesuaikan path untuk setiap file yang sekarang ada di dalam foldernya sendiri
    Route::get('/kegiatan-triwulan/sktr', fn() => view('timProduksi.SKTR.SKTR'))->name('sktr');
    Route::get('/kegiatan-triwulan/tpi', fn() => view('timProduksi.TPI.TPI'))->name('tpi');
    Route::get('/kegiatan-triwulan/sphbst', fn() => view('timProduksi.SPHBST.SPHBST'))->name('sphbst');
    Route::get('/kegiatan-triwulan/sphtbf', fn() => view('timProduksi.SPHTBF.SPHTBF'))->name('sphtbf');
    Route::get('/kegiatan-triwulan/sphth', fn() => view('timProduksi.SPHTH.SPHTH'))->name('sphth');
    Route::get('/kegiatan-triwulan/air-bersih', fn() => view('timProduksi.airBersih.airbersih'))->name('airbersih');
    Route::get('/kegiatan-bulanan/ksapadi', fn() => view('timProduksi.KSAPadi.KSAPadi'))->name('ksapadi');
    Route::get('/kegiatan-bulanan/ksajagung', fn() => view('timProduksi.KSAjagung.KSAjagung'))->name('ksajagung');
    Route::get('/kegiatan-bulanan/lptb', fn() => view('timProduksi.LPTB.LPTB'))->name('lptb');
    Route::get('/kegiatan-bulanan/sphsbs', fn() => view('timProduksi.SPHSBS.SPHSBS'))->name('sphsbs');
    Route::get('/kegiatan-bulanan/sppalawija', fn() => view('timProduksi.SPpalawija.SPpalawija'))->name('sppalawija');
    Route::get('/kegiatan-bulanan/perkebunan', fn() => view('timProduksi.perkebunan.perkebunanbulanan'))->name('perkebunanbulanan');
    Route::get('/kegiatan-bulanan/ibs', fn() => view('timProduksi.IBSbulanan.IBSbulanan'))->name('ibsbulanan');
});

/* --- TIM NWA --- */
Route::prefix('nwa')->name('nwa.')->group(function () {
    Route::get('/tahunan', fn() => view('timNWA.tahunan.NWAtahunan'))->name('tahunan');
    Route::get('/triwulanan/sklnp', fn() => view('timNWA.SKLNP.SKLNP'))->name('sklnp');
    Route::get('/triwulanan/snaper', fn() => view('timNWA.snaper.snaper'))->name('snaper');
    Route::get('/triwulanan/sktnp', fn() => view('timNWA.SKTNP.SKTNP'))->name('sktnp');
});

/* --- REKAPITULASI --- */
Route::prefix('rekapitulasi')->name('rekapitulasi.')->group(function () {
    Route::get('/pencacah', fn() => view('Rekapitulasi.rekapcacah.rekappencacah'))->name('pencacah');
    Route::get('/pengawas', fn() => view('Rekapitulasi.rekappengawas.rekappengawas'))->name('pengawas');
});

/* --- MASTER --- */
Route::get('/master-petugas', fn() => view('masterpetugas'))->name('master.petugas');
Route::get('/master-kegiatan', fn() => view('masterkegiatan'))->name('master.kegiatan');
Route::get('/user', fn() => view('user'))->name('user');
