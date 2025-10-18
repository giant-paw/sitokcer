{{-- Menggunakan layout. 'layouts.app' berarti folder layouts, file app.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard')
@section('header-title', 'Dashboard Utama')

{{-- Memulai bagian konten --}}
@section('content')
    <div class="container-fluid">

        {{-- Header Selamat Datang --}}
        <div class="text-center mb-5">
            <h1>Selamat Datang di SITOKCER</h1>
            <p class="text-lg text-muted">APLIKASI MONITORING PEKERJAAN AGAR CEPAT DAN LANCAR.</p>
            {{-- Anda bisa menambahkan gambar deskripsi di sini jika ada --}}
            {{-- <img src="..." alt="Deskripsi gambar" class="mt-3" style="max-width: 400px; margin: auto;"> --}}
        </div>

        {{-- Baris Kartu Dashboard Utama --}}
        <div class="row mb-5">
            <div class="col-12 col-md-6 col-lg-3 mb-4">
                <a href="{{ route('tim-distribusi.tahunan.index') }}" class="dashboard-card bg-distribusi">
                    <div class="dashboard-card-icon">
                        <span>📊</span> {{-- Ganti dengan ikon SVG jika ada --}}
                    </div>
                    <div class="dashboard-card-title">Dashboard Distribusi</div>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3 mb-4">
                <a href="{{ route('tim-produksi.tahunan.index') }}" class="dashboard-card bg-produksi">
                    <div class="dashboard-card-icon">
                        <span>⚙️</span> 
                    </div>
                    <div class="dashboard-card-title">Dashboard Produksi</div>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3 mb-4">
                <a href="{{ route('sosial.tahunan.index') }}" class="dashboard-card bg-sosial">
                    <div class="dashboard-card-icon">
                        <span>👥</span>
                    </div>
                    <div class="dashboard-card-title">Dashboard Sosial</div>
                </a>
            </div>
            <div class="col-12 col-md-6 col-lg-3 mb-4">
                <a href="{{ route('nwa.tahunan.index') }}" class="dashboard-card bg-nwa">
                    <div class="dashboard-card-icon">
                        <span>📈</span> 
                    </div>
                    <div class="dashboard-card-title">Dashboard NWA</div>
                </a>
            </div>
        </div>

        {{-- Bagian Informasi & Kartu Fitur --}}
        <div class="row">
            {{-- Kolom Informasi Import --}}
            <div class="col-12 col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Informasi Import Data</h5>
                        <ol class="info-list">
                            <li>Penambahan kegiatan yang berjalan dapat didaftarkan <a href="https://docs.google.com/spreadsheets/d/1TViFcsTtvLnpI1BWE4O5E778SBiDbncSbf8a49CKxWs/edit?gid=0#gid=0">disini</a>, untuk
                                kemudian konfirmasi ke Tim IT.</li>
                            <li>Manual penggunaan Stokcer dapat didownload <a href="https://drive.google.com/file/d/1J2CuyJrA9atzjpesEbJi8JZU7tQEyYGP/edit">disini</a>.</li>
                            <li>Gunakan template excel, yang dapat didownload <a href="https://drive.google.com/uc?export=download&id=17uzCIz6mBaomfCNlyYKcA5AjcUCt29jZ">disini</a>.</li>
                            <li>Pastikan mengisikan kolom kegiatan dengan benar berdasarkan menu master kegiatan karena akan
                                digunakan sebagai dasar tabulasi dan tab filtering.</li>
                            <li>Pastikan mengisikan nama pencacah dan pengawas dengan benar berdasarkan menu pada master
                                petugas, kesalahan penulisan nama akan mengakibatkan data tidak dapat ditarik.</li>
                        </ol>
                    </div>
                </div>
            </div>

            {{-- Kolom Kartu Fitur --}}
            <div class="col-12 col-lg-6">
                <div class="row">
                    <div class="col-12 col-md-4 mb-4">
                        <div class="card card-feature">
                            <div class="card-body text-center">
                                <span class="feature-icon">📄</span>
                                <h6 class="feature-title">SKGB Giling</h6>
                                <p class="feature-desc">Produksi Tahunan View description</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-4">
                        <a href="#" class="feature-card-link">
                            <span class="feature-icon">📜</span>
                            <h6 class="feature-title">SKGB Kering</h6>
                        </a>
                    </div>
                    <div class="col-12 col-md-4 mb-4">
                        <a href="#" class="feature-card-link">
                            <span class="feature-icon">📅</span>
                            <h6 class="feature-title">IBS Bulanan</h6>
                        </a>
                    </div>
                    <div class="col-12 col-md-4 mb-4">
                        <a href="#" class="feature-card-link">
                            <span class="feature-icon">📦</span>
                            <h6 class="feature-title">SKP</h6>
                        </a>
                    </div>
                    <div class="col-12 col-md-4 mb-4">
                        <a href="#" class="feature-card-link">
                            <span class="feature-icon">💧</span>
                            <h6 class="feature-title">Air Bersih</h6>
                        </a>
                    </div>
                    <div class="col-12 col-md-4 mb-4">
                        <a href="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => 'ubinan padi palawija']) }}" class="feature-card-link">
                            <span class="feature-icon">🌾</span>
                            <h6 class="feature-title">Updating UTPPalawija</h6>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
