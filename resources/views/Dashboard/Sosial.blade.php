@extends('layouts.app')

{{-- [PERUBAHAN] Title diubah --}}
@section('title', 'Dashboard Tim Sosial')
@section('header-title', 'Dashboard Tim Sosial')

@section('content')
<div class="container-fluid">
    {{-- Header Section with Year Filter --}}
    <div class="row mb-4">
        <div class="col-md-7">
            {{-- [PERUBAHAN] Teks diubah --}}
            <h3 class="mb-1 fw-bold">Dashboard Tim Sosial</h3>
            <p class="text-muted mb-0">Monitoring dan evaluasi kegiatan sosial tahun {{ $selectedTahun }}</p>
        </div>
        <div class="col-md-3">
            {{-- Filter Tahun --}}
            {{-- [PERUBAHAN] Route diubah --}}
            <form action="{{ route('dashboard.sosial.index') }}" method="GET" id="filterTahunForm">
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
                    
                    {{-- Statistics Cards - Hanya Selesai & Belum Selesai --}}
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

                    {{-- Progress Bar - Hanya Selesai & Belum Selesai --}}
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
            {{-- [PERUBAHAN] Route diubah --}}
            <a href="{{ route('dashboard.sosial.tahunan', ['tahun' => $selectedTahun]) }}" class="card-clickable">
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
            {{-- [PERUBAHAN] Route diubah --}}
            <a href="{{ route('dashboard.sosial.triwulanan', ['tahun' => $selectedTahun]) }}" class="card-clickable">
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
                        <div class="row g-2">
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Total</small><h5 class="mb-0 fw-bold">{{ $triwulanan->total ?? 0 }}</h5></div></div>
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Selesai</small><h5 class="mb-0 fw-bold text-success">{{ $triwulanan->selesai ?? 0 }}</h5></div></div>
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Belum</small><h5 class="mb-0 fw-bold text-danger">{{ $triwulanan->belum_selesai ?? 0 }}</h5></div></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- [PERUBAHAN] Kegiatan Semesteran --}}
        <div class="col-lg-4">
            {{-- [PERUBAHAN] Route diubah ke .semesteran --}}
            <a href="{{ route('dashboard.sosial.semesteran', ['tahun' => $selectedTahun]) }}" class="card-clickable">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            {{-- [PERUBAHAN] Icon diubah --}}
                            <div class="bg-light rounded p-2 me-3"><i class="bi bi-calendar2-half text-primary fs-4"></i></div>
                            <div>
                                {{-- [PERUBAHAN] Teks diubah --}}
                                <h6 class="mb-0 fw-semibold">Kegiatan Semesteran</h6>
                                <small class="text-muted">Periode 6 Bulan</small>
                            </div>
                        </div>
                         <div class="mb-3">
                            {{-- [PERUBAHAN] Variabel diubah ke $semesteran --}}
                            @php
                                $progressSemesteran = ($semesteran->total ?? 0) > 0 ? round((($semesteran->selesai ?? 0) / $semesteran->total) * 100, 1) : 0;
                            @endphp
                            <div class="d-flex justify-content-between mb-2">
                                <small class="text-muted">Progress</small>
                                {{-- [PERUBAHAN] Variabel diubah ke $progressSemesteran --}}
                                <small class="fw-semibold">{{ $progressSemesteran }}%</small>
                            </div>
                            <div class="progress" style="height: 6px;">
                                {{-- [PERUBAHAN] Variabel diubah ke $progressSemesteran --}}
                                <div class="progress-bar bg-primary" style="width: {{ $progressSemesteran }}%"></div>
                            </div>
                        </div>
                        <div class="row g-2">
                            {{-- [PERUBAHAN] Variabel diubah ke $semesteran --}}
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Total</small><h5 class="mb-0 fw-bold">{{ $semesteran->total ?? 0 }}</h5></div></div>
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Selesai</small><h5 class="mb-0 fw-bold text-success">{{ $semesteran->selesai ?? 0 }}</h5></div></div>
                            <div class="col-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block mb-1">Belum</small><h5 class="mb-0 fw-bold text-danger">{{ $semesteran->belum_selesai ?? 0 }}</h5></div></div>
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