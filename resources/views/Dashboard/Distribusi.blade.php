@extends('layouts.app')

@section('title', 'Dashboard Tim Distribusi')
@section('header-title', 'Dashboard Tim Distribusi')

@section('content')
<div class="container-fluid">
    {{-- Header Section with Year Filter --}}
    <div class="row mb-4">
        <div class="col-md-7">
            <h3 class="mb-1 fw-bold">Dashboard Tim Distribusi</h3>
            <p class="text-muted mb-0">Monitoring dan evaluasi kegiatan distribusi tahun {{ $selectedTahun }}</p>
        </div>
        <div class="col-md-3">
            {{-- Filter Tahun --}}
            <form action="{{ route('dashboard.distribusi.index') }}" method="GET" id="filterTahunForm">
                <select name="tahun" class="form-select form-select-lg" onchange="this.form.submit()">
                    @forelse($availableTahun as $tahun)
                        <option value="{{ $tahun }}" {{ $tahun == $selectedTahun ? 'selected' : '' }}>
                            Tahun {{ $tahun }}
                        </option>
                    @empty
                        <option>{{ date('Y') }}</option>
                    @endforelse
                </select>
            </form>
        </div>
        <div class="col-md-2 text-end">
            <small class="text-muted">Total Kegiatan</small>
            <h2 class="mb-0 fw-bold text-primary">{{ $total_semua ?? 0 }}</h2>
        </div>
    </div>

    {{-- Ringkasan Umum --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-4 fw-semibold">Ringkasan Keseluruhan (Tahun {{ $selectedTahun }})</h5>
                    
                    {{-- [PERUBAHAN] Statistics Cards - Hanya Selesai & Belum Selesai --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="text-muted">Selesai</span>
                                    <i class="bi bi-check-circle text-success fs-4"></i>
                                </div>
                                <h3 class="mb-1 fw-bold">{{ $total_selesai ?? 0 }}</h3>
                                @php
                                    $persentaseSelesai = $total_semua > 0 ? round(($total_selesai / $total_semua) * 100, 1) : 0;
                                @endphp
                                <small class="text-muted">{{ $persentaseSelesai }}% dari total</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-4 bg-light rounded-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="text-muted">Belum Selesai</span>
                                    <i class="bi bi-clock-history text-danger fs-4"></i>
                                </div>
                                <h3 class="mb-1 fw-bold">{{ $total_belum_selesai ?? 0 }}</h3>
                                @php
                                    $persentaseBelum = $total_semua > 0 ? round(($total_belum_selesai / $total_semua) * 100, 1) : 0;
                                @endphp
                                <small class="text-muted">{{ $persentaseBelum }}% dari total</small>
                            </div>
                        </div>
                    </div>

                    {{-- [PERUBAHAN] Progress Bar - Hanya Selesai & Belum Selesai --}}
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Progress Keseluruhan</span>
                            <span class="small fw-semibold">{{ $persentaseSelesai ?? 0 }}% Selesai</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @php
                                $persenSelesai = $total_semua > 0 ? ($total_selesai / $total_semua) * 100 : 0;
                                $persenBelum = 100 - $persenSelesai;
                            @endphp
                            <div class="progress-bar bg-success" style="width: {{ $persenSelesai }}%"></div>
                            <div class="progress-bar bg-danger" style="width: {{ $persenBelum }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detail per Periode --}}
    <div class="row g-4">
        {{-- Kegiatan Tahunan --}}
        <div class="col-lg-4">
            <a href="{{ route('dashboard.distribusi.tahunan', ['tahun' => $selectedTahun]) }}" class="card-clickable">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-light rounded p-2 me-3"><i class="bi bi-calendar-year text-primary fs-4"></i></div>
                            <div>
                                <h6 class="mb-0 fw-semibold">Kegiatan Tahunan</h6>
                                <small class="text-muted">Periode 1 Tahun</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            @php
                                $progressTahunan = ($tahunan->total ?? 0) > 0 ? round((($tahunan->selesai ?? 0) / $tahunan->total) * 100, 1) : 0;
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Progress</small>
                                <small class="fw-semibold">{{ $progressTahunan }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: {{ $progressTahunan }}%"></div>
                            </div>
                        </div>
                        {{-- [PERUBAHAN] Tampilkan 3 box kecil (Total, Selesai, Belum Selesai) --}}
                        <div class="row g-2">
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Total</small><h5 class="mb-0 fw-bold">{{ $tahunan->total ?? 0 }}</h5></div></div>
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Selesai</small><h5 class="mb-0 fw-bold text-success">{{ $tahunan->selesai ?? 0 }}</h5></div></div>
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Belum</small><h5 class="mb-0 fw-bold text-danger">{{ $tahunan->belum_selesai ?? 0 }}</h5></div></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Kegiatan Triwulanan --}}
        <div class="col-lg-4">
            <a href="{{ route('dashboard.distribusi.triwulanan', ['tahun' => $selectedTahun]) }}" class="card-clickable">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-light rounded p-2 me-3"><i class="bi bi-calendar3 text-primary fs-4"></i></div>
                            <div>
                                <h6 class="mb-0 fw-semibold">Kegiatan Triwulanan</h6>
                                <small class="text-muted">Periode 3 Bulan</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            @php
                                $progressTriwulanan = ($triwulanan->total ?? 0) > 0 ? round((($triwulanan->selesai ?? 0) / $triwulanan->total) * 100, 1) : 0;
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Progress</small>
                                <small class="fw-semibold">{{ $progressTriwulanan }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: {{ $progressTriwulanan }}%"></div>
                            </div>
                        </div>
                        {{-- [PERUBAHAN] Tampilkan 3 box kecil --}}
                        <div class="row g-2">
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Total</small><h5 class="mb-0 fw-bold">{{ $triwulanan->total ?? 0 }}</h5></div></div>
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Selesai</small><h5 class="mb-0 fw-bold text-success">{{ $triwulanan->selesai ?? 0 }}</h5></div></div>
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Belum</small><h5 class="mb-0 fw-bold text-danger">{{ $triwulanan->belum_selesai ?? 0 }}</h5></div></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Kegiatan Bulanan --}}
        <div class="col-lg-4">
            <a href="{{ route('dashboard.distribusi.bulanan', ['tahun' => $selectedTahun]) }}" class="card-clickable">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-light rounded p-2 me-3"><i class="bi bi-calendar-month text-primary fs-4"></i></div>
                            <div>
                                <h6 class="mb-0 fw-semibold">Kegiatan Bulanan</h6>
                                <small class="text-muted">Periode 1 Bulan</small>
                            </div>
                        </div>
                         <div class="mb-3">
                            @php
                                $progressBulanan = ($bulanan->total ?? 0) > 0 ? round((($bulanan->selesai ?? 0) / $bulanan->total) * 100, 1) : 0;
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Progress</small>
                                <small class="fw-semibold">{{ $progressBulanan }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: {{ $progressBulanan }}%"></div>
                            </div>
                        </div>
                        {{-- [PERUBAHAN] Tampilkan 3 box kecil --}}
                        <div class="row g-2">
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Total</small><h5 class="mb-0 fw-bold">{{ $bulanan->total ?? 0 }}</h5></div></div>
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Selesai</small><h5 class="mb-0 fw-bold text-success">{{ $bulanan->selesai ?? 0 }}</h5></div></div>
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Belum</small><h5 class="mb-0 fw-bold text-danger">{{ $bulanan->belum_selesai ?? 0 }}</h5></div></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    .card-clickable .card { transition: all 0.3s ease; }
    .card-clickable:hover .card {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.08) !important;
    }
    .card-clickable, .card-clickable:hover {
        text-decoration: none; color: inherit;
    }
    .bg-light { background-color: #f8f9fa !important; }
    .progress { background-color: #e9ecef; }
    .form-select-lg { font-size: 1.1rem; font-weight: 600; }
</style>
@endsection