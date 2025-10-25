{{-- Menggunakan layout --}}
@extends('layouts.app')

@section('title', 'Dashboard')
@section('header-title', 'Dashboard')

@section('content')
    <div class="container-fluid px-4 py-5">

        {{-- Hero Section --}}
        <div class="hero-section mb-5">
            <div class="hero-content">
                <h1 class="hero-title">SITOKCER</h1>
                <p class="hero-subtitle">Sistem Monitoring Pekerjaan untuk Efisiensi Maksimal</p>
            </div>
        </div>

        {{-- Main Dashboard Cards --}}
        <div class="dashboard-grid mb-5">
            <a href="{{ route('dashboard.distribusi') }}" class="dash-card dash-card-distribusi">
                <div class="dash-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"></line>
                        <line x1="12" y1="20" x2="12" y2="4"></line>
                        <line x1="6" y1="20" x2="6" y2="14"></line>
                    </svg>
                </div>
                <h3 class="dash-card-title">Distribusi</h3>
                <p class="dash-card-desc">Monitor distribusi & logistik</p>
            </a>

            <a href="{{ route('dashboard.produksi') }}" class="dash-card dash-card-produksi">
                <div class="dash-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M12 1v6m0 6v6m8.66-11.66l-5.2 3M8.54 14l-5.2 3m13.32 0l-5.2-3M8.54 10l-5.2-3"></path>
                    </svg>
                </div>
                <h3 class="dash-card-title">Produksi</h3>
                <p class="dash-card-desc">Data produksi & output</p>
            </a>

            <a href="{{ route('dashboard.sosial') }}" class="dash-card dash-card-sosial">
                <div class="dash-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3 class="dash-card-title">Sosial</h3>
                <p class="dash-card-desc">Statistik sosial & demografi</p>
            </a>

            <a href="{{ route('dashboard.nwa') }}" class="dash-card dash-card-nwa">
                <div class="dash-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                    </svg>
                </div>
                <h3 class="dash-card-title">NWA</h3>
                <p class="dash-card-desc">Analisis & pelaporan</p>
            </a>
        </div>

        {{-- Content Section --}}
        <div class="content-section">
            <div class="row g-4">
                {{-- Info Panel --}}
                <div class="col-lg-7">
                    <div class="info-panel">
                        <h5 class="info-title">Panduan Import Data</h5>
                        <div class="info-content">
                            <div class="info-item">
                                <span class="info-number">1</span>
                                <div class="info-text">
                                    <p>Daftarkan kegiatan baru <a
                                            href="https://docs.google.com/spreadsheets/d/1TViFcsTtvLnpI1BWE4O5E778SBiDbncSbf8a49CKxWs/edit?gid=0#gid=0"
                                            target="_blank" class="info-link">di sini</a>, lalu konfirmasi ke Tim IT</p>
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="info-number">2</span>
                                <div class="info-text">
                                    <p>Download <a
                                            href="https://drive.google.com/file/d/1J2CuyJrA9atzjpesEbJi8JZU7tQEyYGP/edit"
                                            target="_blank" class="info-link">manual penggunaan</a> SITOKCER</p>
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="info-number">3</span>
                                <div class="info-text">
                                    <p>Gunakan <a
                                            href="https://drive.google.com/uc?export=download&id=17uzCIz6mBaomfCNlyYKcA5AjcUCt29jZ"
                                            target="_blank" class="info-link">template Excel</a> untuk import data</p>
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="info-number">4</span>
                                <div class="info-text">
                                    <p>Pastikan kolom kegiatan sesuai dengan master kegiatan</p>
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="info-number">5</span>
                                <div class="info-text">
                                    <p>Isi nama pencacah dan pengawas sesuai master petugas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quick Access --}}
                <div class="col-lg-5">
                    <div class="quick-access-panel">
                        <h5 class="quick-title">Akses Cepat</h5>
                        <div class="quick-grid">
                            <div class="quick-item">
                                <span class="quick-icon">📅</span>
                                <span class="quick-label">IBS Bulanan</span>
                            </div>
                            <div class="quick-item">
                                <span class="quick-icon">📦</span>
                                <span class="quick-label">SKP</span>
                            </div>
                            <div class="quick-item">
                                <span class="quick-icon">💧</span>
                                <span class="quick-label">Air Bersih</span>
                            </div>
                            <a href="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => 'ubinan padi palawija']) }}"
                                class="quick-item quick-item-link">
                                <span class="quick-icon">🌾</span>
                                <span class="quick-label">UTP Palawija</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <style>
        /* Hero Section */
        .hero-section {
            

            {{-- TAMBAHKAN INI --}}
            /* Ganti 'images/hero-foto.jpg' dengan path ke foto Anda di folder public */
            background-image: url("{{ asset('hero-foto.jpg') }}");
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
            position: relative; /* Penting untuk overlay */
            overflow: hidden; /* Menjaga overlay tetap di dalam border-radius */
            /* ---------------- */

            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.2);
        }

        {{-- TAMBAHKAN RULE BARU INI UNTUK OVERLAY --}}
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            /* Ini adalah overlay gradien gelap yang mirip dengan warna asli Anda */
            /* Anda bisa ganti dengan `background: rgba(0, 0, 0, 0.5);` untuk overlay hitam sederhana */
            /* background: linear-gradient(135deg, rgba(71, 71, 72, 0.75), rgba(62, 61, 62, 0.75)); */
            border-radius: 20px; /* Samakan dengan parent */
            z-index: 1; /* Posisikan di atas background-image */
        }

        {{-- TAMBAHKAN INI UNTUK MEMPOSISIKAN KONTEN DI ATAS OVERLAY --}}
        .hero-content {
            position: relative;
            z-index: 2; /* Posisikan di atas overlay (::before) */
        }

        .hero-title {
            font-family: "Libre Baskerville", serif;
            font-weight: 400;
            font-style: normal;
            font-size: 3rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 12px;
            letter-spacing: 2px;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 400;
            margin: 0;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            {{-- DIUBAH (dari 250px) agar kartu bisa lebih sempit --}}
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
        }

        .dash-card {
            background: #ffffff;
            border-radius: 16px;
            {{-- DIUBAH (dari 15px 24px) agar lebih ringkas --}}
            padding: 16px 20px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e5e7eb;
            position: relative;
            overflow: hidden;
        }

        .dash-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        /* .dash-card:hover::before {
        transform: scaleX(1);
    } */

        .dash-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }

        .dash-card-icon {
            {{-- DIUBAH (dari 56px) --}}
            width: 48px;
            {{-- DIUBAH (dari 56px) --}}
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            {{-- DIUBAH (dari 20px) --}}
            margin-bottom: 16px;
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .dash-card-distribusi .dash-card-icon {
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
        }

        .dash-card-produksi .dash-card-icon {
            color: #8b5cf6;
            background: rgba(139, 92, 246, 0.1);
        }

        .dash-card-sosial .dash-card-icon {
            color: #ec4899;
            background: rgba(236, 72, 153, 0.1);
        }

        .dash-card-nwa .dash-card-icon {
            color: #f59e0b;
            background: rgba(245, 158, 11, 0.1);
        }

        .dash-card-title {
            {{-- DIUBAH (dari 1.25rem) --}}
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            {{-- DIUBAH (dari 8px) --}}
            margin-bottom: 4px;
        }

        .dash-card-desc {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        /* Info Panel */
        .info-panel {
            background: #ffffff;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid #e5e7eb;
            height: 100%;
        }

        .info-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 24px;
        }

        .info-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-item {
            display: flex;
            gap: 16px;
            align-items: flex-start;
        }

        .info-number {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            flex-shrink: 0;
        }

        .info-text p {
            margin: 0;
            color: #4b5563;
            font-size: 0.9375rem;
            line-height: 1.6;
        }

        .info-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .info-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Quick Access Panel */
        .quick-access-panel {
            background: #ffffff;
            border-radius: 16px;
            padding: 32px;
            border: 1px solid #e5e7eb;
            height: 100%;
        }

        .quick-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 24px;
        }

        .quick-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .quick-item {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 12px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
            text-decoration: none;
        }

        .quick-item:hover {
            background: #ffffff;
            border-color: #667eea;
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
        }

        .quick-icon {
            font-size: 2rem;
        }

        .quick-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }

        .quick-item-link {
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .hero-section {
                padding: 40px 24px;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .quick-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection