@extends('layouts.app')

@section('title', 'Master Petugas')
@section('header-title', 'Master Petugas')

@section('content')
<div class="container-fluid px-4 py-4">
    
    {{-- Page Header --}}
    <div class="page-header mb-4">
        <div class="header-content">
            <h2 class="page-title">Master Petugas</h2>
            <p class="page-subtitle">Kelola data petugas dan mitra</p>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="data-card">
        
        {{-- Toolbar --}}
        <div class="toolbar">
            <div class="toolbar-left">
                <button type="button" class="btn-action btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    Tambah Baru
                </button>
                <a href="{{ route('master.petugas.export') }}" class="btn-action btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Ekspor CSV
                </a>
                <button type="button" class="btn-action btn-danger" id="bulkDeleteBtn" data-bs-toggle="modal" data-bs-target="#deleteDataModal" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Hapus Terpilih
                </button>
            </div>

            <div class="toolbar-right">
                <div class="filter-group">
                    <label class="filter-label">Tampilkan:</label>
                    <select class="filter-select" id="perPageSelect">
                        @php $options = [10, 15, 25, 50, 100, 'all']; @endphp
                        @foreach($options as $option)
                            <option value="{{ $option }}" {{ (request('per_page', 15) == $option) ? 'selected' : '' }}>
                                {{ $option == 'all' ? 'Semua' : $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <form action="{{ route('master.petugas.index') }}" method="GET" class="search-form">
                    <input type="text" class="search-input" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama, NIK, atau kategori...">
                    <button class="search-btn" type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Alert --}}
        @if(session('success'))
            <div class="alert-success">
                <div class="alert-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" data-bs-dismiss="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Table --}}
        <form id="bulkDeleteForm" action="{{ route('master.petugas.bulkDelete') }}" method="POST">
            @csrf
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="th-checkbox">
                                <input type="checkbox" class="table-checkbox" id="selectAll">
                            </th>
                            <th>Nama Petugas</th>
                            <th>Kategori</th>
                            <th>NIK</th>
                            <th>No HP</th>
                            <th>Posisi</th>
                            <th class="th-action">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($petugas as $p)
                            <tr>
                                <td class="td-checkbox">
                                    <input type="checkbox" class="table-checkbox row-checkbox" name="ids[]" value="{{ $p->id_petugas }}">
                                </td>
                                <td>
                                    <div class="user-name">{{ $p->nama_petugas }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $p->kategori == 'Mitra' ? 'blue' : 'purple' }}">
                                        {{ $p->kategori }}
                                    </span>
                                </td>
                                <td class="text-secondary">{{ $p->nik }}</td>
                                <td class="text-secondary">{{ $p->no_hp }}</td>
                                <td class="text-secondary">{{ $p->posisi }}</td>
                                <td class="td-action">
                                    <div class="action-buttons">
                                        <a href="{{ route('master.petugas.show', $p) }}" class="btn-icon btn-icon-view" title="Lihat">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
                                        <button type="button" class="btn-icon btn-icon-edit" title="Edit" data-bs-toggle="modal" data-bs-target="#editDataModal" onclick="editData({{ $p->id_petugas }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn-icon btn-icon-delete" title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteDataModal" onclick="deleteData({{ $p->id_petugas }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <div class="empty-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <path d="m21 21-4.35-4.35"></path>
                                        </svg>
                                    </div>
                                    <p class="empty-text">Tidak ada data ditemukan</p>
                                    @if($search ?? false)
                                        <a href="{{ route('master.petugas.index') }}" class="empty-link">Reset pencarian</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        {{-- Footer --}}
        <div class="table-footer">
            <div class="footer-info">
                Menampilkan {{ $petugas->firstItem() ?? 0 }} - {{ $petugas->lastItem() ?? 0 }} dari {{ $petugas->total() }} data
            </div>
            <div class="footer-pagination">
                {{ $petugas->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('master.petugas.store') }}" method="POST">
            @csrf
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Petugas Baru</h5>
                    <button type="button" class="modal-close" data-bs-dismiss="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Petugas <span class="required">*</span></label>
                            <input type="text" class="form-input @error('nama_petugas') is-invalid @enderror" name="nama_petugas" value="{{ old('nama_petugas') }}" required>
                            @error('nama_petugas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select class="form-select @error('kategori') is-invalid @enderror" name="kategori">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Mitra" {{ old('kategori') == 'Mitra' ? 'selected' : '' }}>Mitra</option>
                                <option value="Organik BPS" {{ old('kategori') == 'Organik BPS' ? 'selected' : '' }}>Organik BPS</option>
                            </select>
                            @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK</label>
                            <input type="text" class="form-input @error('nik') is-invalid @enderror" name="nik" value="{{ old('nik') }}">
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No HP</label>
                            <input type="text" class="form-input @error('no_hp') is-invalid @enderror" name="no_hp" value="{{ old('no_hp') }}" placeholder="+62 8xx...">
                            @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Posisi</label>
                            <input type="text" class="form-input @error('posisi') is-invalid @enderror" name="posisi" value="{{ old('posisi') }}">
                            @error('posisi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-input @error('alamat') is-invalid @enderror" name="alamat" rows="2">{{ old('alamat') }}</textarea>
                            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pendidikan Terakhir</label>
                            <input type="text" class="form-input @error('pendidikan') is-invalid @enderror" name="pendidikan" value="{{ old('pendidikan') }}">
                            @error('pendidikan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-input @error('tgl_lahir') is-invalid @enderror" name="tgl_lahir" value="{{ old('tgl_lahir') }}">
                            @error('tgl_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" class="form-input @error('kecamatan') is-invalid @enderror" name="kecamatan" value="{{ old('kecamatan') }}">
                            @error('kecamatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" class="form-input @error('pekerjaan') is-invalid @enderror" name="pekerjaan" value="{{ old('pekerjaan') }}">
                            @error('pekerjaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Petugas</h5>
                    <button type="button" class="modal-close" data-bs-dismiss="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Petugas <span class="required">*</span></label>
                            <input type="text" class="form-input @error('nama_petugas', 'edit_error') is-invalid @enderror" id="edit_nama_petugas" name="nama_petugas" required>
                            @error('nama_petugas', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kategori</label>
                            <select class="form-select @error('kategori', 'edit_error') is-invalid @enderror" id="edit_kategori" name="kategori">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Mitra">Mitra</option>
                                <option value="Organik BPS">Organik BPS</option>
                            </select>
                            @error('kategori', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIK</label>
                            <input type="text" class="form-input @error('nik', 'edit_error') is-invalid @enderror" id="edit_nik" name="nik">
                            @error('nik', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No HP</label>
                            <input type="text" class="form-input @error('no_hp', 'edit_error') is-invalid @enderror" id="edit_no_hp" name="no_hp">
                            @error('no_hp', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-input @error('email', 'edit_error') is-invalid @enderror" id="edit_email" name="email">
                            @error('email', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Posisi</label>
                            <input type="text" class="form-input @error('posisi', 'edit_error') is-invalid @enderror" id="edit_posisi" name="posisi">
                            @error('posisi', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-input @error('alamat', 'edit_error') is-invalid @enderror" id="edit_alamat" name="alamat" rows="2"></textarea>
                            @error('alamat', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pendidikan Terakhir</label>
                            <input type="text" class="form-input @error('pendidikan', 'edit_error') is-invalid @enderror" id="edit_pendidikan" name="pendidikan">
                            @error('pendidikan', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-input @error('tgl_lahir', 'edit_error') is-invalid @enderror" id="edit_tgl_lahir" name="tgl_lahir">
                            @error('tgl_lahir', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" class="form-input @error('kecamatan', 'edit_error') is-invalid @enderror" id="edit_kecamatan" name="kecamatan">
                            @error('kecamatan', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pekerjaan</label>
                            <input type="text" class="form-input @error('pekerjaan', 'edit_error') is-invalid @enderror" id="edit_pekerjaan" name="pekerjaan">
                            @error('pekerjaan', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Delete --}}
<div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content modern-modal">
                <div class="modal-header modal-header-danger">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="modal-close modal-close-white" data-bs-dismiss="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="delete-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <p class="delete-text" id="deleteModalBody">Apakah Anda yakin ingin menghapus data ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn-danger" id="confirmDeleteButton">Ya, Hapus</button>
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
        editForm.action = `/master-petugas/${id}`;

        fetch(`/master-petugas/${id}/edit`)
            .then(response => {
                if (!response.ok) throw new Error('Data petugas tidak ditemukan.');
                return response.json();
            })
            .then(data => {
                document.getElementById('edit_nama_petugas').value = data.nama_petugas || '';
                document.getElementById('edit_kategori').value = data.kategori || '';
                document.getElementById('edit_nik').value = data.nik || '';
                document.getElementById('edit_alamat').value = data.alamat || '';
                document.getElementById('edit_no_hp').value = data.no_hp || '';
                document.getElementById('edit_posisi').value = data.posisi || '';
                document.getElementById('edit_email').value = data.email || '';
                document.getElementById('edit_pendidikan').value = data.pendidikan || '';
                document.getElementById('edit_tgl_lahir').value = data.tgl_lahir_formatted || '';
                document.getElementById('edit_kecamatan').value = data.kecamatan || '';
                document.getElementById('edit_pekerjaan').value = data.pekerjaan || '';

                editForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                editForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            })
            .catch(error => {
                console.error('Error fetching data for edit:', error);
                alert('Tidak dapat memuat data untuk diedit. Silakan coba lagi.');
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editDataModal'));
                if (editModal) editModal.hide();
            });
    }

    function deleteData(id) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/master-petugas/${id}`;
        document.getElementById('deleteModalBody').innerText = 'Apakah Anda yakin ingin menghapus data petugas ini? Tindakan ini tidak dapat dibatalkan.';
        document.getElementById('confirmDeleteButton').onclick = function() {
            deleteForm.submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');
        const confirmDeleteButton = document.getElementById('confirmDeleteButton');
        const perPageSelect = document.getElementById('perPageSelect');

        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                const selectedValue = this.value;
                const currentUrl = new URL(window.location.href);
                const params = currentUrl.searchParams;
                params.set('per_page', selectedValue);
                params.set('page', 1);
                window.location.href = currentUrl.pathname + '?' + params.toString();
            });
        }

        function updateBulkDeleteButtonState() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            bulkDeleteBtn.disabled = checkedCount === 0;
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                rowCheckboxes.forEach(cb => cb.checked = this.checked);
                updateBulkDeleteButtonState();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkDeleteButtonState);
        });
        updateBulkDeleteButtonState();

        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', function() {
                const count = document.querySelectorAll('.row-checkbox:checked').length;
                document.getElementById('deleteModalBody').innerText = `Apakah Anda yakin ingin menghapus ${count} data petugas yang dipilih? Tindakan ini tidak dapat dibatalkan.`;
                confirmDeleteButton.onclick = function() {
                    bulkDeleteForm.submit();
                }
            });
        }

        @if (session('error_modal') == 'tambahDataModal' && $errors->any())
            const tambahModalEl = document.getElementById('tambahDataModal');
            if (tambahModalEl) {
                const tambahModal = new bootstrap.Modal(tambahModalEl);
                tambahModal.show();
            }
        @endif

        @if (session('error_modal') == 'editDataModal' && $errors->any() && session('edit_id'))
            const editModalEl = document.getElementById('editDataModal');
            const editId = {{ session('edit_id') }};
            if (editModalEl && editId) {
                const editModal = new bootstrap.Modal(editModalEl);
                editData(editId); 
                setTimeout(() => {
                    @foreach ($errors->getBag('edit_error')->keys() as $field)
                        const fieldElement = document.getElementById('edit_{{ $field }}');
                        if (fieldElement) {
                            fieldElement.classList.add('is-invalid');
                            const errorElement = fieldElement.closest('.mb-3').querySelector('.invalid-feedback');
                            if (errorElement) {
                                errorElement.textContent = '{{ $errors->getBag("edit_error")->first($field) }}';
                            }
                        }
                    @endforeach
                }, 500);
                editModal.show();
            }
        @endif

        const autoHideAlerts = document.querySelectorAll('.alert-success');
        autoHideAlerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>
@endpush

@push('styles')
<style>
/* Page Header */
.page-header {
    margin-bottom: 32px;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.page-subtitle {
    font-size: 0.9375rem;
    color: #6b7280;
    margin: 0;
}

/* Data Card */
.data-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

/* Toolbar */
.toolbar {
    padding: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    border-bottom: 1px solid #f3f4f6;
}

.toolbar-left {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.toolbar-right {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

/* Action Buttons */
.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border: none;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #ffffff;
}

.btn-primary:hover {
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
}

.btn-success:hover {
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
}

.btn-danger:hover {
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.btn-danger:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.btn-secondary {
    background: #f3f4f6;
    color: #6b7280;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

/* Filter Group */
.filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-label {
    font-size: 0.875rem;
    color: #6b7280;
    white-space: nowrap;
    margin: 0;
}

.filter-select {
    padding: 8px 32px 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #1f2937;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Search Form */
.search-form {
    display: flex;
    gap: 8px;
    min-width: 280px;
}

.search-input {
    flex: 1;
    padding: 10px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-btn {
    padding: 10px 16px;
    background: #667eea;
    border: none;
    border-radius: 10px;
    color: #ffffff;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.search-btn:hover {
    background: #5568d3;
    transform: translateY(-2px);
}

/* Alert */
.alert-success {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 20px 24px;
    padding: 16px;
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.3);
    border-radius: 12px;
    color: #059669;
    font-size: 0.875rem;
}

.alert-icon {
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.alert-close {
    margin-left: auto;
    padding: 4px;
    background: none;
    border: none;
    color: #059669;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.alert-close:hover {
    background: rgba(16, 185, 129, 0.15);
}

/* Table */
.table-wrapper {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead tr {
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.data-table th {
    padding: 16px 20px;
    text-align: left;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.data-table tbody tr {
    border-bottom: 1px solid #f3f4f6;
    transition: all 0.2s ease;
}

.data-table tbody tr:hover {
    background: #f9fafb;
}

.data-table td {
    padding: 16px 20px;
    font-size: 0.875rem;
    color: #1f2937;
}

.th-checkbox,
.td-checkbox {
    width: 48px;
    text-align: center;
}

.th-action,
.td-action {
    width: 140px;
    text-align: center;
}

.table-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #667eea;
}

/* User Name */
.user-name {
    font-weight: 500;
    color: #1f2937;
}

.text-secondary {
    color: #6b7280;
}

/* Badge */
.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-blue {
    background: rgba(59, 130, 246, 0.1);
    color: #2563eb;
}

.badge-purple {
    background: rgba(139, 92, 246, 0.1);
    color: #7c3aed;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-icon:hover {
    transform: translateY(-2px);
}

.btn-icon-view {
    background: rgba(107, 114, 128, 0.1);
    color: #4b5563;
}

.btn-icon-view:hover {
    background: rgba(107, 114, 128, 0.2);
}

.btn-icon-edit {
    background: rgba(245, 158, 11, 0.1);
    color: #d97706;
}

.btn-icon-edit:hover {
    background: rgba(245, 158, 11, 0.2);
}

.btn-icon-delete {
    background: rgba(239, 68, 68, 0.1);
    color: #dc2626;
}

.btn-icon-delete:hover {
    background: rgba(239, 68, 68, 0.2);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px !important;
}

.empty-icon {
    margin-bottom: 16px;
    color: #d1d5db;
}

.empty-text {
    font-size: 1rem;
    color: #6b7280;
    margin-bottom: 12px;
}

.empty-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}

.empty-link:hover {
    text-decoration: underline;
}

/* Table Footer */
.table-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: #f9fafb;
    border-top: 1px solid #e5e7eb;
    flex-wrap: wrap;
    gap: 16px;
}

.footer-info {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Modal */
.modern-modal {
    border-radius: 16px;
    border: none;
    overflow: hidden;
}

.modal-header {
    padding: 24px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    border-bottom: none;
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.modal-header-danger .modal-title {
    color: #ffffff;
}

.modal-close {
    padding: 8px;
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    background: #e5e7eb;
}

.modal-close-white {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}

.modal-close-white:hover {
    background: rgba(255, 255, 255, 0.3);
}

.modal-body {
    padding: 24px;
}

.modal-footer {
    padding: 20px 24px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

/* Form Inputs */
.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 8px;
}

.required {
    color: #ef4444;
}

.form-input,
.form-select {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    color: #1f2937;
    transition: all 0.2s ease;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input.is-invalid,
.form-select.is-invalid {
    border-color: #ef4444;
}

.invalid-feedback {
    display: block;
    margin-top: 6px;
    font-size: 0.8125rem;
    color: #ef4444;
}

/* Delete Modal */
.delete-icon {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
    color: #ef4444;
}

.delete-text {
    text-align: center;
    font-size: 0.9375rem;
    color: #6b7280;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .toolbar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .toolbar-left,
    .toolbar-right {
        width: 100%;
        justify-content: stretch;
    }
    
    .toolbar-right {
        flex-direction: column;
    }
    
    .filter-group,
    .search-form {
        width: 100%;
    }
    
    .btn-action {
        flex: 1;
        justify-content: center;
    }
    
    .table-footer {
        flex-direction: column;
        text-align: center;
    }
}
</style>
@endpush