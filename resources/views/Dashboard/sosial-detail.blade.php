@extends('layouts.app') 

@section('title', 'Grafik Kegiatan ' . $periode)
@section('header-title', 'Grafik Kegiatan ' . $periode)

@section('content')
<div class="container-fluid">
    
    @if($chartData->isEmpty())
        <div class="d-flex align-items-center justify-content-center" style="height: 70vh;">
            <div class="text-center">
                <i class="bi bi-bar-chart-line fs-1 text-muted"></i>
                <h4 class="mt-3 text-muted">Tidak ada data kegiatan untuk periode {{ $periode }} tahun {{ $selectedTahun }}.</h4>
                {{-- [PERUBAHAN] Route diubah ke .sosial.index --}}
                <a href="{{ route('dashboard.sosial.index', ['tahun' => $selectedTahun]) }}" class="btn btn-primary mt-3">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-0 fw-bold">Grafik Kegiatan {{ $periode }} (Tahun {{ $selectedTahun }})</h4>
                        <p class="text-muted mb-0">Menampilkan progress realisasi (Selesai) dibandingkan dengan Target.</p>
                    </div>
                    {{-- [PERUBAHAN] Route diubah ke .sosial.index --}}
                    <a href="{{ route('dashboard.sosial.index', ['tahun' => $selectedTahun]) }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Kembali
                    </a>
                </div>

                @php
                    // Buat canvas lebih tinggi jika datanya banyak
                    $chartHeight = max(400, $chartData->count() * 40); // 40px per batang
                @endphp
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

{{-- Script ini SAMA PERSIS dengan 'distribusi-detail' dan akan bekerja --}}
{{-- karena variabel $chartData memiliki struktur yang identik. --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        const chartData = @json($chartData);
        
        if (chartData && chartData.length > 0) {
            
            const labels = chartData.map(item => item.nama_kegiatan);
            const ctx = document.getElementById('kegiatanChart').getContext('2d');
            
            // --- LOGIKA BARU UNTUK GRAFIK 100% ---
            
            // Dataset 1: Persentase Selesai
            const dataSelesai = chartData.map(item => {
                const target = parseFloat(item.target) || 0;
                const selesai = parseFloat(item.realisasi_selesai) || 0;
                if (target === 0) return 0; // Hindari pembagian nol
                const persen = (selesai / target) * 100;
                // Jika realisasi > target, tetap tampilkan 100%
                return persen > 100 ? 100 : persen; 
            });
            
            // Dataset 2: Persentase Sisa (Gray bar)
            const dataSisaTarget = chartData.map(item => {
                const target = parseFloat(item.target) || 0;
                const selesai = parseFloat(item.realisasi_selesai) || 0;
                if (target === 0) return 0; 
                const persenSelesai = (selesai / target) * 100;
                // Jika selesai > 100%, sisa target adalah 0
                if (persenSelesai >= 100) return 0;
                // Jika tidak, hitung sisanya ke 100%
                return 100 - persenSelesai;
            });
            
            // --- Konfigurasi Chart ---
            const chartConfig = {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Selesai',
                            data: dataSelesai, // Data dalam persentase
                            backgroundColor: '#198754', // Hijau
                        },
                        {
                            label: 'Sisa Target',
                            data: dataSisaTarget, // Data dalam persentase
                            backgroundColor: '#e9ecef', // Abu-abu
                        }
                    ]
                },
                options: {
                    indexAxis: 'y', // Membuat chart horizontal
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { 
                            stacked: true, // Tumpuk di sumbu Y
                            ticks: { autoSkip: false } 
                        },
                        x: { 
                            stacked: true, // Tumpuk di sumbu X
                            beginAtZero: true, 
                            max: 100, // Paksa sumbu X maksimal 100%
                            title: { display: true, text: 'Persentase Progress (%)' } 
                        }
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            // --- Tooltip Kustom ---
                            callbacks: {
                                label: function(context) {
                                    // Hanya tampilkan label untuk dataset 'Selesai'
                                    if (context.dataset.label === 'Selesai') {
                                        const item = chartData[context.dataIndex];
                                        const selesai = parseFloat(item.realisasi_selesai) || 0;
                                        return ` Selesai: ${selesai} (${context.raw.toFixed(1)}%)`;
                                    }
                                    return null; // Sembunyikan tooltip untuk 'Sisa Target'
                                },
                                footer: function(tooltipItems) {
                                    // Tampilkan total di footer
                                    const dataIndex = tooltipItems[0].dataIndex;
                                    const item = chartData[dataIndex];
                                    const target = parseFloat(item.target) || 0;
                                    const selesai = parseFloat(item.realisasi_selesai) || 0;
                                    const sisa = Math.max(0, target - selesai); // Sisa tidak boleh negatif
                                    
                                    if (target === 0) return 'Progress: - (Target 0)';
                                    
                                    return `Target: ${target}\nSisa: ${sisa}`;
                                }
                            }
                        }
                    }
                }
            };
            new Chart(ctx, chartConfig);
        }
    });
</script>
@endpush