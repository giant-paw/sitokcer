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
        overflow: hidden;
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
    }

    .modern-table thead th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
        padding: 1rem;
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
</style>
@endpush


@section('content')
    <div class="container-fluid px-0">

        <h4 class="mb-3 text-secondary">Rekapitulasi Pencacah</h4>
        
        <div class="card elegant-card mb-4">
            <div class="card-body">
                <form method="get" class="mb-3" id="searchForm">
                    <div class="input-group search-group" style="max-width: 360px">
                        <input type="text" id="searchInput" class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama pencacah..." autocomplete="off">
                         <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                    </div>
                </form>
            </div>
        </div>

        <div id="tableContainer">
            <div class="card elegant-card">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle modern-table">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="width: 56px">#</th>
                                <th class="text-start">Pencacah</th>
                                <th>Jumlah Responden</th>
                                <th style="width: 180px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapPencacah as $index => $pencacah)
                                <tr>
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
                                    <td colspan="4" class="text-center text-muted py-5">
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
    {{-- Script tidak perlu diubah --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                    .then(response => response.json())
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
                        modalBody.innerHTML = '<tr><td colspan="2" class="text-center">Gagal memuat data.</td></tr>';
                    });
            });
        });
    </script>
@endpush