@extends('layouts.app')

@section('title', 'Rekapitulasi Pencacah')
@section('header-title', 'Rekapitulasi Pencacah')

{{-- Menambahkan CSS Kustom untuk Halaman Ini --}}
@push('styles')
<style>
    /* Style kustom tetap dipertahankan */
    .card.elegant-card {
        border: none;
        border-radius: 12px; /* Lebih rounded dari default global */
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        background-color: var(--card-bg, #ffffff); /* Pakai var global */
        overflow: visible; /* Biarkan overflow visible */
        margin-bottom: var(--spacing-lg, 1.5rem); /* Gunakan spacing global */
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
                <h2 class="page-title">Rekapitulasi Pencacah</h2>
                <p class="page-subtitle">Ringkasan jumlah responden per pencacah</p>
            </div>
        </div>

        {{-- 2. Filter Card (Mempertahankan style elegant-card kustom) --}}
        <div class="card elegant-card filter-card">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                {{-- 3. Menggunakan search-form dari global.css --}}
                <form method="get" class="search-form mb-0 flex-grow-1" id="searchForm" style="min-width: 280px; max-width: 400px;">
                     <input type="text" id="searchInput" class="search-input" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama pencacah..." autocomplete="off">
                     {{-- Tombol search tidak eksplisit, submit on input change --}}
                </form>

                <div class="btn-group">
                    {{-- 4. Tombol Print (Tetap menggunakan Bootstrap standar untuk dropdown) --}}
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('rekapitulasi.pencacah.printAll', ['q' => $q]) }}" target="_blank">
                                Cetak Semua
                            </a>
                        </li>
                        <li>
                            <button class="dropdown-item " type="button" id="printCurrentPage" >
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
                 {{-- Tambahkan Toolbar Kosong jika perlu spacing konsisten (opsional) --}}
                 {{-- <div class="toolbar"></div> --}}

                {{-- 6. Menggunakan .table-wrapper dan .data-table --}}
                <div class="table-wrapper">
                    <table class="data-table"> {{-- Hapus table-hover, sudah ada di global --}}
                        <thead>
                            <tr> {{-- Hapus text-center, default left --}}
                                {{-- 7. Gunakan .th-checkbox & .table-checkbox --}}
                                <th class="th-checkbox">
                                    <input class="table-checkbox" type="checkbox" id="checkAll" title="Pilih Semua">
                                </th>
                                <th style="width: 56px">#</th>
                                <th>Pencacah</th> {{-- Hapus text-start, default left --}}
                                <th class="text-center">Jumlah Responden</th> {{-- Tetap center --}}
                                {{-- 8. Gunakan .th-action --}}
                                <th class="th-action">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapPencacah as $index => $pencacah)
                                <tr>
                                    {{-- 9. Gunakan .td-checkbox & .table-checkbox --}}
                                    <td class="td-checkbox">
                                        <input class="table-checkbox row-checkbox" type="checkbox" name="pencacah_ids[]" value="{{ $pencacah->nama_pencacah }}">
                                    </td>
                                    <td class="text-center fw-bold text-secondary">{{ $rekapPencacah->firstItem() + $index }}</td>
                                    <td>{{ $pencacah->nama_pencacah }}</td> {{-- Hapus text-start --}}
                                    <td class="text-center">{{ $pencacah->total_responden }}</td>
                                     {{-- 10. Gunakan .td-action dan .action-buttons (jika perlu) & .btn-outline-primary --}}
                                    <td class="td-action">
                                         <div class="action-buttons"> {{-- Tambahkan wrapper jika ada >1 tombol --}}
                                             <button type="button" class="btn-icon btn-icon-view" {{-- Ubah ke button style icon --}}
                                                title="Lihat Detail"
                                                data-bs-toggle="modal" data-bs-target="#detailModal"
                                                data-pencacah="{{ $pencacah->nama_pencacah }}">
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
                                            <a href="{{ route('rekapitulasi.pencacah.index') }}" class="empty-link">Reset pencarian</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 12. Gunakan .table-footer & .footer-pagination --}}
                @if ($rekapPencacah->hasPages())
                <div class="table-footer">
                     {{-- Info halaman (opsional, bisa ditambahkan jika perlu) --}}
                    <div class="footer-info">
                         Displaying {{ $rekapPencacah->firstItem() ?? 0 }} - {{ $rekapPencacah->lastItem() ?? 0 }} of {{ $rekapPencacah->total() }}
                    </div>
                    <div class="footer-pagination">
                        {{ $rekapPencacah->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal untuk detail kegiatan pencacah --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered"> {{-- Tambah centered --}}
            {{-- 13. Terapkan .modern-modal --}}
            <div class="modal-content modern-modal">
                <div class="modal-header">
                     <div class="modal-header-content">
                        <h5 class="modal-title" id="detailModalLabel">Daftar Kegiatan</h5>
                        <p class="modal-subtitle" id="namaPencacahModal">Memuat...</p>
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
                                    <th>Nama Kegiatan</th>
                                    <th class="text-center" style="width: 150px;">Jumlah Responden</th>
                                </tr>
                            </thead>
                            <tbody id="detailKegiatanBody">
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

    /**
     * Membuat konten HTML untuk jendela cetak dan membukanya.
     * @param {Array} data - Array objek data pencacah.
     * @param {string} title - Judul dokumen cetak.
     */
    function openPrintWindow(data, title) {
        let content = '';
        data.forEach((pencacah, index) => {
            let activityRows = '';
            if (pencacah.kegiatan && pencacah.kegiatan.length > 0) {
                pencacah.kegiatan.forEach(keg => {
                    activityRows += `
                        <tr>
                            <td>${keg.nama_kegiatan}</td>
                            <td class="text-center">${keg.jumlah_responden}</td>
                        </tr>`;
                });
            } else {
                activityRows = '<tr><td colspan="2" class="text-center" style="font-size: 11px; color: #6c757d;">Tidak ada kegiatan.</td></tr>';
            }

            content += `
                <tr class="main-row">
                    <td class="text-center">${index + 1}</td>
                    <td>${pencacah.nama_pencacah}</td>
                    <td class="text-center">${pencacah.total_responden}</td>
                    <td>
                        <table class="activity-table">
                            <thead><tr><th>Nama Kegiatan</th><th class="text-center" style="width: 30%">Responden</th></tr></thead>
                            <tbody>${activityRows}</tbody>
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
                .activity-table { margin: 8px 0; width: 95%; border: none; }
                .activity-table th, .activity-table td { font-size: 11px; padding: 5px; border: 1px solid #e9ecef; }
                .activity-table th { background-color: #fff; } @page { size: A4; margin: 20mm; }
                @media print { body { -webkit-print-color-adjust: exact; } }
            </style>
            </head><body>
            <h3 style="text-align: center; margin-bottom: 1.5rem;">${title}</h3>
            <table>
                <thead><tr><th class="text-center" style="width: 5%;">#</th><th style="width: 45%;">Pencacah</th><th class="text-center" style="width: 15%;">Total Responden</th><th style="width: 35%;">Detail Kegiatan</th></tr></thead>
                <tbody>${content}</tbody>
            </table>
            <script>window.onload = () => { window.print(); window.close(); }<\/script>
            </body></html>`
        );
        printWindow.document.close();
    }

    /**
     * Mengambil data detail pencacah dari server via AJAX.
     * @param {Array<string>} pencacahNames - Array nama pencacah.
     * @returns {Promise<Array|null>}
     */
    async function fetchPrintData(pencacahNames) {
        try {
            const response = await fetch("{{ route('rekapitulasi.pencacah.printSelected') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ pencacah: pencacahNames })
            });
            if (!response.ok) throw new Error('Gagal mengambil data dari server.');
            return await response.json();
        } catch (error) {
            console.error('Error fetching print data:', error);
            alert('Terjadi kesalahan saat menyiapkan data cetak.');
            return null;
        }
    }

    // Event listener untuk "Cetak Halaman Ini"
    printCurrentPageBtn.addEventListener('click', async () => {
        const pencacahOnPage = Array.from(document.querySelectorAll('.row-checkbox')).map(cb => cb.value);
        if (pencacahOnPage.length === 0) return alert('Tidak ada data untuk dicetak di halaman ini.');
        
        const data = await fetchPrintData(pencacahOnPage);
        if (data) openPrintWindow(data, 'Laporan Rekapitulasi Pencacah (Halaman Ini)');
    });

    // Event listener untuk "Cetak yang Dipilih"
    printSelectedBtn.addEventListener('click', async () => {
        const selectedPencacah = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
        if (selectedPencacah.length === 0) return;

        const data = await fetchPrintData(selectedPencacah);
        if (data) openPrintWindow(data, 'Laporan Rekapitulasi Pencacah (Data Terpilih)');
    });

    // --- LOGIKA CHECKBOX ---
    const checkAll = document.getElementById('checkAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');

    function togglePrintSelectedButton() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        if (printSelectedBtn) {
            printSelectedBtn.disabled = checkedCount === 0;
        }
        if (checkAll) {
            checkAll.checked = (rowCheckboxes.length > 0 && checkedCount === rowCheckboxes.length);
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            togglePrintSelectedButton();
        });
    }
    
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', togglePrintSelectedButton);
    });

    // --- FUNGSI LAINNYA ---
    // Auto-search script
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    searchInput.addEventListener('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('searchForm').submit();
        }, 500);
    });
    
    // Modal script
    const detailModal = document.getElementById('detailModal');
    detailModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const namaPencacah = button.getAttribute('data-pencacah');
        const modalTitle = detailModal.querySelector('#namaPencacahModal');
        const modalBody = detailModal.querySelector('#detailKegiatanBody');
        modalTitle.textContent = 'Kegiatan untuk: ' + namaPencacah;
        modalBody.innerHTML = '<tr><td colspan="2" class="text-center">Memuat data...</td></tr>';
        let urlTemplate = "{{ route('rekapitulasi.pencacah.detail', ['nama' => 'PLACEHOLDER']) }}";
        const url = urlTemplate.replace('PLACEHOLDER', encodeURIComponent(namaPencacah));
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                let tableContent = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        tableContent += `<tr>
                            <td>${item.nama_kegiatan}</td>
                            <td class="text-center">${item.jumlah_responden}</td>
                        </tr>`;
                    });
                } else {
                    tableContent = '<tr><td colspan="2" class="text-center">Tidak ada kegiatan ditemukan.</td></tr>';
                }
                modalBody.innerHTML = tableContent;
            })
            .catch(error => {
                console.error('Error fetching details:', error);
                modalBody.innerHTML = '<tr><td colspan="2" class="text-center text-danger">Gagal memuat data.</td></tr>';
            });
    });
});
</script>
@endpush