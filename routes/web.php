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
    
    // UBAH BAGIAN INI
    // Semula: 'sosial.sosial-triwulanan'
    Route::get('/tahunan', function () {
        return view('timSosial.sosialtahunan'); // Diubah agar sesuai nama file
    })->name('tahunan'); // Nama rute diubah menjadi 'tahunan'

    // Rute lainnya...
    Route::get('/kegiatan-triwulanan/seruti', function () {
        return view('timSosial.seruti');
    })->name('seruti');

    Route::get('/kegiatan-semesteran/sakernas', function () {
        return view('timSosial.sakemas'); // Disesuaikan dengan nama file sakemas.blade.php
    })->name('sakernas');

    Route::get('/kegiatan-semesteran/susenas', function () {
        return view('timSosial.susenas');
    })->name('susenas');

});

// RUTE UNTUK MENU TIM DISTRIBUSI
Route::prefix('distribusi')->name('distribusi.')->group(function () {
    
    // /distribusi/tahunan
    Route::get('/tahunan', function () {
        return view('timDistribusi.distribusitahunan');
    })->name('tahunan');

    // /distribusi/kegiatan-triwulan/spunp
    Route::get('/kegiatan-triwulan/spunp', function () {
        return view('timDistribusi.SPUNP');
    })->name('spunp');
    
    // /distribusi/kegiatan-triwulan/shkk
    Route::get('/kegiatan-triwulan/shkk', function () {
        return view('timDistribusi.SHKK');
    })->name('shkk');

    // -- Rute untuk Distribusi Bulanan --
    Route::get('/bulanan/vhts', function () { return view('timDistribusi.VHTS'); })->name('vhts');
    Route::get('/bulanan/hkd', function () { return view('timDistribusi.HKD'); })->name('hkd');
    Route::get('/bulanan/shpb', function () { return view('timDistribusi.SHPB'); })->name('shpb');
    Route::get('/bulanan/shp', function () { return view('timDistribusi.SHP'); })->name('shp');
    Route::get('/bulanan/shpj', function () { return view('timDistribusi.SHPJ'); })->name('shpj');
    Route::get('/bulanan/shpgb', function () { return view('timDistribusi.SHPBG'); })->name('shpgb');
    Route::get('/bulanan/hd', function () { return view('timDistribusi.HD'); })->name('hd');

});

// RUTE UNTUK MENU TIM PRODUKSI
Route::prefix('produksi')->name('produksi.')->group(function () {
    
    Route::get('/tahunan', function () {
        return view('timProduksi.produksitahunan'); // Perhatikan nama file di gambar adalah prooduksitahunan
    })->name('tahunan');

    // -- Kegiatan Caturwulan --
    Route::get('/kegiatan-caturwulan/ubinan-padi-palawija', function () {
        return view('timProduksi.ubinanpadipalawija');
    })->name('ubinanpadipalawija');

    Route::get('/kegiatan-caturwulan/update-utp-palawija', function () {
        return view('timProduksi.updateingutppalawija');
    })->name('updateutppalawija');

    // -- Kegiatan Triwulan --
    Route::get('/kegiatan-triwulan/sktr', function () { return view('timProduksi.SKTR'); })->name('sktr');
    Route::get('/kegiatan-triwulan/tpi', function () { return view('timProduksi.TPI'); })->name('tpi');
    Route::get('/kegiatan-triwulan/sphbst', function () { return view('timProduksi.SPHBST'); })->name('sphbst');
    Route::get('/kegiatan-triwulan/sphtbf', function () { return view('timProduksi.SPHTBF'); })->name('sphtbf');
    Route::get('/kegiatan-triwulan/sphth', function () { return view('timProduksi.SPHTH'); })->name('sphth');
    Route::get('/kegiatan-triwulan/air-bersih', function () { return view('timProduksi.airbersih'); })->name('airbersih');

    // -- Kegiatan Bulanan --
    Route::get('/kegiatan-bulanan/ksapadi', function () { return view('timProduksi.KSAPadi'); })->name('ksapadi');
    Route::get('/kegiatan-bulanan/ksajagung', function () { return view('timProduksi.KSAJagung'); })->name('ksajagung');
    Route::get('/kegiatan-bulanan/lptb', function () { return view('timProduksi.LPTB'); })->name('lptb');
    Route::get('/kegiatan-bulanan/sphsbs', function () { return view('timProduksi.SPHSBS'); })->name('sphsbs');
    Route::get('/kegiatan-bulanan/sppalawija', function () { return view('timProduksi.SPpalawija'); })->name('sppalawija');
    Route::get('/kegiatan-bulanan/perkebunan', function () { return view('timProduksi.perkebunanbulanan'); })->name('perkebunanbulanan');
    Route::get('/kegiatan-bulanan/ibs', function () { return view('timProduksi.IBSbulanan'); })->name('ibsbulanan');

});

// routes/web.php

// ... (rute-rute Anda yang sudah ada)

// RUTE UNTUK MENU TIM NWA
Route::prefix('nwa')->name('nwa.')->group(function () {
    
    // /nwa/tahunan
    Route::get('/tahunan', function () {
        return view('timNWA.NWAtahunan');
    })->name('tahunan');

    // -- Rute untuk NWA Triwulanan --
    Route::get('/triwulanan/sklnp', function () {
        return view('timNWA.SKLNP');
    })->name('sklnp');

    Route::get('/triwulanan/snaper', function () {
        return view('timNWA.snaper');
    })->name('snaper');

    Route::get('/triwulanan/sktnp', function () {
        return view('timNWA.SKTNP');
    })->name('sktnp');

});

// RUTE UNTUK MENU REKAPITULASI
Route::prefix('rekapitulasi')->name('rekapitulasi.')->group(function () {
    
    // /rekapitulasi/pencacah
    Route::get('/pencacah', function () {
        return view('Rekapitulasi.rekappencacah');
    })->name('pencacah');

    // /rekapitulasi/pengawas
    Route::get('/pengawas', function () {
        return view('Rekapitulasi.rekappengawas');
    })->name('pengawas');

});

Route::get('/master-petugas', function () {
    // Diperbaiki: Mengarah ke file masterpetugas.blade.php
    return view('masterpetugas'); 
})->name('master.petugas');

Route::get('/master-kegiatan', function () {
    // Rute ini sudah benar, menunjuk ke masterkegiatan.blade.php
    return view('masterkegiatan'); 
})->name('master.kegiatan');