@extends('layouts.app')

@section('title', 'Rekapitulasi Pengawas')

@section('header-title', 'Rekapitulasi Pengawas')

@section('content')
    <div class="container-fluid px-0">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">REKAPITULASI BEBAN KERJA PENGAWAS</h4>
        </div>
        
        <form method="get" class="mb-3" id="searchForm">
            <div class="input-group" style="max-width: 360px">
                <input type="text" id="searchInput" class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama pengawas..." autocomplete="off">
                <button class="btn btn-outline-secondary" type="submit">Cari</button>
            </div>
        </form>

        <div id="tableContainer">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover table-bordered mb-0 align-middle">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th style="width: 56px">#</th>
                                    <th>Pengawas</th>
                                    <th>Total Responden Diawasi</th>
                                    <th style="width: 180px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapPengawas as $index => $pengawas)
                                    <tr>
                                        <td class="text-center">{{ $rekapPengawas->firstItem() + $index }}</td>
                                        <td>{{ $pengawas->nama_pengawas }}</td>
                                        <td class="text-center">{{ $pengawas->total_responden }}</td>
                                        <td class="text-center text-nowrap">
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-detail"
                                                data-bs-toggle="modal" data-bs-target="#detailModal"
                                                data-pengawas="{{ $pengawas->nama_pengawas }}">
                                                Lihat Detail
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            {{ $q ? 'Data tidak ditemukan.' : 'Belum ada data.' }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($rekapPengawas->hasPages())
                    <div class="card-footer">
                        {{ $rekapPengawas->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- INI BAGIAN YANG DIPERBAIKI --}}
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
                                <th>Jumlah Responden</th>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- SCRIPT AUTO-SEARCH BARU ---
            const searchInput = document.getElementById('searchInput');
            let searchTimeout;

            searchInput.addEventListener('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('searchForm').submit();
                }, 500);
            });

            // --- Modal script (tidak perlu diubah) ---
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