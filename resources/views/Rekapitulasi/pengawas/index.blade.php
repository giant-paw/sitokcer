@extends('layouts.app')

@section('title', 'Rekapitulasi Pengawas')
@section('header-title', 'Rekapitulasi Pengawas')

@push('styles')
<style>
    /* Style kustom tetap dipertahankan */
    .card.elegant-card {
        border: none;
        border-radius: 12px; /* Lebih rounded dari default global */
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        background-color: var(--card-bg, #ffffff); /* Pakai var global */
        overflow: visible; /* Mengizinkan dropdown terlihat */
        margin-bottom: var(--spacing-lg, 1.5rem); /* Gunakan spacing global */
    }

    .dropdown-menu .dropdown-item {
            /* Paksa warna teks jadi gelap. Sesuaikan var() jika perlu. */
            color: var(--dark-color, #343a40) !important;
        }

        .dropdown-menu .dropdown-item:hover,
        .dropdown-menu .dropdown-item:focus {
            /* Pastikan teks tetap gelap saat di-hover */
            color: var(--color-gray-900, #1f2937) !important;
            background-color: var(--color-gray-100, #f3f4f6) !important;
        }

        .dropdown-menu .dropdown-item:disabled,
        .dropdown-menu .dropdown-item.disabled {
            color: #adb5bd !important;
            /* Warna abu-abu standar untuk disabled */
        }

    /* Memastikan filter card di atas data card */
    .card.elegant-card.filter-card {
        position: relative;
        z-index: 10;
        margin-bottom: var(--spacing-lg, 1.5rem); /* Konsistenkan margin */
    }

    /* Style print tetap dipertahankan */
    @media print {
        body * {
            visibility: hidden;
        }

        #tableContainer, #tableContainer * {
            visibility: visible;
        }

        #tableContainer {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none !important;
            border: none !important;
        }

        /* Sembunyikan filter saat print */
        .filter-card, .page-header, .table-footer {
            display: none !important;
        }

         /* Sembunyikan card wrapper tabel saat print */
        #tableContainer .data-card {
             box-shadow: none !important;
             border: none !important;
             background: transparent !important;
        }
        /* Sembunyikan toolbar tabel saat print */
         #tableContainer .data-card .toolbar {
             display: none !important;
         }


        .data-table th:first-child, .data-table td:first-child, /* Checkbox */
        .data-table th:last-child, .data-table td:last-child { /* Aksi */
            display: none;
        }
         /* Pastikan pagination tidak tercetak */
        .pagination {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
    <div class="container-fluid px-4 py-4"> {{-- Gunakan padding global --}}

        {{-- 1. Menggunakan Page Header --}}
        <div class="page-header mb-4">
            <div class="header-content">
                <h2 class="page-title">Rekapitulasi Pengawas</h2>
                <p class="page-subtitle">Ringkasan jumlah responden per pengawas</p>
            </div>
        </div>

        {{-- 2. Filter Card (Mempertahankan style elegant-card kustom) --}}
        <div class="card elegant-card filter-card">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                {{-- 3. Menggunakan search-form dari global.css --}}
                <form method="get" class="search-form mb-0 flex-grow-1" id="searchForm" style="min-width: 280px; max-width: 400px;">
                     <input type="text" id="searchInput" class="search-input" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama pengawas..." autocomplete="off">
                     {{-- Tombol submit otomatis via JS, jadi tidak perlu tombol eksplisit --}}
                     {{-- <button class="search-btn" type="submit">...</button> --}}
                </form>

                <div class="btn-group">
                    {{-- 4. Tombol Print (Gunakan style .btn-outline-secondary global) --}}
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-printer me-1"></i> Cetak Laporan {{-- Tambah margin --}}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('rekapitulasi.pengawas.printAll', ['q' => $q]) }}" target="_blank">
                                Cetak Semua
                            </a>
                        </li>
                        <li>
                            <button class="dropdown-item" type="button" id="printCurrentPage">
                                Cetak Halaman Ini
                            </button>
                        </li>
                        <li>
                            <button class="dropdown-item" type="button" id="printSelected" disabled>
                                Cetak yang Dipilih
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Container untuk Print --}}
        <div id="tableContainer">
            {{-- 5. Menggunakan .data-card untuk tabel --}}
            <div class="data-card">
                 {{-- Tambahkan Toolbar Kosong jika perlu spacing konsisten --}}
                 {{-- <div class="toolbar"></div> --}}

                {{-- 6. Menggunakan .table-wrapper dan .data-table --}}
                <div class="table-wrapper">
                    <table class="data-table"> {{-- Hapus table-hover mb-0 align-middle modern-table --}}
                        <thead>
                            <tr>
                                {{-- 7. Gunakan .th-checkbox & .table-checkbox --}}
                                <th class="th-checkbox">
                                    <input class="table-checkbox" type="checkbox" id="checkAll" title="Pilih Semua">
                                </th>
                                <th style="width: 56px">#</th>
                                <th>Pengawas</th>
                                <th class="text-center">Total Responden Diawasi</th> {{-- text-center dipertahankan --}}
                                {{-- 8. Gunakan .th-action --}}
                                <th class="th-action">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapPengawas as $index => $pengawas)
                                <tr>
                                    {{-- 9. Gunakan .td-checkbox & .table-checkbox --}}
                                    <td class="td-checkbox">
                                        <input class="table-checkbox row-checkbox" type="checkbox" name="pengawas_ids[]" value="{{ $pengawas->nama_pengawas }}">
                                    </td>
                                    <td class="text-center fw-bold text-secondary">{{ $rekapPengawas->firstItem() + $index }}</td>
                                    <td>{{ $pengawas->nama_pengawas }}</td>
                                    <td class="text-center">{{ $pengawas->total_responden }}</td>
                                    {{-- 10. Gunakan .td-action dan .action-buttons & .btn-icon-view --}}
                                    <td class="td-action">
                                        <div class="action-buttons">
                                            <button type="button" class="btn-icon btn-icon-view" {{-- Ubah ke button style icon --}}
                                                title="Lihat Detail"
                                                data-bs-toggle="modal" data-bs-target="#detailModal"
                                                data-pengawas="{{ $pengawas->nama_pengawas }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                {{-- 11. Gunakan .empty-state --}}
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        <div class="empty-icon">
                                             <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                                        </div>
                                        <p class="empty-text">{{ $q ? 'Data tidak ditemukan.' : 'Belum ada data.' }}</p>
                                        @if($q)
                                            <a href="{{ route('rekapitulasi.pengawas.index') }}" class="empty-link">Reset pencarian</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 12. Gunakan .table-footer & .footer-pagination --}}
                @if ($rekapPengawas->hasPages())
                <div class="table-footer">
                    <div class="footer-info">
                         Displaying {{ $rekapPengawas->firstItem() ?? 0 }} - {{ $rekapPengawas->lastItem() ?? 0 }} of {{ $rekapPengawas->total() }}
                    </div>
                    <div class="footer-pagination">
                        {{ $rekapPengawas->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal untuk detail --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            {{-- 13. Terapkan .modern-modal --}}
            <div class="modal-content modern-modal">
                <div class="modal-header">
                     <div class="modal-header-content">
                        <h5 class="modal-title">Detail Pengawas</h5>
                        <p class="modal-subtitle" id="namaPengawasModal">Memuat...</p> {{-- Ganti id --}}
                    </div>
                    <button type="button" class="modal-close" data-bs-dismiss="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- 14. Gunakan table style Bootstrap standar di dalam modal --}}
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Pencacah</th> {{-- Ubah header tabel --}}
                                    <th class="text-center" style="width: 150px;">Jumlah Responden</th>
                                </tr>
                            </thead>
                            <tbody id="detailTableBody"> {{-- Ubah id --}}
                                {{-- Data akan dimuat oleh JavaScript --}}
                                <tr><td colspan="2" class="text-center p-5"><span class="spinner-border spinner-border-sm"></span> Memuat...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    {{-- 15. Gunakan .btn-secondary --}}
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const printCurrentPageBtn = document.getElementById('printCurrentPage');
    const printSelectedBtn = document.getElementById('printSelected');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function openPrintWindow(data, title) {
        let content = '';
        data.forEach((pengawas, index) => {
            let detailRows = '';
            if (pengawas.pencacah_list && pengawas.pencacah_list.length > 0) {
                pengawas.pencacah_list.forEach(pencacah => {
                    detailRows += `<tr><td>${pencacah.pencacah}</td><td class="text-center">${pencacah.jumlah_responden}</td></tr>`;
                });
            } else {
                detailRows = '<tr><td colspan="2" class="text-center" style="font-size: 11px; color: #6c757d;">Tidak ada pencacah.</td></tr>';
            }

            content += `
                <tr class="main-row">
                    <td class="text-center">${index + 1}</td>
                    <td>${pengawas.nama_pengawas}</td>
                    <td class="text-center">${pengawas.total_responden}</td>
                    <td>
                        <table class="detail-table">
                            <thead><tr><th>Nama Pencacah</th><th class="text-center" style="width: 30%">Responden</th></tr></thead>
                            <tbody>${detailRows}</tbody>
                        </table>
                    </td>
                </tr>`;
        });

        const printWindow = window.open('', '', 'height=800,width=1000');
        printWindow.document.write(`
            <html><head><title>${title}</title>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; } .text-center { text-align: center; }
                table { width: 100%; border-collapse: collapse; } th, td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
                thead th { background-color: #f8f9fa; font-weight: bold; } .main-row td { font-weight: bold; background-color: #f1f3f5; }
                .detail-table { margin: 8px 0; width: 95%; border: none; }
                .detail-table th, .detail-table td { font-size: 11px; padding: 5px; border: 1px solid #e9ecef; }
                .detail-table th { background-color: #fff; } @page { size: A4; margin: 20mm; }
            </style></head><body>
            <h3 style="text-align: center; margin-bottom: 1.5rem;">${title}</h3>
            <table>
                <thead><tr><th class="text-center" style="width: 5%;">#</th><th style="width: 45%;">Pengawas</th><th class="text-center" style="width: 15%;">Total Responden</th><th style="width: 35%;">Detail Pencacah</th></tr></thead>
                <tbody>${content}</tbody>
            </table>
            <script>window.onload = () => { window.print(); window.close(); }<\/script>
            </body></html>`);
        printWindow.document.close();
    }

    async function fetchPrintData(pengawasNames) {
        try {
            const response = await fetch("{{ route('rekapitulasi.pengawas.printSelected') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ pengawas: pengawasNames })
            });
            if (!response.ok) throw new Error('Gagal mengambil data dari server.');
            return await response.json();
        } catch (error) {
            console.error('Error fetching print data:', error);
            alert('Terjadi kesalahan saat menyiapkan data cetak.');
            return null;
        }
    }

    printCurrentPageBtn.addEventListener('click', async () => {
        const pengawasOnPage = Array.from(document.querySelectorAll('.row-checkbox')).map(cb => cb.value);
        if (pengawasOnPage.length === 0) return alert('Tidak ada data untuk dicetak di halaman ini.');
        const data = await fetchPrintData(pengawasOnPage);
        if (data) openPrintWindow(data, 'Laporan Rekapitulasi Pengawas (Halaman Ini)');
    });

    printSelectedBtn.addEventListener('click', async () => {
        const selectedPengawas = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selectedPengawas.length === 0) return;
        const data = await fetchPrintData(selectedPengawas);
        if (data) openPrintWindow(data, 'Laporan Rekapitulasi Pengawas (Data Terpilih)');
    });

    const checkAll = document.getElementById('checkAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    function togglePrintSelectedButton() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        if (printSelectedBtn) printSelectedBtn.disabled = checkedCount === 0;
        if (checkAll) checkAll.checked = (rowCheckboxes.length > 0 && checkedCount === rowCheckboxes.length);
    }
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => checkbox.checked = this.checked);
            togglePrintSelectedButton();
        });
    }
    rowCheckboxes.forEach(checkbox => checkbox.addEventListener('change', togglePrintSelectedButton));
    
    // --- Skrip Asli (Pencarian dan Modal) ---
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    searchInput.addEventListener('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => document.getElementById('searchForm').submit(), 500);
    });

    const detailModal = document.getElementById('detailModal');
    detailModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const namaPengawas = button.getAttribute('data-pengawas');
        const modalTitle = detailModal.querySelector('#namaPengawasModal');
        const modalBody = detailModal.querySelector('#detailTableBody');

        modalTitle.textContent = 'Pencacah diawasi oleh: ' + namaPengawas;
        modalBody.innerHTML = '<tr><td colspan="2" class="text-center">Memuat data...</td></tr>';
        
        let urlTemplate = "{{ route('rekapitulasi.pengawas.detail', ['nama' => 'PLACEHOLDER']) }}";
        const url = urlTemplate.replace('PLACEHOLDER', encodeURIComponent(namaPengawas));

        fetch(url)
            .then(response => response.json())
            .then(data => {
                let tableContent = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        tableContent += `<tr><td>${item.pencacah}</td><td class="text-center">${item.jumlah_responden}</td></tr>`;
                    });
                } else {
                    tableContent = '<tr><td colspan="2" class="text-center">Tidak ada pencacah ditemukan.</td></tr>';
                }
                modalBody.innerHTML = tableContent;
            });
    });
});
</script>
@endpush