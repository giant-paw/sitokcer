<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SosialTahunanController; // <— tambah ini

Route::get('/', fn() => view('home'))->name('home');

/* DASHBOARD */
Route::get('/dashboard-distribusi', fn() => view('Dashboard.dashboardDistribusi')) // perbaiki typo Dsitribusi -> Distribusi
    ->name('dashboard.distribusi');
Route::get('/dashboard-nwa', fn() => view('Dashboard.dashboardNWA'))->name('dashboard.nwa');
Route::get('/dashboard-produksi', fn() => view('Dashboard.dashboardProduksi'))->name('dashboard.produksi');
Route::get('/dashboard-sosial', fn() => view('Dashboard.dashboardSosial'))->name('dashboard.sosial');

/* --- TIM SOSIAL --- */
Route::prefix('sosial')->name('sosial.')->group(function () {
    // CRUD Sosial Tahunan (index/create/store/show/edit/update/destroy)
    Route::resource('tahunan', SosialTahunanController::class);

    // Rute lain tetap
    Route::get('/kegiatan-triwulanan/seruti', fn() => view('timSosial.seruti'))->name('seruti');
    Route::get('/kegiatan-semesteran/sakernas', fn() => view('timSosial.sakemas'))->name('sakernas');
    Route::get('/kegiatan-semesteran/susenas', fn() => view('timSosial.susenas'))->name('susenas');
});

/* --- TIM DISTRIBUSI --- */
Route::prefix('distribusi')->name('distribusi.')->group(function () {
    Route::get('/tahunan', fn() => view('timDistribusi.distribusitahunan'))->name('tahunan');
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
    Route::get('/tahunan', fn() => view('timProduksi.produksitahunan'))->name('tahunan');
    Route::get('/kegiatan-caturwulan/ubinan-padi-palawija', fn() => view('timProduksi.ubinanpadipalawija'))->name('ubinanpadipalawija');
    Route::get('/kegiatan-caturwulan/update-utp-palawija', fn() => view('timProduksi.updateingutppalawija'))->name('updateutppalawija');
    Route::get('/kegiatan-triwulan/sktr', fn() => view('timProduksi.SKTR'))->name('sktr');
    Route::get('/kegiatan-triwulan/tpi', fn() => view('timProduksi.TPI'))->name('tpi');
    Route::get('/kegiatan-triwulan/sphbst', fn() => view('timProduksi.SPHBST'))->name('sphbst');
    Route::get('/kegiatan-triwulan/sphtbf', fn() => view('timProduksi.SPHTBF'))->name('sphtbf');
    Route::get('/kegiatan-triwulan/sphth', fn() => view('timProduksi.SPHTH'))->name('sphth');
    Route::get('/kegiatan-triwulan/air-bersih', fn() => view('timProduksi.airbersih'))->name('airbersih');
    Route::get('/kegiatan-bulanan/ksapadi', fn() => view('timProduksi.KSAPadi'))->name('ksapadi');
    Route::get('/kegiatan-bulanan/ksajagung', fn() => view('timProduksi.KSAJagung'))->name('ksajagung');
    Route::get('/kegiatan-bulanan/lptb', fn() => view('timProduksi.LPTB'))->name('lptb');
    Route::get('/kegiatan-bulanan/sphsbs', fn() => view('timProduksi.SPHSBS'))->name('sphsbs');
    Route::get('/kegiatan-bulanan/sppalawija', fn() => view('timProduksi.SPpalawija'))->name('sppalawija');
    Route::get('/kegiatan-bulanan/perkebunan', fn() => view('timProduksi.perkebunanbulanan'))->name('perkebunanbulanan');
    Route::get('/kegiatan-bulanan/ibs', fn() => view('timProduksi.IBSbulanan'))->name('ibsbulanan');
});

/* --- TIM NWA --- */
Route::prefix('nwa')->name('nwa.')->group(function () {
    Route::get('/tahunan', fn() => view('timNWA.NWAtahunan'))->name('tahunan');
    Route::get('/triwulanan/sklnp', fn() => view('timNWA.SKLNP'))->name('sklnp');
    Route::get('/triwulanan/snaper', fn() => view('timNWA.snaper'))->name('snaper');
    Route::get('/triwulanan/sktnp', fn() => view('timNWA.SKTNP'))->name('sktnp');
});

/* --- REKAPITULASI --- */
Route::prefix('rekapitulasi')->name('rekapitulasi.')->group(function () {
    Route::get('/pencacah', fn() => view('Rekapitulasi.rekappencacah'))->name('pencacah');
    Route::get('/pengawas', fn() => view('Rekapitulasi.rekappengawas'))->name('pengawas');
});

/* --- MASTER --- */
Route::get('/master-petugas', fn() => view('masterpetugas'))->name('master.petugas');
Route::get('/master-kegiatan', fn() => view('masterkegiatan'))->name('master.kegiatan');
Route::get('/user', fn() => view('user'))->name('user');
