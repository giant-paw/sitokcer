@extends('layouts.app')

@section('title', 'Detail Petugas')
@section('header-title', 'Detail Petugas')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
            <h4 class="card-title mb-0">{{ $petugas->nama_petugas }}</h4>
            <a href="{{ route('master.petugas.index') }}" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
            </a>
        </div>
        <div class="card-body">
            <dl class="row">
                {{-- Informasi Utama --}}
                <dt class="col-sm-3">Nama Petugas</dt>
                <dd class="col-sm-9">{{ $petugas->nama_petugas ?? '-' }}</dd>

                <dt class="col-sm-3">Kategori</dt>
                <dd class="col-sm-9">{{ $petugas->kategori ?? '-' }}</dd>

                <dt class="col-sm-3">NIK</dt>
                <dd class="col-sm-9">{{ $petugas->nik ?? '-' }}</dd>

                <dt class="col-sm-3">No. HP</dt>
                <dd class="col-sm-9">{{ $petugas->no_hp ?? '-' }}</dd>

                <dt class="col-sm-3">Posisi</dt>
                <dd class="col-sm-9">{{ $petugas->posisi ?? '-' }}</dd>

                {{-- Informasi Detail Lainnya --}}
                <hr class="my-3">

                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $petugas->email ?? '-' }}</dd>

                <dt class="col-sm-3">Alamat</dt>
                <dd class="col-sm-9">{{ $petugas->alamat ?? '-' }}</dd>

                <dt class="col-sm-3">Kecamatan</dt>
                <dd class="col-sm-9">{{ $petugas->kecamatan ?? '-' }}</dd>

                <dt class="col-sm-3">Tanggal Lahir</dt>
                <dd class="col-sm-9">{{ $petugas->tgl_lahir ? $petugas->tgl_lahir->isoFormat('D MMMM YYYY') : '-' }}</dd>

                <dt class="col-sm-3">Pendidikan</dt>
                <dd class="col-sm-9">{{ $petugas->pendidikan ?? '-' }}</dd>
                
                <dt class="col-sm-3">Pekerjaan</dt>
                <dd class="col-sm-9">{{ $petugas->pekerjaan ?? '-' }}</dd>
            </dl>
        </div>
        <div class="card-footer bg-light text-end">
            <a href="{{ route('master.petugas.index', ['edit_id' => $petugas->id_petugas]) }}" class="btn btn-warning">
                <i class="bi bi-pencil-square"></i> Edit
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteDataModal" onclick="deleteData({{ $petugas->id_petugas }})">
                <i class="bi bi-trash"></i> Hapus
            </button>
        </div>
    </div>
</div>

{{-- [PERBAIKAN] Kode modal hapus ditempel langsung di sini --}}
<div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteDataModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="deleteModalBody">
                    Apakah Anda yakin ingin menghapus data petugas ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">Ya, Hapus</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Fungsi deleteData diperlukan oleh tombol Hapus di halaman ini
    function deleteData(id) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/master-petugas/${id}`; 
        document.getElementById('deleteModalBody').innerText = 'Apakah Anda yakin ingin menghapus data petugas ini? Tindakan ini tidak dapat dibatalkan.';
        document.getElementById('confirmDeleteButton').onclick = function() {
            deleteForm.submit();
        }
    }
</script>
@endpush

