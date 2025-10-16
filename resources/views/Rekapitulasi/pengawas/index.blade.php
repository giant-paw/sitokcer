@extends('layouts.app')

@section('title', 'Rekapitulasi Pengawas')
@section('header-title', 'Rekapitulasi Pengawas')

{{-- Menambahkan CSS Kustom untuk Halaman Ini --}}
@push('styles')
<style>
    .card.elegant-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        background-color: #ffffff;
        overflow: hidden; /* Penting agar card-body tidak tumpah */
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

        <h4 class="mb-3 text-secondary">Rekapitulasi Pengawas</h4>
        
        <div class="card elegant-card mb-4">
            <div class="card-body">
                <form method="get" id="searchForm">
                    <div class="input-group search-group" style="max-width: 360px">
                        <input type="text" id="searchInput" class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama pengawas..." autocomplete="off">
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
                    {{-- Diberi class modern-table --}}
                    <table class="table table-hover mb-0 align-middle modern-table">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 56px">#</th>
                                <th class="text-start">Pengawas</th>
                                <th>Total Responden Diawasi</th>
                                <th style="width: 180px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapPengawas as $index => $pengawas)
                                <tr>
                                    <td class="text-center fw-bold text-secondary">{{ $rekapPengawas->firstItem() + $index }}</td>
                                    <td class="text-start">{{ $pengawas->nama_pengawas }}</td>
                                    <td class="text-center">{{ $pengawas->total_responden }}</td>
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-primary action-button"
                                            data-bs-toggle="modal" data-bs-target="#detailModal"
                                            data-pengawas="{{ $pengawas->nama_pengawas }}">
                                            <i class="bi bi-eye"></i>
                                            Lihat Detail
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

                @if ($rekapPengawas->hasPages())
                    <div class="card-footer bg-white">
                        {{ $rekapPengawas->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal untuk menampilkan detail pencacah per pengawas --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pengawas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 id="namaPengawasModal" class="mb-3">Memuat...</h6>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Pencacah</th>
                                <th class="text-center">Jumlah Responden</th>
                            </tr>
                        </thead>
                        <tbody id="detailTableBody">
                            {{-- Data dimuat oleh JavaScript --}}
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
    {{-- Script tidak perlu diubah, sudah berfungsi dengan baik --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;

            searchInput.addEventListener('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('searchForm').submit();
                }, 500);
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
                                tableContent += `<tr>
                                    <td>${item.pencacah}</td>
                                    <td class="text-center">${item.jumlah_responden}</td>
                                </tr>`;
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