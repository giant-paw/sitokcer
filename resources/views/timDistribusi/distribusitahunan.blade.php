@extends('layouts.app')

@section('title', 'Distribusi Tahunan')
@section('header-title', 'List Target Kegiatan Tahunan Tim Distribusi')

@push('styles')
<style>
    .autocomplete-container {
        position: relative;
    }
    .autocomplete-suggestions {
        position: absolute;
        border: 1px solid #d1d3e2;
        border-top: none;
        z-index: 1056; /* Di atas modal (1055) */
        width: 100%;
        background-color: #dcd5d5ff;
        max-height: 200px;
        overflow-y: auto;
    }
    .autocomplete-suggestion-item {
        padding: 8px 12px;
        cursor: pointer;
    }
    .autocomplete-suggestion-item:hover,
    .autocomplete-suggestion-item.active {
        background-color: #0d6efd;
        color: #fff;
    }
</style>
@endpush

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
                    <button type="button" class="btn btn-secondary">
                        <i class="bi bi-upload"></i> Import
                    </button>
                    <button type="button" class="btn btn-success">
                        <i class="bi bi-download"></i> Ekspor Hasil
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
                             @if(request('kegiatan'))
                                <input type="hidden" name="kegiatan" value="{{ request('kegiatan') }}">
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
                    {{-- Link "Semua Data" menghapus parameter 'kegiatan' --}}
                    <a class="nav-link {{ !request('kegiatan') ? 'active' : '' }}" href="{{ route('tim-distribusi.tahunan.index', request()->except('kegiatan')) }}">Semua Data</a>
                </li>
                @foreach($kegiatanCounts as $item) 
                    <li class="nav-item">
                        <a class="nav-link {{ request('kegiatan') == $item->nama_kegiatan ? 'active' : '' }}"
                           href="{{ route('tim-distribusi.tahunan.index', array_merge(request()->except('page'), ['kegiatan' => $item->nama_kegiatan])) }}">
                            {{ $item->nama_kegiatan }}
                            <span class="badge bg-secondary rounded-pill ms-2">{{ $item->total }}</span>
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
                                <td class="text-center">{{ $item->target_penyelesaian ? $item->target_penyelesaian->format('d/m/Y') : '-' }}</td>
                                <td class="text-center"><span class="badge {{ $item->flag_progress == 'Selesai' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $item->flag_progress }}</span></td>
                                <td class="text-center">{{ $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format('d/m/Y') : '-' }}</td>
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

<div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('tim-distribusi.tahunan.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"> <h5 class="modal-title">Tambah Data Baru</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div>
                <div class="modal-body">
                    
                    <div class="mb-3">
                         <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                         
                         <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                                id="nama_kegiatan" name="nama_kegiatan" 
                                value="{{ old('nama_kegiatan') }}" 
                                placeholder="Contoh: IBS-2024" 
                                required 
                                list="kegiatan-list"> @if($masterKegiatanList->isNotEmpty())
                            <datalist id="kegiatan-list">
                                @foreach($masterKegiatanList as $kegiatan)
                                    <option value="{{ $kegiatan->nama_kegiatan }}">
                                @endforeach
                            </datalist>
                         @endif

                         <div id="namaKegiatanHelp" class="form-text mt-1">
                            Ketik nama kegiatan. Gunakan format NAMA-Suffix (cth: IBS-2024).
                         </div>
                         
                         @error('nama_kegiatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3"> <label for="BS_Responden" class="form-label">BS/Responden <span class="text-danger">*</span></label> <input type="text" class="form-control @error('BS_Responden') is-invalid @enderror" id="BS_Responden" name="BS_Responden" value="{{ old('BS_Responden') }}" required> @error('BS_Responden')<div class="invalid-feedback">{{ $message }}</div>@enderror </div>
                    <div class="mb-3 autocomplete-container"> <label for="pencacah" class="form-label">Pencacah <span class="text-danger">*</span></label> <input type="text" class="form-control @error('pencacah') is-invalid @enderror" id="pencacah" name="pencacah" value="{{ old('pencacah') }}" required autocomplete="off"> <div class="autocomplete-suggestions" id="pencacah-suggestions"></div> @error('pencacah')<div class="invalid-feedback">{{ $message }}</div>@enderror </div>
                    <div class="mb-3 autocomplete-container"> <label for="pengawas" class="form-label">Pengawas <span class="text-danger">*</span></label> <input type="text" class="form-control @error('pengawas') is-invalid @enderror" id="pengawas" name="pengawas" value="{{ old('pengawas') }}" required autocomplete="off"> <div class="autocomplete-suggestions" id="pengawas-suggestions"></div> @error('pengawas')<div class="invalid-feedback">{{ $message }}</div>@enderror </div>
                    <div class="mb-3"> <label for="target_penyelesaian" class="form-label">Target Penyelesaian <span class="text-danger">*</span></label> <input type="date" class="form-control @error('target_penyelesaian') is-invalid @enderror" id="target_penyelesaian" name="target_penyelesaian" value="{{ old('target_penyelesaian') }}" required> @error('target_penyelesaian')<div class="invalid-feedback">{{ $message }}</div>@enderror </div>
                    <div class="mb-3"> <label for="flag_progress" class="form-label">Progress <span class="text-danger">*</span></label> <select class="form-select @error('flag_progress') is-invalid @enderror" id="flag_progress" name="flag_progress" required> <option value="Belum Selesai" {{ old('flag_progress') == 'Belum Selesai' ? 'selected' : '' }}>Belum Selesai</option> <option value="Selesai" {{ old('flag_progress') == 'Selesai' ? 'selected' : '' }}>Selesai</option> </select> @error('flag_progress')<div class="invalid-feedback">{{ $message }}</div>@enderror </div>
                    <div class="mb-3"> <label for="tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label> <input type="date" class="form-control @error('tanggal_pengumpulan') is-invalid @enderror" id="tanggal_pengumpulan" name="tanggal_pengumpulan" value="{{ old('tanggal_pengumpulan') }}"> @error('tanggal_pengumpulan')<div class="invalid-feedback">{{ $message }}</div>@enderror </div>
                </div>
                <div class="modal-footer"> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button> <button type="submit" class="btn btn-primary">Simpan</button> </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('tim-distribusi.tahunan.store') }}" method="POST" id="tambahForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header"> <h5 class="modal-title">Tambah Data Baru</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div>
                <div class="modal-body">
                    
                    {{-- UBAH BLOK INPUT UNTUK MENAMBAHKAN `data-field` --}}
                    <div class="mb-3 autocomplete-container">
                        <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                               id="nama_kegiatan" name="nama_kegiatan" 
                               value="{{ old('nama_kegiatan') }}" 
                               placeholder="Ketik untuk mencari kegiatan..." required autocomplete="off">
                        <div class="autocomplete-suggestions" id="kegiatan-suggestions"></div>
                        {{-- Ganti/tambahkan div error --}}
                        <div class="invalid-feedback" data-field="nama_kegiatan">@error('nama_kegiatan') {{ $message }} @enderror</div>
                    </div>

                    <div class="mb-3">
                        <label for="BS_Responden" class="form-label">BS/Responden <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('BS_Responden') is-invalid @enderror" id="BS_Responden" name="BS_Responden" value="{{ old('BS_Responden') }}" required>
                        <div class="invalid-feedback" data-field="BS_Responden">@error('BS_Responden'){{ $message }}@enderror</div>
                    </div>
                    
                    <div class="mb-3 autocomplete-container">
                        <label for="pencacah" class="form-label">Pencacah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('pencacah') is-invalid @enderror" id="pencacah" name="pencacah" value="{{ old('pencacah') }}" required autocomplete="off">
                        <div class="autocomplete-suggestions" id="pencacah-suggestions"></div>
                        <div class="invalid-feedback" data-field="pencacah">@error('pencacah'){{ $message }}@enderror</div>
                    </div>
                    
                    <div class="mb-3 autocomplete-container">
                        <label for="pengawas" class="form-label">Pengawas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('pengawas') is-invalid @enderror" id="pengawas" name="pengawas" value="{{ old('pengawas') }}" required autocomplete="off">
                        <div class="autocomplete-suggestions" id="pengawas-suggestions"></div>
                        <div class="invalid-feedback" data-field="pengawas">@error('pengawas'){{ $message }}@enderror</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="target_penyelesaian" class="form-label">Target Penyelesaian <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('target_penyelesaian') is-invalid @enderror" id="target_penyelesaian" name="target_penyelesaian" value="{{ old('target_penyelesaian') }}" required>
                        <div class="invalid-feedback" data-field="target_penyelesaian">@error('target_penyelesaian'){{ $message }}@enderror</div>
                    </div>

                    <div class="mb-3">
                        <label for="flag_progress" class="form-label">Progress <span class="text-danger">*</span></label>
                        <select class="form-select @error('flag_progress') is-invalid @enderror" id="flag_progress" name="flag_progress" required> <option value="Belum Selesai" {{ old('flag_progress') == 'Belum Selesai' ? 'selected' : '' }}>Belum Selesai</option> <option value="Selesai" {{ old('flag_progress') == 'Selesai' ? 'selected' : '' }}>Selesai</option> </select>
                        <div class="invalid-feedback" data-field="flag_progress">@error('flag_progress'){{ $message }}@enderror</div>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                        <input type="date" class="form-control @error('tanggal_pengumpulan') is-invalid @enderror" id="tanggal_pengumpulan" name="tanggal_pengumpulan" value="{{ old('tanggal_pengumpulan') }}">
                        <div class="invalid-feedback" data-field="tanggal_pengumpulan">@error('tanggal_pengumpulan'){{ $message }}@enderror</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    {{-- Tambahkan spinner untuk loading --}}
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        {{-- Form 'id="editForm"' sudah ada, bagus --}}
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header"> <h5 class="modal-title">Edit Data</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div>
                <div class="modal-body">
                    
                    {{-- UBAH BLOK INPUT UNTUK MENAMBAHKAN `data-field` --}}
                    <div class="mb-3 autocomplete-container">
                        <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                               id="edit_nama_kegiatan" name="nama_kegiatan" required autocomplete="off">
                        <div class="autocomplete-suggestions" id="edit-kegiatan-suggestions"></div>
                        <div class="invalid-feedback" data-field="nama_kegiatan">@error('nama_kegiatan') {{ $message }} @enderror</div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_BS_Responden" class="form-label">BS/Responden <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('BS_Responden') is-invalid @enderror" id="edit_BS_Responden" name="BS_Responden" required>
                        <div class="invalid-feedback" data-field="BS_Responden">@error('BS_Responden'){{ $message }}@enderror</div>
                    </div>
                    
                    <div class="mb-3 autocomplete-container">
                        <label for="edit_pencacah" class="form-label">Pencacah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('pencacah') is-invalid @enderror" id="edit_pencacah" name="pencacah" required autocomplete="off">
                        <div class="autocomplete-suggestions" id="edit-pencacah-suggestions"></div>
                        <div class="invalid-feedback" data-field="pencacah">@error('pencacah'){{ $message }}@enderror</div>
                    </div>
                    
                    <div class="mb-3 autocomplete-container">
                        <label for="edit_pengawas" class="form-label">Pengawas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('pengawas') is-invalid @enderror" id="edit_pengawas" name="pengawas" required autocomplete="off">
                        <div class="autocomplete-suggestions" id="edit-pengawas-suggestions"></div>
                        <div class="invalid-feedback" data-field="pengawas">@error('pengawas'){{ $message }}@enderror</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_target_penyelesaian" class="form-label">Target Penyelesaian <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('target_penyelesaian') is-invalid @enderror" id="edit_target_penyelesaian" name="target_penyelesaian" required>
                        <div class="invalid-feedback" data-field="target_penyelesaian">@error('target_penyelesaian'){{ $message }}@enderror</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_flag_progress" class="form-label">Progress <span class="text-danger">*</span></label>
                        <select class="form-select @error('flag_progress') is-invalid @enderror" id="edit_flag_progress" name="flag_progress" required> <option value="Belum Selesai">Belum Selesai</option> <option value="Selesai">Selesai</option> </select>
                        <div class="invalid-feedback" data-field="flag_progress">@error('flag_progress'){{ $message }}@enderror</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                        <input type="date" class="form-control @error('tanggal_pengumpulan') is-invalid @enderror" id="edit_tanggal_pengumpulan" name="tanggal_pengumpulan">
                        <div class="invalid-feedback" data-field="tanggal_pengumpulan">@error('tanggal_pengumpulan'){{ $message }}@enderror</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Simpan Perubahan
                    </button>
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
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteDataModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="deleteModalBody">
                    Apakah Anda yakin ingin menghapus data yang dipilih?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="confirmDeleteButton">Hapus</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')

<script>
    function initAutocomplete(inputId, suggestionsId, searchUrl) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const suggestionsContainer = document.getElementById(suggestionsId);
        let debounceTimer;
        let activeSuggestionIndex = -1; 

        input.addEventListener('input', function() {
            const query = this.value;
            clearTimeout(debounceTimer);
            if (query.length < 1) { 
                if (suggestionsContainer) suggestionsContainer.innerHTML = '';
                activeSuggestionIndex = -1;
                return;
            }
            debounceTimer = setTimeout(() => {
                const finalSearchUrl = `${searchUrl}?query=${query}`;
                fetch(finalSearchUrl)
                    .then(response => response.json())
                    .then(data => {
                        suggestionsContainer.innerHTML = ''; 
                        activeSuggestionIndex = -1;
                        data.forEach((item, index) => {
                            const div = document.createElement('div');
                            div.textContent = item;
                            div.classList.add('autocomplete-suggestion-item');

                            div.onclick = () => {
                                input.value = item;
                                suggestionsContainer.innerHTML = '';
                            };

                            div.onmouseover = () => {
                                document.querySelectorAll(`#${suggestionsId} .autocomplete-suggestion-item`).forEach(el => el.classList.remove('active'));
                                div.classList.add('active');
                                activeSuggestionIndex = index;
                            };
                            suggestionsContainer.appendChild(div);
                        });
                    })
                    .catch(error => console.error('Autocomplete error:', error));
            }, 100); 
        });

 
        input.addEventListener('keydown', function(e) {
            const suggestions = suggestionsContainer.querySelectorAll('.autocomplete-suggestion-item');
            if (suggestions.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault(); 
                activeSuggestionIndex = (activeSuggestionIndex + 1) % suggestions.length;
                updateActiveSuggestion(suggestions, activeSuggestionIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault(); 
                activeSuggestionIndex = (activeSuggestionIndex - 1 + suggestions.length) % suggestions.length;
                updateActiveSuggestion(suggestions, activeSuggestionIndex);
            } else if (e.key === 'Enter') {
                if (activeSuggestionIndex > -1) {
                    e.preventDefault();
                    input.value = suggestions[activeSuggestionIndex].textContent;
                    suggestionsContainer.innerHTML = '';
                    activeSuggestionIndex = -1;
                }
            } else if (e.key === 'Escape') {
                suggestionsContainer.innerHTML = '';
                activeSuggestionIndex = -1;
            }
        });

        function updateActiveSuggestion(suggestions, index) {
            suggestions.forEach(el => el.classList.remove('active'));
            if (suggestions[index]) {
                suggestions[index].classList.add('active');
            }
        }

        document.addEventListener('click', (e) => {
            if (e.target.id !== inputId && suggestionsContainer) {
                suggestionsContainer.innerHTML = '';
                activeSuggestionIndex = -1;
            }
        });
    }

    function editData(id) {
        const editModalEl = document.getElementById('editDataModal');
        if (!editModalEl) return;
        const editModal = new bootstrap.Modal(editModalEl);
        const editForm = document.getElementById('editForm');
        editForm.action = `/tim-distribusi/tahunan/${id}`; 
        
        clearFormErrors(editForm);

        
        fetch(`/tim-distribusi/tahunan/${id}/edit`) 
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(text || 'Data tidak ditemukan');
                    });
                }
                return response.json();
            })
            .then(data => {
                
                document.getElementById('edit_nama_kegiatan').value = data.nama_kegiatan || '';
                document.getElementById('edit_BS_Responden').value = data.BS_Responden || '';
                document.getElementById('edit_pencacah').value = data.pencacah || '';
                document.getElementById('edit_pengawas').value = data.pengawas || '';
                document.getElementById('edit_target_penyelesaian').value = data.target_penyelesaian || '';
                document.getElementById('edit_flag_progress').value = data.flag_progress || 'Belum Selesai';
                document.getElementById('edit_tanggal_pengumpulan').value = data.tanggal_pengumpulan || '';

                editModal.show(); 
            })
            .catch(error => {
                console.error("Error loading edit data:", error);
                alert('Tidak dapat memuat data untuk diedit. Error: ' + error.message);
            });
    }

    function deleteData(id) {
        const deleteModalEl = document.getElementById('deleteDataModal');
        if (!deleteModalEl) return;
        const deleteModal = new bootstrap.Modal(deleteModalEl);
        const deleteForm = document.getElementById('deleteForm');

        deleteForm.action = `/tim-distribusi/tahunan/${id}`; 

        let methodInput = deleteForm.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            deleteForm.appendChild(methodInput);
        }
        methodInput.value = 'DELETE';

        deleteForm.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());

        document.getElementById('deleteModalBody').innerText = 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';

        const newConfirmButton = document.getElementById('confirmDeleteButton').cloneNode(true);
        document.getElementById('confirmDeleteButton').parentNode.replaceChild(newConfirmButton, document.getElementById('confirmDeleteButton'));
        newConfirmButton.addEventListener('click', (e) => {
            e.preventDefault();
            deleteForm.submit();
        });

        deleteModal.show();
    }


    function clearFormErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
       
        form.querySelectorAll('.invalid-feedback[data-field]').forEach(el => el.textContent = '');
    }

    function showFormErrors(form, errors) {
        for (const [field, messages] of Object.entries(errors)) {
            const input = form.querySelector(`[name="${field}"]`);
            
            const errorDiv = form.querySelector(`.invalid-feedback[data-field="${field}"]`);

            if (input) {
                input.classList.add('is-invalid');
            }
            if (errorDiv) {
                errorDiv.textContent = messages[0]; 
            }
        }
    }

    async function handleFormSubmitAjax(event, form, modalInstance) {
        event.preventDefault(); 

        const submitButton = form.querySelector('button[type="submit"]');
        const spinner = submitButton.querySelector('.spinner-border');

        submitButton.disabled = true;
        if (spinner) spinner.classList.remove('d-none');
        clearFormErrors(form);

        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: form.method,
                body: formData,
                headers: {
                    'Accept': 'application/json', 
                    'X-CSRF-TOKEN': formData.get('_token')
                }
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 422 && data.errors) {
                    showFormErrors(form, data.errors);
                } else {
                    alert(data.message || 'Terjadi kesalahan server.');
                }
            } else {
                
                modalInstance.hide();
                
                location.reload();
            }

        } catch (error) {
            console.error('Fetch error:', error);
            alert('Tidak dapat terhubung ke server.');
        } finally {
            submitButton.disabled = false;
            if (spinner) spinner.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {

        @if(Route::has('master.kegiatan.search'))
        initAutocomplete('nama_kegiatan', 'kegiatan-suggestions', '{{ route("master.kegiatan.search") }}');
        initAutocomplete('edit_nama_kegiatan', 'edit-kegiatan-suggestions', '{{ route("master.kegiatan.search") }}');
        @else
        console.warn('Rute "master.kegiatan.search" tidak ditemukan.');
        @endif

        @if(Route::has('master.petugas.search'))
        initAutocomplete('pencacah', 'pencacah-suggestions', '{{ route("master.petugas.search") }}');
        initAutocomplete('pengawas', 'pengawas-suggestions', '{{ route("master.petugas.search") }}');
        initAutocomplete('edit_pencacah', 'edit-pencacah-suggestions', '{{ route("master.petugas.search") }}');
        initAutocomplete('edit_pengawas', 'edit-pengawas-suggestions', '{{ route("master.petugas.search") }}');
        @else
        console.warn('Rute "master.petugas.search" tidak ditemukan.');
        @endif


        const tambahModalEl = document.getElementById('tambahDataModal');
        const tambahForm = document.getElementById('tambahForm');
        if (tambahModalEl && tambahForm) {
            const tambahModal = new bootstrap.Modal(tambahModalEl);
            tambahForm.addEventListener('submit', (event) => {
                handleFormSubmitAjax(event, tambahForm, tambahModal);
            });
            tambahModalEl.addEventListener('hidden.bs.modal', () => {
                clearFormErrors(tambahForm);
                tambahForm.reset(); 
            });
        }

        const editModalEl = document.getElementById('editDataModal');
        const editForm = document.getElementById('editForm');
        if (editModalEl && editForm) {
            const editModal = new bootstrap.Modal(editModalEl);
            editForm.addEventListener('submit', (event) => {
                handleFormSubmitAjax(event, editForm, editModal);
            });
            editModalEl.addEventListener('hidden.bs.modal', () => {
                clearFormErrors(editForm);
            });
        }

        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const deleteForm = document.getElementById('deleteForm'); 

        function updateBulkDeleteBtnState() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            if (bulkDeleteBtn) bulkDeleteBtn.disabled = checkedCount === 0;
        }

        selectAll?.addEventListener('change', () => {
            rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkDeleteBtnState();
        });
        rowCheckboxes.forEach(cb => cb.addEventListener('change', updateBulkDeleteBtnState));
        updateBulkDeleteBtnState(); 

        bulkDeleteBtn?.addEventListener('click', () => {
            const count = document.querySelectorAll('.row-checkbox:checked').length;
            if (count === 0) return;

            const deleteModalEl = document.getElementById('deleteDataModal');
            if (!deleteModalEl || !deleteForm) return;
            const deleteModal = new bootstrap.Modal(deleteModalEl);

            deleteForm.action = '{{ route("tim-distribusi.tahunan.bulkDelete") }}';
            let methodInput = deleteForm.querySelector('input[name="_method"]');
            if (methodInput) methodInput.value = 'POST';
            
            deleteForm.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
            document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = cb.value;
                deleteForm.appendChild(input);
            });

            document.getElementById('deleteModalBody').innerText = `Apakah Anda yakin ingin menghapus ${count} data yang dipilih?`;
            
            const newConfirmButton = document.getElementById('confirmDeleteButton').cloneNode(true);
            document.getElementById('confirmDeleteButton').parentNode.replaceChild(newConfirmButton, document.getElementById('confirmDeleteButton'));
            newConfirmButton.addEventListener('click', (e) => {
                e.preventDefault();
                deleteForm.submit();
            });

            deleteModal.show();
        });

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

        @if (session('error_modal') == 'tambahDataModal' && $errors->any())
        const tambahModalEl_fallback = document.getElementById('tambahDataModal');
        if (tambahModalEl_fallback) {
            const tambahModal = new bootstrap.Modal(tambahModalEl_fallback);
            tambahModal.show();
        }
        @endif

        @if (session('error_modal') == 'editDataModal' && $errors->any() && session('edit_id'))
        const editId = {{ session('edit_id') }};
        if (editId) {
            editData(editId); 
            
            setTimeout(() => {
                const editForm_fallback = document.getElementById('editForm');
                @foreach ($errors->keys() as $field)
                    // Target input
                    const fieldElement = editForm_fallback.querySelector('[name="{{ $field }}"]');
                    if (fieldElement) {
                        fieldElement.classList.add('is-invalid');
                    }
                    // Target div error
                    const errorElement = editForm_fallback.querySelector(`.invalid-feedback[data-field="{{ $field }}"]`);
                    if (errorElement) {
                         errorElement.textContent = '{{ $errors->first($field) }}';
                    }
                @endforeach
            }, 500);
        }
        @endif

        const autoHideAlerts = document.querySelectorAll('.alert-dismissible[role="alert"]');
        autoHideAlerts.forEach(alert => {
            if (!alert.closest('.modal')) { // Pastikan ini bukan bagian dari modal
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    if (bsAlert) {
                        bsAlert.close();
                    }
                }, 5000); 
            }
        });

    });
</script>

@endpush

