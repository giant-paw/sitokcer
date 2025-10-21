@extends('layouts.app')

@section('title', 'Dashboard Sosial')

@section('content')
<div class="container-fluid py-4">

    {{-- Header dan Ringkasan --}}
    <div class="row mb-4">
        <div class="col">
            <h4 class="fw-bold text-primary">
                <i class="bi bi-people-fill me-2"></i>Dashboard Bidang Sosial
            </h4>
            <p class="text-muted mb-0">Monitoring dan evaluasi kegiatan sosial</p>
        </div>
    </div>

    {{-- Ringkasan Total --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <h6 class="text-muted">Total Kegiatan</h6>
                <h3 class="fw-bold text-primary">{{ $total_semua }}</h3>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <h6 class="text-muted">Selesai</h6>
                <h3 class="fw-bold text-success">{{ $total_selesai }}</h3>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <h6 class="text-muted">Proses</h6>
                <h3 class="fw-bold text-warning">{{ $total_proses }}</h3>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 text-center p-3">
                <h6 class="text-muted">Belum Mulai</h6>
                <h3 class="fw-bold text-secondary">{{ $total_belum_mulai }}</h3>
            </div>
        </div>
    </div>

    {{-- Detail per Periode --}}
    <div class="row g-4">

        {{-- Kegiatan Tahunan --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light rounded p-2 me-3">
                            <i class="bi bi-calendar-year text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">Kegiatan Tahunan</h6>
                            <small class="text-muted">Periode 1 Tahun</small>
                        </div>
                    </div>

                    @php
                        $progressTahunan = ($tahunan->total ?? 0) > 0 ? round((($tahunan->selesai ?? 0) / $tahunan->total) * 100, 1) : 0;
                    @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted">Progress</small>
                        <small class="fw-semibold">{{ $progressTahunan }}%</small>
                    </div>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: {{ $progressTahunan }}%"></div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6"><div class="p-3 bg-light rounded text-center"><small class="text-muted d-block mb-1">Total</small><h5 class="fw-bold">{{ $tahunan->total ?? 0 }}</h5></div></div>
                        <div class="col-6"><div class="p-3 bg-light rounded text-center"><small class="text-muted d-block mb-1">Selesai</small><h5 class="fw-bold text-success">{{ $tahunan->selesai ?? 0 }}</h5></div></div>
                        <div class="col-6"><div class="p-3 bg-light rounded text-center"><small class="text-muted d-block mb-1">Proses</small><h5 class="fw-bold text-warning">{{ $tahunan->proses ?? 0 }}</h5></div></div>
                        <div class="col-6"><div class="p-3 bg-light rounded text-center"><small class="text-muted d-block mb-1">Belum Mulai</small><h5 class="fw-bold text-secondary">{{ $tahunan->belum_mulai ?? 0 }}</h5></div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kegiatan Semesteran --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light rounded p-2 me-3">
                            <i class="bi bi-calendar2-range text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">Kegiatan Semesteran</h6>
                            <small class="text-muted">Periode 6 Bulan</small>
                        </div>
                    </div>

                    @php
                        $progressSemesteran = ($semesteran->total ?? 0) > 0 ? round((($semesteran->selesai ?? 0) / $semesteran->total) * 100, 1) : 0;
                    @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted">Progress</small>
                        <small class="fw-semibold">{{ $progressSemesteran }}%</small>
                    </div>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: {{ $progressSemesteran }}%"></div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6"><div class="p-3 bg-light rounded text-center"><small class="text-muted d-block mb-1">Total</small><h5 class="fw-bold">{{ $semesteran->total ?? 0 }}</h5></div></div>
                        <div class="col-6"><div class="p-3 bg-light rounded text-center"><small class="text-muted d-block mb-1">Selesai</small><h5 class="fw-bold text-success">{{ $semesteran->selesai ?? 0 }}</h5></div></div>
                        <div class="col-6"><div class="p-3 bg-light rounded text-center"><small class="text-muted d-block mb-1">Proses</small><h5 class="fw-bold text-warning">{{ $semesteran->proses ?? 0 }}</h5></div></div>
                        <div class="col-6"><div class="p-3 bg-light rounded text-center"><small class="text-muted d-block mb-1">Belum Mulai</small><h5 class="fw-bold text-secondary">{{ $semesteran->belum_mulai ?? 0 }}</h5></div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kegiatan Triwulanan --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light rounded p-2 me-3">
                            <i class="bi bi-calendar3 text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold">Kegiatan Triwulanan</h6>
                            <small class="text-muted">Periode 3 Bulan</small>
                        </div>
                    </div>

                    @php
                        $progressTriwulanan = ($triwulanan->total ?? 0) > 0 ? round((($triwulanan->selesai ?? 0) / $triwulanan->total) * 100, 1) : 0;
                    @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <small class="text-muted">Progress</small>
                        <small class="fw-semibold">{{ $progressTriwulanan }}%</small>
                    </div>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar bg-primary" style="width: {{ $progressTriwulanan }}%"></div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6"><div class="p-3 bg-light rounded text-center"><small class="text-muted d-block mb-1">Total</small><h5 class="fw-bold">{{ $triwulanan->total ?? 0 }}</h5></div></div>
                        <div class="col-6"><div class="p-3 bg-light rounded text-center"><small class="text-muted d-block mb-1">Selesai</small><h5 class="fw-bold text-success">{{ $triwulanan->selesai ?? 0 }}</h5></div></div>
                        <div class="col-6"><div class="p-3 bg-light rounded text-center"><small class="text-muted d-block mb-1">Proses</small><h5 class="fw-bold text-warning">{{ $triwulanan->proses ?? 0 }}</h5></div></div>
                        <div class="col-6"><div class="p-3 bg-light rounded text-center"><small class="text-muted d-block mb-1">Belum Mulai</small><h5 class="fw-bold text-secondary">{{ $triwulanan->belum_mulai ?? 0 }}</h5></div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- === GRAFIK === --}}
<div class="row mt-5">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-bar-chart-fill text-primary me-1"></i>
                    Grafik Perbandingan Status per Periode
                </h6>
                <canvas id="statusChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-pie-chart-fill text-primary me-1"></i>
                    Proporsi Total Kegiatan
                </h6>
                <canvas id="summaryChart" height="120"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Script Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx1 = document.getElementById('statusChart');
    const statusChart = new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: @json($chartData['labels']),
            datasets: [
                {
                    label: 'Selesai',
                    data: @json($chartData['selesai']),
                    backgroundColor: '#198754'
                },
                {
                    label: 'Proses',
                    data: @json($chartData['proses']),
                    backgroundColor: '#ffc107'
                },
                {
                    label: 'Belum Mulai',
                    data: @json($chartData['belum']),
                    backgroundColor: '#6c757d'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } }
        }
    });

    const ctx2 = document.getElementById('summaryChart');
    const summaryChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Selesai', 'Proses', 'Belum Mulai'],
            datasets: [{
                data: [{{ $total_selesai }}, {{ $total_proses }}, {{ $total_belum_mulai }}],
                backgroundColor: ['#198754', '#ffc107', '#6c757d']
            }]
        },
        options: {
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>


    </div>
</div>
@endsection
