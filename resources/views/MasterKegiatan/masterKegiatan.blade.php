@extends('layouts.app')

@section('title', 'Master Kegiatan')
@section('header-title', 'Master Kegiatan')

@push('styles')
    {{-- Tidak ada style kustom tambahan, semua diambil dari global.css --}}
@endpush

@section('content')
<div class="container-fluid px-4 py-4"> {{-- Padding global --}}

    {{-- 1. Menggunakan Page Header --}}
    <div class="page-header mb-4">
        <div class="header-content">
            <h2 class="page-title">Master Kegiatan</h2>
            <p class="page-subtitle">Kelola daftar semua kegiatan yang tersedia</p>
        </div>
    </div>

    {{-- 2. Menggunakan .data-card sebagai wrapper utama --}}
    <div class="data-card">
        {{-- 3. Menggunakan .toolbar --}}
        <div class="toolbar">
            <div class="toolbar-left">
                {{-- 4. Tombol .btn-action --}}
                <button type="button" class="btn-action btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    Tambah Baru
                </button>
                <button type="button" class="btn-action btn-danger" id="bulkDeleteBtn" data-bs-toggle="modal" data-bs-target="#deleteDataModal" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    Hapus Terpilih
                </button>
            </div>
            <div class="toolbar-right">
                {{-- 5. Search form --}}
                <form action="{{ route('master.kegiatan.index') }}" method="GET" class="search-form">
                    <input type="text" class="search-input" name="search" value="{{ $search ?? '' }}" placeholder="Cari Nama/Deskripsi...">
                    <button class="search-btn" type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- 6. Alert (Opsional, jika perlu) --}}
        @if (session('success'))
            <div class="alert-success">
                <div class="alert-icon"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> </div>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" data-bs-dismiss="alert"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
            </div>
        @endif
        @if ($errors->any() && !session('error_modal'))
            <div class="alert alert-danger alert-dismissible fade show mx-4" role="alert"> <strong>Error!</strong> Periksa form.<button type="button" class="btn-close" data-bs-dismiss="alert"></button> </div>
        @endif

        {{-- 7. Tabel Data --}}
        <form id="bulkDeleteForm" action="{{ route('master.kegiatan.bulkDelete') }}" method="POST">
            @csrf
            <div class="table-wrapper">
                {{-- 8. Gunakan .data-table --}}
                <table class="data-table">
                    <thead>
                        <tr>
                            {{-- 9. Sesuaikan header tabel --}}
                            <th class="th-checkbox"><input type="checkbox" class="table-checkbox" id="selectAll"></th>
                            <th>Nama Kegiatan</th>
                            <th>Deskripsi</th>
                            <th class="th-action">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($kegiatan as $k)
                            <tr>
                                {{-- 10. Sesuaikan body tabel --}}
                                <td class="td-checkbox"><input type="checkbox" class="table-checkbox row-checkbox" name="ids[]" value="{{ $k->id_master_kegiatan }}"></td>
                                <td class="user-name">{{ $k->nama_kegiatan }}</td> {{-- Pakai .user-name agar bold --}}
                                <td class="text-secondary">{{ $k->deskripsi }}</td> {{-- Pakai .text-secondary --}}
                                <td class="td-action">
                                    {{-- 11. Gunakan .action-buttons & .btn-icon --}}
                                    <div class="action-buttons">
                                        <button type="button" class="btn-icon btn-icon-edit" title="Edit"
                                                data-bs-toggle="modal" data-bs-target="#editDataModal"
                                                onclick="editData({{ $k->id_master_kegiatan }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                        </button>
                                        <button type="button" class="btn-icon btn-icon-delete" title="Hapus"
                                                data-bs-toggle="modal" data-bs-target="#deleteDataModal"
                                                onclick="deleteData({{ $k->id_master_kegiatan }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- 12. Gunakan .empty-state --}}
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <div class="empty-icon"> <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg> </div>
                                    <p class="empty-text">{{ $search ? 'Data tidak ditemukan.' : 'Belum ada data.' }}</p>
                                    @if($search)
                                        <a href="{{ route('master.kegiatan.index') }}" class="empty-link">Reset pencarian</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
        
        {{-- 13. Gunakan .table-footer --}}
        @if ($kegiatan->hasPages())
        <div class="table-footer">
            <div class="footer-info">
                Menampilkan {{ $kegiatan->firstItem() ?? 0 }} - {{ $kegiatan->lastItem() ?? 0 }} dari {{ $kegiatan->total() }} data
            </div>
            <div class="footer-pagination">
                {{ $kegiatan->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ================================================= --}}
{{-- ==              MODAL SECTIONS                 == --}}
{{-- ================================================= --}}

{{-- MODAL TAMBAH --}}
<div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered"> {{-- Center modal --}}
        <form action="{{ route('master.kegiatan.store') }}" method="POST">
            @csrf
             {{-- 14. Gunakan .modern-modal --}}
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <div class="modal-header-content">
                        <h5 class="modal-title">Tambah Kegiatan Baru</h5>
                        <p class="modal-subtitle">Isi detail kegiatan di bawah</p>
                    </div>
                    <button type="button" class="modal-close" data-bs-dismiss="modal"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                </div>
                <div class="modal-body">
                    {{-- 15. Gunakan .form-group, .form-label, .form-input, .form-textarea --}}
                    <div class="form-group">
                        <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="required">*</span></label>
                        <input type="text" class="form-input @error('nama_kegiatan') is-invalid @enderror" id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" required>
                        @error('nama_kegiatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-input @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea> {{-- Ganti ke .form-input (sama stylenya) --}}
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT --}}
<div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="_form" value="editForm"> {{-- Untuk fallback error --}}
            <input type="hidden" name="edit_id_fallback" id="edit_id_fallback" value="{{ session('edit_id') ?? '' }}">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <div class="modal-header-content">
                        <h5 class="modal-title">Edit Kegiatan</h5>
                        <p class="modal-subtitle">Perbarui detail kegiatan</p>
                    </div>
                    <button type="button" class="modal-close" data-bs-dismiss="modal"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan <span class="required">*</span></label>
                        <input type="text" class="form-input @error('nama_kegiatan', 'edit_error') is-invalid @enderror" id="edit_nama_kegiatan" name="nama_kegiatan" required>
                        @error('nama_kegiatan', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-input @error('deskripsi', 'edit_error') is-invalid @enderror" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                        @error('deskripsi', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary">
                         <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- MODAL HAPUS --}}
<div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            {{-- 16. Gunakan .modern-modal dan .modal-header-danger --}}
            <div class="modal-content modern-modal">
                <div class="modal-header modal-header-danger">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="modal-close modal-close-white" data-bs-dismiss="modal"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                </div>
                <div class="modal-body">
                    <div class="delete-icon"> <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> </div>
                    <p class="delete-text" id="deleteModalBody">Apakah Anda yakin ingin menghapus data ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn-danger" id="confirmDeleteButton">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        Ya, Hapus
                    </button>
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
