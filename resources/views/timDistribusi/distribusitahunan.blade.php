@extends('layouts.app')

@section('title', 'Distribusi Tahunan - Sitokcer')

@section('header-title', 'List Target Kegiatan Tahunan Tim Distribusi')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">LIST TARGET KEGIATAN TAHUNAN TIM DISTRIBUSI</h4>
            </div>
            <div class="card-body">
                <div class="mb-10">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal">
                        <i class="bi bi-plus-circle"></i> Tambah baru
                    </button>
                    <button type="button" class="btn btn-secondary"><i class="bi bi-upload"></i> Import</button>
                    <button type="button" class="btn btn-success"><i class="bi bi-download"></i> Ekspor hasil</button>
                    <button 
                        type="button" 
                        class="btn btn-danger"
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteDataModal" 
                        id="bulkDeleteBtn"><i class="bi bi-trash"></i> Hapus
                    </button>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Terjadi Kesalahan!</strong> Mohon periksa kembali isian form Anda.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <ul class="nav nav-pills mb-3 d-flex flex-wrap gap-8" >
                    <li class="nav-item"> 
                        <a class="nav-link {{ request('kegiatan') == '' ? 'active' : '' }}" href="{{ route('tim-distribusi.tahunan.index') }}">All data</a>
                    </li>
                    @foreach($kegiatanCounts as $kegiatan)
                        <li class="nav-item">
                            <a class="nav-link {{ request('kegiatan') == $kegiatan->nama_kegiatan ? 'active' : '' }}" href="{{ route('tim-distribusi.tahunan.index', ['kegiatan' => $kegiatan->nama_kegiatan]) }}">
                                {{ $kegiatan->nama_kegiatan }} <span class="badge bg-secondary">{{ $kegiatan->total }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <form action="{{ route('tim-distribusi.tahunan.index') }}" method="GET" class="mb-4">
                    <div class="row g-2 align-items-center">
                        @if(request('kegiatan'))
                            <input type="hidden" name="kegiatan" value="{{ request('kegiatan') }}">
                        @endif
                        <div class="col-md-9 col-12">
                            <input type="text" class="form-control" placeholder="Cari berdasarkan Responden, Pencacah, atau Pengawas..." name="search" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 col-12">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </form>


                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">
                                    <input type="checkbox row-checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th scope="col">Nama Kegiatan</th>
                                <th scope="col">Blok Sensus/Responden</th>
                                <th scope="col">Pencacah</th>
                                <th scope="col">Pengawas</th>
                                <th scope="col">Tanggal Target Penyelesaian</th>
                                <th scope="col">Flag Progress</th>
                                <th scope="col">Tanggal Pengumpulan</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listData as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $item->id_distribusi }}">
                                    </td>
                                    <td>{{ $item->nama_kegiatan }}</td>
                                    <td>{{ $item->BS_Responden }}</td>
                                    <td>{{ $item->pencacah }}</td>
                                    <td>{{ $item->pengawas }}</td>
                                    <td>{{ $item->target_penyelesaian }}</td>
                                    <td><span class="badge {{ $item->flag_progress == 'Selesai' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $item->flag_progress }}</span></td>
                                    <td>{{ $item->tanggal_pengumpulan }}</td>
                                    <td class="d-flex gap-2">
                                        <!-- Edit -->
                                        <button class="btn btn-sm btn-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editDataModal" 
                                                onclick="editData({{ $item->id_distribusi }})">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <!-- Hapus -->
                                        <button class="btn btn-sm btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteDataModal" 
                                                onclick="deleteData({{ $item->id_distribusi }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="text-muted">
                    Displaying {{ $listData->firstItem() ?? 0 }} - {{ $listData->lastItem() ?? 0 }} of {{ $listData->total() }}
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
                <h5 class="modal-title" id="tambahDataModalLabel">Tambah Data Baru</h5>
                
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                
            </div>
                <div class="modal-body">
                <div class="mb-3">
                    <label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
                    <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" required>
                </div>
                <div class="mb-3">
                    <label for="BS_Responden" class="form-label">Blok Sensus/Responden</label>
                    <input type="text" class="form-control" id="BS_Responden" name="BS_Responden" required>
                </div>

                <div class="mb-3 autocomplete-container">
                    <label for="pencacah" class="form-label">Pencacah</label>
                    <input type="text" class="form-control" id="pencacah" name="pencacah" required autocomplete="off"> 
                    <div class="autocomplete-suggestions" id="pencacah-suggestions"></div> 
                </div>

                <div class="mb-3 autocomplete-container">
                    <label for="pengawas" class="form-label">Pengawas</label>
                    <input type="text" class="form-control" id="pengawas" name="pengawas" required autocomplete="off">
                    <div class="autocomplete-suggestions" id="pengawas-suggestions"></div>
                </div>

                <div class="mb-3">
                    <label for="target_penyelesaian" class="form-label">Tanggal Target Penyelesaian</label>
                    <input type="date" class="form-control" id="target_penyelesaian" name="target_penyelesaian" placeholder="dd/mm/yyyy" required>
                </div>
                <div class="mb-3">
                    <label for="flag_progress" class="form-label">Flag Progress</label>
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

    <!-- Modal edit -->
    <div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT') 
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDataModalLabel">Edit Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan</label>
                            <input type="text" class="form-control" id="edit_nama_kegiatan" name="nama_kegiatan" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_BS_Responden" class="form-label">Blok Sensus/Responden</label>
                            <input type="text" class="form-control" id="edit_BS_Responden" name="BS_Responden" required>
                        </div>

                        <div class="mb-3 autocomplete-container"> 
                            <label for="edit_pencacah" class="form-label">Pencacah</label>
                            <input type="text" class="form-control" id="edit_pencacah" name="pencacah" required autocomplete="off">
                            <div class="autocomplete-suggestions" id="edit-pencacah-suggestions"></div>
                        </div>

                        <div class="mb-3 autocomplete-container">
                            <label for="edit_pengawas" class="form-label">Pengawas</label>
                            <input type="text" class="form-control" id="edit_pengawas" name="pengawas" required autocomplete="off">
                            <div class="autocomplete-suggestions" id="edit-pengawas-suggestions"></div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_target_penyelesaian" class="form-label">Tanggal Target Penyelesaian</label>
                            <input type="date" class="form-control" id="edit_target_penyelesaian" name="target_penyelesaian" placeholder="dd/mm/yyyy" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_flag_progress" class="form-label">Flag Progress</label>
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

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteDataModalLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Apakah Anda yakin ingin menghapus data ini?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // --- FUNGSI UNTUK AUTOCOMPLETE ---
    function initAutocomplete(inputId, suggestionsId, fieldName) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const suggestionsContainer = document.getElementById(suggestionsId);
        let debounceTimer;

        input.addEventListener('input', function() {
            const query = this.value;
            clearTimeout(debounceTimer);
            
            if (query.length < 2) {
                if(suggestionsContainer) suggestionsContainer.innerHTML = '';
                return;
            }

            debounceTimer = setTimeout(() => {
                const searchUrl = `{{ route("tim-distribusi.tahunan.searchPetugas") }}?field=${fieldName}&query=${query}`;
                
                fetch(searchUrl)
                    .then(response => response.json())
                    .then(data => {
                        suggestionsContainer.innerHTML = ''; 
                        data.forEach(item => {
                            const suggestionDiv = document.createElement('div');
                            suggestionDiv.textContent = item;
                            suggestionDiv.classList.add('autocomplete-suggestion-item');
                            suggestionDiv.addEventListener('click', function() {
                                input.value = item;
                                suggestionsContainer.innerHTML = '';
                            });
                            suggestionsContainer.appendChild(suggestionDiv);
                        });
                    })
                    .catch(error => console.error('Autocomplete error:', error));
            }, 10);
        });
        
        document.addEventListener('click', function(e) {
            if (e.target.id !== inputId) {
                if(suggestionsContainer) suggestionsContainer.innerHTML = '';
            }
        });
    }

    // --- FUNGSI UNTUK EDIT DATA ---
    function editData(id) {
        const getUrl = `/tim-distribusi/tahunan/${id}/edit`;
        const updateUrl = `/tim-distribusi/tahunan/${id}`;
        const editForm = document.getElementById('editForm');
        
        fetch(getUrl)
            .then(response => {
                if (!response.ok) throw new Error('Gagal mengambil data dari server.');
                return response.json();
            })
            .then(data => {
                document.getElementById('edit_nama_kegiatan').value = data.nama_kegiatan;
                document.getElementById('edit_BS_Responden').value = data.BS_Responden;
                document.getElementById('edit_pencacah').value = data.pencacah;
                document.getElementById('edit_pengawas').value = data.pengawas;
                document.getElementById('edit_target_penyelesaian').value = data.target_penyelesaian;
                document.getElementById('edit_flag_progress').value = data.flag_progress;
                document.getElementById('edit_tanggal_pengumpulan').value = data.tanggal_pengumpulan;
                editForm.action = updateUrl;
            })
            .catch(error => {
                console.error('Terjadi kesalahan:', error);
                alert('Tidak dapat memuat data untuk diedit. Silakan coba lagi.');
            });
    }

    // --- FUNGSI UNTUK HAPUS SATU DATA ---
    function deleteData(id) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/tim-distribusi/tahunan/${id}`;
        deleteForm.querySelector('input[name="_method"]').value = 'DELETE';
        deleteForm.querySelectorAll('input[name="ids[]"]').forEach(input => input.remove());
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Inisialisasi Autocomplete untuk semua 4 field
        initAutocomplete('pencacah', 'pencacah-suggestions', 'pencacah');
        initAutocomplete('pengawas', 'pengawas-suggestions', 'pengawas');
        initAutocomplete('edit_pencacah', 'edit-pencacah-suggestions', 'pencacah');
        initAutocomplete('edit_pengawas', 'edit-pengawas-suggestions', 'pengawas');

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
    });
</script>
@endpush

@push('styles')
<style>
    .autocomplete-container {
        position: relative;
    }
    .autocomplete-suggestions {
        position: absolute;
        border: 1px solid #ddd;
        border-top: none;
        background-color: #fff;
        z-index: 1056; 
        width: 100%;
        max-height: 150px;
        overflow-y: auto;
    }
    .autocomplete-suggestion-item {
        padding: 8px 12px;
        cursor: pointer;
        color: #0d6efd; 
        background-color: #f8f9fa;
        font-weight: 500;
    }
    .autocomplete-suggestion-item:hover {
        background-color: #ffe082; 
        color: #212529; 
    }
</style>
@endpush