@extends('layouts.app') 

@section('title', 'Grafik Kegiatan NWA ' . $periode)
@section('header-title', 'Grafik Kegiatan NWA ' . $periode)

@section('content')
<div class="container-fluid">
    
    @if($chartData->isEmpty())
        <div class="d-flex align-items-center justify-content-center" style="height: 70vh;">
            <div class="text-center">
                <i class="bi bi-bar-chart-line fs-1 text-muted"></i>
                <h4 class="mt-3 text-muted">Tidak ada data kegiatan untuk periode {{ $periode }} tahun {{ $selectedTahun }}.</h4>
                {{-- PASTIKAN ROUTE KEMBALI MENGARAH KE DASHBOARD NWA --}}
                <a href="{{ route('dashboard.nwa.index', ['tahun' => $selectedTahun]) }}" class="btn btn-primary mt-3">
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
                    {{-- PASTIKAN ROUTE KEMBALI MENGARAH KE DASHBOARD NWA --}}
                    <a href="{{ route('dashboard.nwa.index', ['tahun' => $selectedTahun]) }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Kembali
                    </a>
                </div>

                @php
                    // Menghitung tinggi grafik berdasarkan jumlah item
                    $chartHeight = max(400, $chartData->count() * 40);
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

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const chartData = @json($chartData);
        if (chartData && chartData.length > 0) {
            const labels = chartData.map(item => item.nama_kegiatan);
            
            // 🔥 PERBAIKAN PENTING: getContext HARUS '2d'
            const ctx = document.getElementById('kegiatanChart').getContext('2d');
            
            const dataSelesai = chartData.map(item => {
                const target = parseFloat(item.target) || 0;
                const selesai = parseFloat(item.realisasi_selesai) || 0;
                if (target === 0) return 0;
                const persen = (selesai / target) * 100;
                // Batasi maksimum 100% untuk batang Selesai
                return persen > 100 ? 100 : persen; 
            });
            
            const dataSisaTarget = chartData.map(item => {
                const target = parseFloat(item.target) || 0;
                const selesai = parseFloat(item.realisasi_selesai) || 0;
                if (target === 0) return 0; 
                const persenSelesai = (selesai / target) * 100;
                // Jika sudah 100% atau lebih, sisa targetnya 0
                if (persenSelesai >= 100) return 0;
                return 100 - persenSelesai;
            });
            
            const chartConfig = {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { 
                            label: 'Selesai', 
                            data: dataSelesai, 
                            backgroundColor: '#198754', // Warna Hijau 
                        },
                        { 
                            label: 'Sisa Target', 
                            data: dataSisaTarget, 
                            backgroundColor: '#e9ecef', // Warna Abu-abu muda 
                        }
                    ]
                },
                options: {
                    indexAxis: 'y', // Membuat Horizontal Bar Chart
                    responsive: true, 
                    maintainAspectRatio: false,
                    scales: {
                        y: { stacked: true, ticks: { autoSkip: false } },
                        x: { 
                            stacked: true, 
                            beginAtZero: true, 
                            max: 100, // Maksimum sumbu X adalah 100%
                            title: { display: true, text: 'Persentase Progress (%)' } 
                        }
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                // Menampilkan jumlah realisasi selesai di tooltip bar 'Selesai'
                                label: function(context) {
                                    if (context.dataset.label === 'Selesai') {
                                        const item = chartData[context.dataIndex];
                                        const selesai = parseFloat(item.realisasi_selesai) || 0;
                                        return ` Selesai: ${selesai} (${context.raw.toFixed(1)}%)`;
                                    }
                                    return null;
                                },
                                // Menampilkan Target dan Sisa di footer tooltip
                                footer: function(tooltipItems) {
                                    const dataIndex = tooltipItems[0].dataIndex;
                                    const item = chartData[dataIndex];
                                    const target = parseFloat(item.target) || 0;
                                    const selesai = parseFloat(item.realisasi_selesai) || 0;
                                    const sisa = Math.max(0, target - selesai);
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