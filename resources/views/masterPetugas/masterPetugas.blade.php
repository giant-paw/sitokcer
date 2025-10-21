@extends('layouts.app') {{-- Sesuaikan dengan layout utama Anda --}}

@section('title', 'Master Petugas')
@section('header-title', 'Master Petugas') {{-- Sesuaikan jika perlu --}}

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm"> {{-- Tambahkan shadow --}}
        <div class="card-header bg-light py-3"> {{-- Atur padding header --}}
             <h4 class="card-title mb-0">Daftar Petugas</h4>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2"> {{-- Tambahkan gap --}}
                {{-- Tombol Aksi Kiri --}}
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Baru {{-- Tambahkan margin icon --}}
                    </button>
                    <a href="{{ route('master.petugas.export') }}" class="btn btn-success">
                        <i class="bi bi-download me-1"></i> Ekspor CSV
                    </a>
                    <button type="button" class="btn btn-danger" id="bulkDeleteBtn" data-bs-toggle="modal" data-bs-target="#deleteDataModal" disabled>
                        <i class="bi bi-trash me-1"></i> Hapus Terpilih
                    </button>
                </div>
                
                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2 col-12 col-md-6 mt-2 mt-md-0">
                    
                    <div class="d-flex align-items-center">
                        <label for="perPageSelect" class="form-label me-2 mb-0 small text-nowrap">Tampilkan:</label>
                        <select class="form-select form-select-sm" id="perPageSelect" style="width: auto;">
                            @php $options = [10, 15, 25, 50, 100, 'all']; @endphp
                            @foreach($options as $option)
                                <option value="{{ $option }}" {{ (request('per_page', 15) == $option) ? 'selected' : '' }}>
                                    {{ $option == 'all' ? 'Semua' : $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-grow-1" style="min-width: 200px;">
                         <form action="{{ route('master.petugas.index') }}" method="GET" class="d-flex">
                            <input type="text" class="form-control me-1" name="search" value="{{ $search ?? '' }}" placeholder="Cari Nama/NIK/Kategori...">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            {{-- Tabel Data --}}
            <form id="bulkDeleteForm" action="{{ route('master.petugas.bulkDelete') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle"> {{-- align-middle --}}
                        <thead class="table-light text-center"> {{-- text-center --}}
                            <tr>
                                <th style="width: 1%;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                                <th>Nama Petugas</th>
                                <th>Kategori</th>
                                <th>NIK</th>
                                <th>Alamat</th>
                                <th>No HP</th>
                                <th>Posisi</th>
                                <th>Email</th>
                                <th>Pendidikan</th>
                                <th>Tanggal Lahir</th>
                                <th>Kecamatan</th>
                                <th>Pekerjaan</th>
                                <th style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($petugas as $p)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="{{ $p->id_petugas }}">
                                    </td>
                                    <td>{{ $p->nama_petugas }}</td>
                                    <td class="text-center">{{ $p->kategori }}</td>
                                    <td>{{ $p->nik }}</td>
                                    <td>{{ $p->alamat }}</td>         
                                    <td>{{ $p->no_hp }}</td>
                                    <td>{{ $p->posisi }}</td>
                                    <td>{{ $p->email }}</td>
                                    <td>{{ $p->pendidikan }}</td>     
                                    <td>{{ $p->tgl_lahir ? \Carbon\Carbon::parse($p->tgl_lahir)->format('d/m/Y') : '-' }}</td>  
                                    <td>{{ $p->kecamatan }}</td>      
                                    <td>{{ $p->pekerjaan }}</td>      
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-warning"
                                                    title="Edit"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editDataModal"
                                                    onclick="editData({{ $p->id_petugas }})">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger"
                                                    title="Hapus"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteDataModal"
                                                    onclick="deleteData({{ $p->id_petugas }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                         </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-3"> 
                                        <span class="text-muted">Tidak ada data ditemukan.</span>
                                        @if($search)
                                           <a href="{{ route('master.petugas.index') }}" class="ms-2">Reset pencarian</a>
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
                Menampilkan {{ $petugas->firstItem() ?? 0 }} - {{ $petugas->lastItem() ?? 0 }} dari {{ $petugas->total() }} data
            </div>
            <div>
                {{ $petugas->links() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true" data-bs-backdrop="static"> {{-- static backdrop --}}
    <div class="modal-dialog modal-lg">
        <form action="{{ route('master.petugas.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahDataModalLabel">Tambah Petugas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row gx-3">
                        <div class="col-md-6 mb-3">
                            <label for="nama_petugas" class="form-label">Nama Petugas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_petugas') is-invalid @enderror" id="nama_petugas" name="nama_petugas" value="{{ old('nama_petugas') }}" required>
                            @error('nama_petugas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="kategori" class="form-label">Kategori</label>
                            <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori">
                                <option value="" {{ old('kategori') == '' ? 'selected' : '' }}>-- Pilih Kategori --</option>
                                <option value="Mitra" {{ old('kategori') == 'Mitra' ? 'selected' : '' }}>Mitra</option>
                                <option value="Organik BPS" {{ old('kategori') == 'Organik BPS' ? 'selected' : '' }}>Organik BPS</option>
                            </select>
                            @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nik" class="form-label">NIK</label>
                            <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik') }}">
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="no_hp" class="form-label">No HP</label>
                            <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" placeholder="+62 8xx...">
                            @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="posisi" class="form-label">Posisi</label>
                            <input type="text" class="form-control @error('posisi') is-invalid @enderror" id="posisi" name="posisi" value="{{ old('posisi') }}">
                            @error('posisi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="2">{{ old('alamat') }}</textarea>
                            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pendidikan" class="form-label">Pendidikan Terakhir</label>
                            <input type="text" class="form-control @error('pendidikan') is-invalid @enderror" id="pendidikan" name="pendidikan" value="{{ old('pendidikan') }}">
                            @error('pendidikan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="tgl_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control @error('tgl_lahir') is-invalid @enderror" id="tgl_lahir" name="tgl_lahir" value="{{ old('tgl_lahir') }}">
                            @error('tgl_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="kecamatan" class="form-label">Kecamatan</label>
                            <input type="text" class="form-control @error('kecamatan') is-invalid @enderror" id="kecamatan" name="kecamatan" value="{{ old('kecamatan') }}">
                            @error('kecamatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pekerjaan" class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control @error('pekerjaan') is-invalid @enderror" id="pekerjaan" name="pekerjaan" value="{{ old('pekerjaan') }}">
                            @error('pekerjaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
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

<div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDataModalLabel">Edit Petugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                     <div class="row gx-3">
                         <div class="col-md-6 mb-3">
                            <label for="edit_nama_petugas" class="form-label">Nama Petugas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_petugas', 'edit_error') is-invalid @enderror" id="edit_nama_petugas" name="nama_petugas" required>
                            @error('nama_petugas', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_kategori" class="form-label">Kategori</label>
                            <select class="form-select @error('kategori', 'edit_error') is-invalid @enderror" id="edit_kategori" name="kategori">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Mitra">Mitra</option>
                                <option value="Organik BPS">Organik BPS</option>
                            </select>
                            @error('kategori', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_nik" class="form-label">NIK</label>
                            <input type="text" class="form-control @error('nik', 'edit_error') is-invalid @enderror" id="edit_nik" name="nik">
                            @error('nik', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="edit_no_hp" class="form-label">No HP</label>
                            <input type="text" class="form-control @error('no_hp', 'edit_error') is-invalid @enderror" id="edit_no_hp" name="no_hp">
                            @error('no_hp', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email', 'edit_error') is-invalid @enderror" id="edit_email" name="email">
                            @error('email', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_posisi" class="form-label">Posisi</label>
                            <input type="text" class="form-control @error('posisi', 'edit_error') is-invalid @enderror" id="edit_posisi" name="posisi">
                            @error('posisi', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="edit_alamat" class="form-label">Alamat</label>
                            <textarea class="form-control @error('alamat', 'edit_error') is-invalid @enderror" id="edit_alamat" name="alamat" rows="2"></textarea>
                            @error('alamat', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_pendidikan" class="form-label">Pendidikan Terakhir</label>
                            <input type="text" class="form-control @error('pendidikan', 'edit_error') is-invalid @enderror" id="edit_pendidikan" name="pendidikan">
                            @error('pendidikan', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="edit_tgl_lahir" class="form-label">Tanggal Lahir</label>
                            <input type="date" class="form-control @error('tgl_lahir', 'edit_error') is-invalid @enderror" id="edit_tgl_lahir" name="tgl_lahir">
                            @error('tgl_lahir', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                         <div class="col-md-6 mb-3">
                            <label for="edit_kecamatan" class="form-label">Kecamatan</label>
                            <input type="text" class="form-control @error('kecamatan', 'edit_error') is-invalid @enderror" id="edit_kecamatan" name="kecamatan">
                            @error('kecamatan', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_pekerjaan" class="form-label">Pekerjaan</label>
                            <input type="text" class="form-control @error('pekerjaan', 'edit_error') is-invalid @enderror" id="edit_pekerjaan" name="pekerjaan">
                            @error('pekerjaan', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
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

<div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header bg-danger text-white"> {{-- Header merah --}}
                    <h5 class="modal-title" id="deleteDataModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="deleteModalBody">
                    Apakah Anda yakin ingin menghapus data ini?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">Ya, Hapus</button> {{-- Teks lebih jelas --}}
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
                // Isi field di modal edit, gunakan || '' untuk fallback jika null
                document.getElementById('edit_nama_petugas').value = data.nama_petugas || '';
                document.getElementById('edit_kategori').value = data.kategori || '';
                document.getElementById('edit_nik').value = data.nik || '';
                document.getElementById('edit_alamat').value = data.alamat || '';
                document.getElementById('edit_no_hp').value = data.no_hp || '';
                document.getElementById('edit_posisi').value = data.posisi || '';
                document.getElementById('edit_email').value = data.email || '';
                document.getElementById('edit_pendidikan').value = data.pendidikan || '';
                document.getElementById('edit_tgl_lahir').value = data.tgl_lahir_formatted || ''; // YYYY-MM-DD
                document.getElementById('edit_kecamatan').value = data.kecamatan || '';
                document.getElementById('edit_pekerjaan').value = data.pekerjaan || '';

                // Hapus kelas error validasi sebelumnya
                editForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                editForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = ''); // Hapus pesan error lama
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
                 // Setelah modal terbuka, kita tandai field yang error
                 // Ini butuh sedikit delay agar modal sempat terender
                 setTimeout(() => {
                    @foreach ($errors->getBag('default')->keys() as $field)
                        const fieldElement = document.getElementById('edit_{{ $field }}');
                        if (fieldElement) {
                            fieldElement.classList.add('is-invalid');
                            // Cari div invalid-feedback terdekat dan isi pesannya
                            const errorElement = fieldElement.closest('.mb-3').querySelector('.invalid-feedback');
                            if (errorElement) {
                                errorElement.textContent = '{{ $errors->first($field) }}';
                            }
                        }
                    @endforeach
                 }, 500); // Delay 500ms
                 editModal.show();
            }
        @endif

        // Auto-hide alert setelah beberapa detik
        const autoHideAlerts = document.querySelectorAll('.alert-success, .alert-danger');
        autoHideAlerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000); // 5 detik
        });
    });
</script>
@endpush

@push('styles')
<style>
    .table-responsive {
        overflow-x: auto;
    }
    .table th {
        white-space: nowrap;
        vertical-align: middle;
    }
    .table td {
        vertical-align: middle;
    }
    /* Untuk membatasi lebar kolom alamat */
    .table td:nth-child(5) {  /* kolom alamat */
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@endpush