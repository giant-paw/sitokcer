@extends('layouts.app')

@section('title', 'Rekapitulasi Pencacah')

@section('header-title', 'Rekapitulasi Pencacah')

@section('content')
    <div class="container-fluid px-0">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">REKAPITULASI BEBAN KERJA PENCACAH</h4>
        </div>
        
        {{-- Search --}}
        <form method="get" class="mb-3">
            <div class="input-group" style="max-width: 360px">
                {{-- Placeholder diubah karena pengawas sudah dihapus --}}
                <input type="text" class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama pencacah...">
                <button class="btn btn-outline-secondary">Cari</button>
            </div>
        </form>

        {{-- Table --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover table-bordered mb-0 align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="width: 56px">#</th>
                                <th>Pencacah</th>
                                <th>Jumlah Responden</th>
                                <th style="width: 180px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapPencacah as $index => $pencacah)
                                <tr>
                                    <td class="text-center">{{ $rekapPencacah->firstItem() + $index }}</td>
                                    <td>{{ $pencacah->nama_pencacah }}</td>
                                    <td class="text-center">{{ $pencacah->total_responden }}</td>
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-detail"
                                            data-bs-toggle="modal" data-bs-target="#detailModal"
                                            data-pencacah="{{ $pencacah->nama_pencacah }}">
                                            Lihat Kegiatan
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- Colspan disesuaikan menjadi 4 --}}
                                    <td colspan="4" class="text-center text-muted py-4">
                                        {{ $q ? 'Data tidak ditemukan.' : 'Belum ada data.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if ($rekapPencacah->hasPages())
            <div class="card-footer">
                {{ $rekapPencacah->links() }}
            </div>
            @endif
        </div>
    </div>

    {{-- Modal tidak perlu diubah --}}
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
                                <th>Jumlah Responden</th>
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
                            tableContent =
                                '<tr><td colspan="2" class="text-center">Tidak ada kegiatan ditemukan.</td></tr>';
                        }
                        modalBody.innerHTML = tableContent;
                    })
                    .catch(error => {
                        console.error('Error fetching details:', error);
                        modalBody.innerHTML =
                            '<tr><td colspan="2" class="text-center">Gagal memuat data.</td></tr>';
                    });
            });
        });
    </script>
@endpush