@extends('layouts.app') {{-- Sesuaikan dengan layout utama Anda --}}

@section('title', 'Grafik Kegiatan ' . $periode)
@section('header-title', 'Grafik Kegiatan ' . $periode)

@section('content')
<div class="container-fluid">
    
    @if($chartData->isEmpty())
        {{-- Pesan jika tidak ada data sama sekali --}}
        <div class="d-flex align-items-center justify-content-center" style="height: 70vh;">
            <div class="text-center">
                <i class="bi bi-bar-chart-line fs-1 text-muted"></i>
                <h4 class="mt-3 text-muted">Tidak ada data kegiatan untuk periode ini.</h4>
                <a href="{{ route('dashboard.distribusi') }}" class="btn btn-primary mt-3">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    @else
        {{-- Container untuk chart, atur tingginya --}}
        @php
            // Cek apakah ini chart 'Progress' (ada target) atau 'Grouped' (tidak ada target)
            // Kita cek properti 'target' di item pertama
            $hasTarget = isset($chartData[0]->target);
            
            // Buat canvas lebih tinggi jika datanya banyak
            $chartHeight = max(400, $chartData->count() * 40); // 40px per batang
        @endphp
        
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0 fw-bold">Grafik Kegiatan {{ $periode }}</h4>
                        @if($hasTarget)
                            <p class="text-muted mb-0">Menampilkan progress realisasi (Selesai) dibandingkan dengan Target.</p>
                        @else
                            <p class="text-muted mb-0">Menampilkan jumlah kegiatan Selesai vs Belum Selesai.</p>
                        @endif
                    </div>
                    <a href="{{ route('dashboard.distribusi') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Kembali
                    </a>
                </div>

                <div style="height: {{ $chartHeight }}px; width: 100%;">
                    <canvas id="kegiatanChart"></canvas>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
{{-- Load Library Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

{{-- Script untuk render chart --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        const chartData = @json($chartData);
        
        if (chartData && chartData.length > 0) {

            // Cek tipe chart berdasarkan data
            const hasTarget = chartData[0].hasOwnProperty('target');
            
            const labels = chartData.map(item => item.nama_kegiatan);
            const ctx = document.getElementById('kegiatanChart').getContext('2d');
            
            let chartConfig;

            if (hasTarget) {
                // --- KONFIGURASI CHART 1: PROGRESS (REALISASI vs TARGET) ---
                // Ini untuk TRIWULANAN
                
                const dataSelesai = chartData.map(item => parseInt(item.realisasi_selesai) || 0);
                const dataSisaTarget = chartData.map(item => {
                    const target = parseInt(item.target) || 0;
                    const selesai = parseInt(item.realisasi_selesai) || 0;
                    const sisa = target - selesai;
                    return sisa < 0 ? 0 : sisa; // Jangan sampai minus
                });

                chartConfig = {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Selesai',
                                data: dataSelesai,
                                backgroundColor: '#28a745', // Hijau
                            },
                            {
                                label: 'Sisa Target',
                                data: dataSisaTarget,
                                backgroundColor: '#e9ecef', // Abu-abu
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y', // Membuat chart horizontal
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { stacked: true, ticks: { autoSkip: false } },
                            x: { stacked: true, beginAtZero: true, title: { display: true, text: 'Jumlah Kegiatan (Realisasi vs Target)' } }
                        },
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ` ${context.dataset.label}: ${context.raw || 0}`;
                                    },
                                    footer: function(tooltipItems) {
                                        const dataIndex = tooltipItems[0].dataIndex;
                                        const item = chartData[dataIndex];
                                        const target = parseInt(item.target) || 0;
                                        const selesai = parseInt(item.realisasi_selesai) || 0;
                                        if (target === 0) return 'Progress: - (Target 0)';
                                        const persen = ((selesai / target) * 100).toFixed(1);
                                        return `Progress: ${selesai} / ${target} (${persen}%)`;
                                    }
                                }
                            }
                        }
                    }
                };

            } else {
                // --- KONFIGURASI CHART 2: GROUPED (SELESAI vs BELUM SELESAI) ---
                // Ini untuk TAHUNAN dan BULANAN
                
                const dataSelesai = chartData.map(item => parseInt(item.realisasi_selesai) || 0);
                const dataBelumSelesai = chartData.map(item => parseInt(item.belum_selesai) || 0);

                chartConfig = {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Selesai',
                                data: dataSelesai,
                                backgroundColor: '#28a745', // Hijau
                            },
                            {
                                label: 'Belum Selesai',
                                data: dataBelumSelesai,
                                backgroundColor: '#ffc107', // Kuning
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y', // Membuat chart horizontal
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: { stacked: true, ticks: { autoSkip: false } },
                            x: { stacked: true, beginAtZero: true, title: { display: true, text: 'Jumlah Kegiatan' } }
                        },
                        plugins: {
                            legend: { position: 'top' },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ` ${context.dataset.label}: ${context.raw || 0}`;
                                    }
                                }
                            }
                        }
                    }
                };
            }

            // Render chart
            new Chart(ctx, chartConfig);
        }
    });
</script>
@endpush