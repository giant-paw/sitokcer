@extends('layouts.app')

@section('title', 'Master Kegiatan')
@section('header-title', 'Master Kegiatan')

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-header bg-light py-3">
                <h4 class="card-title mb-0">Daftar Master Kegiatan</h4>
            </div>
            <div class="card-body">
                <!-- Tombol Aksi dan Pencarian -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#tambahDataModal">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baru
                        </button>
                        <button type="button" class="btn btn-danger" id="bulkDeleteBtn" data-bs-toggle="modal"
                            data-bs-target="#deleteDataModal" disabled>
                            <i class="bi bi-trash me-1"></i> Hapus Terpilih
                        </button>
                    </div>
                    <div class="col-12 col-md-4 mt-2 mt-md-0">
                        <form action="{{ route('master.kegiatan.index') }}" method="GET" class="d-flex">
                            <input type="text" class="form-control me-1" name="search" value="{{ $search ?? '' }}"
                                placeholder="Cari Nama/Deskripsi...">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Tabel Data -->
                <form id="bulkDeleteForm" action="{{ route('master.kegiatan.bulkDelete') }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 1%;"><input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <th>Nama Kegiatan</th>
                                    <th>Deskripsi</th>
                                    <th style="width: 10%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($kegiatan as $k)
                                    <tr>
                                        <td class="text-center"><input type="checkbox" class="form-check-input row-checkbox"
                                                name="ids[]" value="{{ $k->id_master_kegiatan }}"></td>
                                        <td>{{ $k->nama_kegiatan }}</td>
                                        <td>{{ $k->deskripsi }}</td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-warning" title="Edit"
                                                    data-bs-toggle="modal" data-bs-target="#editDataModal"
                                                    onclick="editData({{ $k->id_master_kegiatan }})">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" title="Hapus"
                                                    data-bs-toggle="modal" data-bs-target="#deleteDataModal"
                                                    onclick="deleteData({{ $k->id_master_kegiatan }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3">
                                            <span class="text-muted">Tidak ada data ditemukan.</span>
                                            @if($search)
                                                <a href="{{ route('master.kegiatan.index') }}" class="ms-2">Reset pencarian</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center bg-light">
                <div class="text-muted small mb-2 mb-md-0">
                    Menampilkan {{ $kegiatan->firstItem() ?? 0 }} - {{ $kegiatan->lastItem() ?? 0 }} dari
                    {{ $kegiatan->total() }} data
                </div>
                <div>
                    {{ $kegiatan->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH --}}
    <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <form action="{{ route('master.kegiatan.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahDataModalLabel">Tambah Kegiatan Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" required>
                            @error('nama_kegiatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi"
                                name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
                            @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDataModalLabel">Edit Kegiatan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                id="edit_nama_kegiatan" name="nama_kegiatan" required>
                            @error('nama_kegiatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="edit_deskripsi"
                                name="deskripsi" rows="3"></textarea>
                            @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL HAPUS --}}
    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteDataModalLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="deleteModalBody"></div>
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
        function editData(id) {
            const editForm = document.getElementById('editForm');
            editForm.action = `/master-kegiatan/${id}`;

            fetch(`/master-kegiatan/${id}/edit`)
                .then(response => {
                    if (!response.ok) throw new Error('Data tidak ditemukan');
                    return response.json();
                })
                .then(data => {
                    document.getElementById('edit_nama_kegiatan').value = data.nama_kegiatan || '';
                    document.getElementById('edit_deskripsi').value = data.deskripsi || '';
                    // Hapus error state sebelumnya
                    editForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Tidak dapat memuat data untuk diedit.');
                    bootstrap.Modal.getInstance(document.getElementById('editDataModal'))?.hide();
                });
        }

        function deleteData(id) {
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = `/master-kegiatan/${id}`;
            document.getElementById('deleteModalBody').innerText = 'Apakah Anda yakin ingin menghapus data kegiatan ini?';
            document.getElementById('confirmDeleteButton').onclick = () => deleteForm.submit();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('selectAll');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');
            const confirmDeleteButton = document.getElementById('confirmDeleteButton');

            function updateBulkDeleteBtnState() {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                bulkDeleteBtn.disabled = checkedCount === 0;
            }

            selectAll?.addEventListener('change', () => {
                rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
                updateBulkDeleteBtnState();
            });

            rowCheckboxes.forEach(cb => cb.addEventListener('change', updateBulkDeleteBtnState));
            updateBulkDeleteBtnState();

            bulkDeleteBtn?.addEventListener('click', () => {
                const count = document.querySelectorAll('.row-checkbox:checked').length;
                document.getElementById('deleteModalBody').innerText = `Apakah Anda yakin ingin menghapus ${count} data kegiatan yang dipilih?`;
                confirmDeleteButton.onclick = () => bulkDeleteForm.submit();
            });

            // Logika untuk membuka kembali modal jika ada error validasi
            @if (session('error_modal') == 'tambahDataModal' && $errors->any())
                new bootstrap.Modal(document.getElementById('tambahDataModal')).show();
            @endif

                @if (session('error_modal') == 'editDataModal' && $errors->any() && session('edit_id'))
                    const editModal = new bootstrap.Modal(document.getElementById('editDataModal'));
                    editData({{ session('edit_id') }});
                    editModal.show();
                @endif
            });
    </script>
@endpush