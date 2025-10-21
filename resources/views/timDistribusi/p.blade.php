@extends('layouts.app')

@section('title', 'Distribusi Tahunan')
@section('header-title', 'List Target Kegiatan Tahunan Tim Distribusi')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-light py-3">
            <h4 class="card-title mb-0">LIST TARGET KEGIATAN TAHUNAN</h4>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Baru
                    </button>
                    <button type="button" class="btn btn-danger" id="bulkDeleteBtn" data-bs-toggle="modal" data-bs-target="#deleteDataModal" disabled>
                        <i class="bi bi-trash me-1"></i> Hapus Terpilih
                    </button>
                </div>

                <div class="d-flex flex-wrap justify-content-end align-items-center gap-2 col-12 col-md-6 mt-2 mt-md-0">
                    <div class="d-flex align-items-center">
                        <label for="perPageSelect" class="form-label me-2 mb-0 small text-nowrap">Tampilkan:</label>
                        <select class="form-select form-select-sm" id="perPageSelect" style="width: auto;">
                            @php $options = [10, 20, 50, 100, 'all']; @endphp
                            @foreach($options as $option)
                                <option value="{{ $option }}" {{ (request('per_page', 20) == $option) ? 'selected' : '' }}>
                                    {{ $option == 'all' ? 'Semua' : $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-grow-1" style="min-width: 200px;">
                        <form action="{{ route('tim-distribusi.tahunan.index') }}" method="GET" class="d-flex">
                             @if(request('kegiatan_id'))
                                <input type="hidden" name="kegiatan_id" value="{{ request('kegiatan_id') }}">
                            @endif
                            <input type="text" class="form-control me-1" name="search" value="{{ request('search') }}" placeholder="Cari...">
                            <button class="btn btn-outline-primary" type="submit">
                                <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>


            <ul class="nav nav-pills mb-3 d-flex flex-wrap gap-2">
                <li class="nav-item">
                    <a class="nav-link {{ !request('kegiatan_id') ? 'active' : '' }}" href="{{ route('tim-distribusi.tahunan.index') }}">Semua Data</a>
                </li>
                @foreach($masterKegiatanList as $master)
                    <li class="nav-item">
                        <a class="nav-link {{ request('kegiatan_id') == $master->id_master_kegiatan ? 'active' : '' }}"
                           href="{{ route('tim-distribusi.tahunan.index', ['kegiatan_id' => $master->id_master_kegiatan]) }}">

                            <span class="badge bg-secondary rounded-pill">{{ $master->distribusi_tahunan_count }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <form id="bulkDeleteForm" action="{{ route('tim-distribusi.tahunan.bulkDelete') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 1%;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                                <th>Nama Kegiatan</th>
                                <th>BS/Responden</th>
                                <th>Pencacah</th>
                                <th>Pengawas</th>
                                <th>Target Selesai</th>
                                <th>Progress</th>
                                <th>Tgl Kumpul</th>
                                <th style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listData as $item)
                            <tr>
                                <td class="text-center"><input type="checkbox" class="form-check-input row-checkbox" name="ids[]" value="{{ $item->id_distribusi }}"></td>
                                <td>{{ $item->nama_kegiatan }}</td>
                                <td>{{ $item->BS_Responden }}</td>
                                <td>{{ $item->pencacah }}</td>
                                <td>{{ $item->pengawas }}</td>
                                <td class="text-center">{{ $item->target_penyelesaian ? \Carbon\Carbon::parse($item->target_penyelesaian)->format('d/m/Y') : '-' }}</td>
                                <td class="text-center"><span class="badge {{ $item->flag_progress == 'Selesai' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $item->flag_progress }}</span></td>
                                <td class="text-center">{{ $item->tanggal_pengumpulan ? \Carbon\Carbon::parse($item->tanggal_pengumpulan)->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-warning" title="Edit" onclick="editData({{ $item->id_distribusi }})">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" title="Hapus" onclick="deleteData({{ $item->id_distribusi }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-3">Tidak ada data ditemukan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </form>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Menampilkan {{ $listData->firstItem() ?? 0 }} - {{ $listData->lastItem() ?? 0 }} dari {{ $listData->total() }} data
            </div>
            <div>
                {{ $listData->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Data -->
<div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('tim-distribusi.tahunan.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Data Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="id_master_kegiatan" class="form-label">Jenis Kegiatan <span class="text-danger">*</span></label>
                        <select class="form-select @error('id_master_kegiatan') is-invalid @enderror" id="id_master_kegiatan" name="id_master_kegiatan" required>
                            <option value="">-- Pilih Jenis Kegiatan --</option>
                            @foreach ($masterKegiatanList as $master)
                                <option value="{{ $master->id_master_kegiatan }}" data-nama="{{ $master->nama_kegiatan }}" {{ old('id_master_kegiatan') == $master->id_master_kegiatan ? 'selected' : '' }}>
                                    {{ $master->nama_kegiatan }} ({{ Str::limit($master->deskripsi, 30) }})
                                </option>
                            @endforeach
                        </select>
                        @error('id_master_kegiatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                         <label for="nama_kegiatan" class="form-label">Nama Kegiatan Spesifik <span class="text-danger">*</span></label>
                         <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" placeholder="Contoh: IBS-2024" required>
                         @error('nama_kegiatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="BS_Responden" class="form-label">BS/Responden <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="BS_Responden" name="BS_Responden" required>
                    </div>
                    <div class="mb-3 autocomplete-container">
                        <label for="pencacah" class="form-label">Pencacah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pencacah" name="pencacah" required autocomplete="off">
                        <div class="autocomplete-suggestions" id="pencacah-suggestions"></div>
                    </div>
                    <div class="mb-3 autocomplete-container">
                        <label for="pengawas" class="form-label">Pengawas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pengawas" name="pengawas" required autocomplete="off">
                        <div class="autocomplete-suggestions" id="pengawas-suggestions"></div>
                    </div>
                    <div class="mb-3">
                        <label for="target_penyelesaian" class="form-label">Target Penyelesaian <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="target_penyelesaian" name="target_penyelesaian" required>
                    </div>
                    <div class="mb-3">
                        <label for="flag_progress" class="form-label">Progress <span class="text-danger">*</span></label>
                        <select class="form-select" id="flag_progress" name="flag_progress" required>
                            <option value="Belum Selesai">Belum Selesai</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                        <input type="date" class="form-control" id="tanggal_pengumpulan" name="tanggal_pengumpulan">
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

<!-- Modal Edit Data -->
<div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_id_master_kegiatan" class="form-label">Jenis Kegiatan <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_id_master_kegiatan" name="id_master_kegiatan" required>
                            <option value="">-- Pilih Jenis Kegiatan --</option>
                            @foreach ($masterKegiatanList as $master)
                                <option value="{{ $master->id_master_kegiatan }}" data-nama="{{ $master->nama_kegiatan }}">
                                    {{ $master->nama_kegiatan }} ({{ Str::limit($master->deskripsi, 30) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                         <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan Spesifik <span class="text-danger">*</span></label>
                         <input type="text" class="form-control" id="edit_nama_kegiatan" name="nama_kegiatan" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_BS_Responden" class="form-label">BS/Responden <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_BS_Responden" name="BS_Responden" required>
                    </div>
                    <div class="mb-3 autocomplete-container">
                        <label for="edit_pencacah" class="form-label">Pencacah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_pencacah" name="pencacah" required autocomplete="off">
                        <div class="autocomplete-suggestions" id="edit-pencacah-suggestions"></div>
                    </div>
                    <div class="mb-3 autocomplete-container">
                        <label for="edit_pengawas" class="form-label">Pengawas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_pengawas" name="pengawas" required autocomplete="off">
                        <div class="autocomplete-suggestions" id="edit-pengawas-suggestions"></div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_target_penyelesaian" class="form-label">Target Penyelesaian <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="edit_target_penyelesaian" name="target_penyelesaian" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_flag_progress" class="form-label">Progress <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_flag_progress" name="flag_progress" required>
                            <option value="Belum Selesai">Belum Selesai</option>
                            <option value="Selesai">Selesai</option>
                        </select>
                    </div>
                     <div class="mb-3">
                        <label for="edit_tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                        <input type="date" class="form-control" id="edit_tanggal_pengumpulan" name="tanggal_pengumpulan">
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

{{-- Modal Hapus --}}
<div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
    {{-- Form di-set oleh JS --}}
</div>
@endsection

@push('scripts')
<script>
    
    // --- FUNGSI UNTUK MENGISI NAMA SPESIFIK OTOMATIS ---
    function setupNamaKegiatanAutocomplete(selectId, targetId) {
        const selectElement = document.getElementById(selectId);
        if (!selectElement) return;
        const targetInput = document.getElementById(targetId);
        
        selectElement.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const namaMaster = selectedOption.getAttribute('data-nama');
            const currentYear = new Date().getFullYear();
            
            if (namaMaster && !targetInput.value.startsWith(namaMaster)) {
                 targetInput.value = `${namaMaster}-${currentYear}`;
            } else if (!namaMaster) {
                 targetInput.value = '';
            }
        });
    }
    
    // --- FUNGSI UNTUK EDIT DATA ---
    function editData(id) {
        const editModal = new bootstrap.Modal(document.getElementById('editDataModal'));
        const editForm = document.getElementById('editForm');
        editForm.action = `/tim-distribusi/tahunan/${id}`;

        fetch(`/tim-distribusi/tahunan/${id}/edit`)
            .then(response => {
                if (!response.ok) throw new Error('Data tidak ditemukan');
                return response.json();
            })
            .then(data => {
                document.getElementById('edit_id_master_kegiatan').value = data.id_master_kegiatan;
                document.getElementById('edit_nama_kegiatan').value = data.nama_kegiatan;
                document.getElementById('edit_BS_Responden').value = data.BS_Responden;
                document.getElementById('edit_pencacah').value = data.pencacah;
                document.getElementById('edit_pengawas').value = data.pengawas;
                document.getElementById('edit_target_penyelesaian').value = data.target_penyelesaian;
                document.getElementById('edit_flag_progress').value = data.flag_progress;
                document.getElementById('edit_tanggal_pengumpulan').value = data.tanggal_pengumpulan;
                editModal.show();
            })
            .catch(error => alert('Tidak dapat memuat data untuk diedit.'));
    }
    
    // --- FUNGSI UNTUK HAPUS DATA ---
    function deleteData(id) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/tim-distribusi/tahunan/${id}`;
        deleteForm.querySelector('input[name="_method"]').value = 'DELETE';
        deleteForm.querySelectorAll('input[name="ids[]"]').forEach(input => input.remove());
    }

    document.addEventListener('DOMContentLoaded', function() {
        setupNamaKegiatanAutocomplete('id_master_kegiatan', 'nama_kegiatan');
        setupNamaKegiatanAutocomplete('edit_id_master_kegiatan', 'edit_nama_kegiatan');
        
        // Fungsi untuk "Select All" Checkbox
        const selectAll = document.getElementById('selectAll');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
            });
        }
        
        // Event listener untuk tombol Bulk Delete
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', function() {
                const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);

                if (selectedIds.length === 0) {
                    alert('Pilih setidaknya satu data untuk dihapus.');
                    return;
                }

                const deleteModal = new bootstrap.Modal(document.getElementById('deleteDataModal'));
                const deleteForm = document.getElementById('deleteForm');
                
                deleteForm.action = '{{ route("tim-distribusi.tahunan.bulkDelete") }}';
                deleteForm.querySelector('input[name="_method"]').value = 'POST'; 
                deleteForm.querySelectorAll('input[name="ids[]"]').forEach(input => input.remove());
                
                selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    deleteForm.appendChild(input);
                });
                deleteModal.show();
            });
        }

        // Enable/disable bulk delete button
        function updateBulkDeleteState() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            bulkDeleteBtn.disabled = checkedCount === 0;
        }

        document.querySelectorAll('.row-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBulkDeleteState);
        });

        // Jika select all diubah, update juga
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
                updateBulkDeleteState();
            });
        }

        // Inisialisasi awal
        updateBulkDeleteState();

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
    });
</script>
@endpush