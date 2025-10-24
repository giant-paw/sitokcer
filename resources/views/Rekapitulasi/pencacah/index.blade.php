@extends('layouts.app')

@section('title', 'Rekapitulasi Pencacah')
@section('header-title', 'Rekapitulasi Pencacah')

{{-- Menambahkan CSS Kustom untuk Halaman Ini --}}
@push('styles')
<style>
    .card.elegant-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        background-color: #ffffff;
        overflow: visible;
    }

    .card.elegant-card.mb-4 {
    position: relative;
    z-index: 10;
}

    .search-group .form-control {
        border-right: none;
    }

    .search-group .input-group-text {
        background-color: transparent;
        border-left: none;
    }

    .modern-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .modern-table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
        padding: 1rem;
        vertical-align: middle;
    }

    .modern-table tbody tr {
        transition: background-color 0.2s ease-in-out;
    }

    .modern-table tbody tr:hover {
        background-color: #f1f3f5;
    }
    
    .modern-table tbody td {
        padding: 1rem;
        border-top: 1px solid #eaecf0;
        color: #212529;
        vertical-align: middle;
    }
    
    .modern-table tbody tr:first-child td {
        border-top: none;
    }

    .action-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }
    
    /* CSS untuk menyembunyikan elemen saat mencetak halaman ini via window.print() */
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
        }

        .card, .card-footer {
            box-shadow: none !important;
            border: none !important;
        }

        .modern-table th:first-child, .modern-table td:first-child,
        .modern-table th:last-child, .modern-table td:last-child {
            display: none;
        }
    }
</style>
@endpush


@section('content')
    <div class="container-fluid px-0">

        <h4 class="mb-3 text-secondary">Rekapitulasi Pencacah</h4>
        
        <div class="card elegant-card mb-4">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <form method="get" class="mb-0" id="searchForm">
                    <div class="input-group search-group" style="max-width: 360px">
                        <input type="text" id="searchInput" class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama pencacah..." autocomplete="off">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                    </div>
                </form>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-printer"></i> Cetak Laporan
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('rekapitulasi.pencacah.printAll', ['q' => $q]) }}" target="_blank">
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

        <div id="tableContainer">
            <div class="card elegant-card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle modern-table">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="width: 30px;"><input class="form-check-input" type="checkbox" id="checkAll" title="Pilih Semua"></th>
                                <th style="width: 56px">#</th>
                                <th class="text-start">Pencacah</th>
                                <th>Jumlah Responden</th>
                                <th style="width: 180px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapPencacah as $index => $pencacah)
                                <tr>
                                    <td class="text-center"><input class="form-check-input row-checkbox" type="checkbox" name="pencacah_ids[]" value="{{ $pencacah->nama_pencacah }}"></td>
                                    <td class="text-center fw-bold text-secondary">{{ $rekapPencacah->firstItem() + $index }}</td>
                                    <td class="text-start">{{ $pencacah->nama_pencacah }}</td>
                                    <td class="text-center">{{ $pencacah->total_responden }}</td>
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary action-button"
                                            data-bs-toggle="modal" data-bs-target="#detailModal"
                                            data-pencacah="{{ $pencacah->nama_pencacah }}">
                                            <i class="bi bi-eye"></i>
                                            Lihat Kegiatan
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        {{ $q ? 'Data tidak ditemukan.' : 'Belum ada data.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if ($rekapPencacah->hasPages())
                <div class="card-footer bg-white">
                    {{ $rekapPencacah->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal untuk detail kegiatan pencacah --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel">Daftar Kegiatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 id="namaPencacahModal" class="mb-3">Memuat...</h6>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Kegiatan</th>
                                <th class="text-center">Jumlah Responden</th>
                            </tr>
                        </thead>
                        <tbody id="detailKegiatanBody">
                            {{-- Data akan dimuat oleh JavaScript --}}
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
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