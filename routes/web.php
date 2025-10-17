<?php

use App\Http\Controllers\SerutiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SosialTahunanController;
use App\Http\Controllers\SosialSemesteranController;
use App\Http\Controllers\DistribusiTahunanController;
use App\Http\Controllers\DashboardDistribusiController;
use App\Http\Controllers\DashboardNwaController;
use App\Http\Controllers\DashboardProduksiController;
use App\Http\Controllers\DashboardSosialController;
use App\Http\Controllers\PencacahController;
use App\Http\Controllers\PengawasController;
use App\Http\Controllers\NwaTahunanController;
use App\Http\Controllers\NwaTriwulananController;

Route::get('/', fn() => view('home'))->name('home');

/* DASHBOARD */
Route::get('/dashboard-distribusi', [DashboardDistribusiController::class, 'index'])->name('dashboard.distribusi');
Route::get('/dashboard-nwa', [DashboardNwaController::class, 'index'])->name('dashboard.nwa');
Route::get('/dashboard-produksi', [DashboardProduksiController::class, 'index'])->name('dashboard.produksi');
Route::get('/dashboard-sosial', [DashboardSosialController::class, 'index'])->name('dashboard.sosial');

/* --- TIM SOSIAL --- */
Route::prefix('sosial')->name('sosial.')->group(function () {
    Route::resource('tahunan', SosialTahunanController::class);
    Route::resource('seruti', SerutiController::class);
    Route::resource('semesteran', SosialSemesteranController::class);

    Route::get('/semesteran/{kategori?}', [SosialSemesteranController::class, 'index'])->name('semesteran.index');
    Route::post('/semesteran', [SosialSemesteranController::class, 'store'])->name('semesteran.store');
    Route::put('/semesteran/{id}', [SosialSemesteranController::class, 'update'])->name('semesteran.update');
    Route::delete('/semesteran/{id}', [SosialSemesteranController::class, 'destroy'])->name('semesteran.destroy');
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
    Route::get('/tahunan', fn() => view('timProduksi.produksitahunan'))->name('tahunan.index');

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

/* --- [DIPERBAIKI] TIM NWA --- */
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
Route::get('/master-petugas', fn() => view('masterpetugas'))->name('master.petugas');
Route::get('/master-kegiatan', fn() => view('masterkegiatan'))->name('master.kegiatan');
Route::get('/user', fn() => view('user'))->name('user');
