@extends('layouts.app')

@section('title', 'Dashboard Distribusi')
@section('header-title', 'Dashboard Distribusi')

@section('content')
<div class="container-fluid">
    <h3 class="fw-bold mb-4">Dashboard Kegiatan Distribusi</h3>

    {{-- 🔹 Ringkasan Umum --}}
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-3">
                <h6 class="text-muted mb-1">Total Kegiatan</h6>
                <h3 class="fw-bold text-primary">{{ $totalSemua }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-3">
                <h6 class="text-muted mb-1">Selesai</h6>
                <h3 class="fw-bold text-success">{{ $totalSelesai }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-3">
                <h6 class="text-muted mb-1">Proses</h6>
                <h3 class="fw-bold text-warning">{{ $totalProses }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-3">
                <h6 class="text-muted mb-1">Belum Mulai</h6>
                <h3 class="fw-bold text-secondary">{{ $totalBelum }}</h3>
            </div>
        </div>
    </div>

    {{-- 🔹 Grafik --}}
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Kegiatan per Bulan (12 Bulan Terakhir)</h5>
                    <canvas id="chartBulan" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Top 10 Pencacah</h5>
                    <canvas id="chartPencacah" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dataBulan = {!! json_encode($dataBulan) !!};
    const kegiatanPerPencacah = {!! json_encode($kegiatanPerPencacah) !!};

    // Grafik per Bulan
    new Chart(document.getElementById('chartBulan'), {
        type: 'line',
        data: {
            labels: Object.keys(dataBulan),
            datasets: [{
                label: 'Jumlah Kegiatan',
                data: Object.values(dataBulan),
                fill: true,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.1)',
                tension: 0.3,
                pointBackgroundColor: '#0d6efd'
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                legend: { display: true, position: 'top' }
            }
        }
    });

    // Grafik per Pencacah
    new Chart(document.getElementById('chartPencacah'), {
        type: 'bar',
        data: {
            labels: kegiatanPerPencacah.map(p => p.pencacah),
            datasets: [{
                label: 'Total Kegiatan',
                data: kegiatanPerPencacah.map(p => p.total),
                backgroundColor: 'rgba(25,135,84,0.7)'
            }]
        },
        options: {
            indexAxis: 'y',
            scales: { x: { beginAtZero: true } }
        }
    });
});
</script>
@endpush
@endsection
