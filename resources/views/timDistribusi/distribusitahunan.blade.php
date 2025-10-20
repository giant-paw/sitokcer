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
                            <span class="badge bg-secondary rounded-pill">{{ $item->total }}</span>
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
        <form action="{{ route('tim-distribusi.tahunan.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header"> <h5 class="modal-title">Tambah Data Baru</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div>
                <div class="modal-body">
                    
                    <div class="mb-3 autocomplete-container">
                        <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                               id="nama_kegiatan" name="nama_kegiatan" 
                               value="{{ old('nama_kegiatan') }}" 
                               placeholder="Ketik untuk mencari kegiatan..." required autocomplete="off">
                        <div class="autocomplete-suggestions" id="kegiatan-suggestions"></div>
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

<div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header"> <h5 class="modal-title">Edit Data</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div>
                <div class="modal-body">
                    
                    <div class="mb-3 autocomplete-container">
                        <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                               id="edit_nama_kegiatan" name="nama_kegiatan" required autocomplete="off">
                        <div class="autocomplete-suggestions" id="edit-kegiatan-suggestions"></div>
                        @error('nama_kegiatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3"> <label for="edit_BS_Responden" class="form-label">BS/Responden <span class="text-danger">*</span></label> <input type="text" class="form-control @error('BS_Responden') is-invalid @enderror" id="edit_BS_Responden" name="BS_Responden" required> @error('BS_Responden')<div class="invalid-feedback">{{ $message }}</div>@enderror </div>
                    <div class="mb-3 autocomplete-container"> <label for="edit_pencacah" class="form-label">Pencacah <span class="text-danger">*</span></label> <input type="text" class="form-control @error('pencacah') is-invalid @enderror" id="edit_pencacah" name="pencacah" required autocomplete="off"> <div class="autocomplete-suggestions" id="edit-pencacah-suggestions"></div> @error('pencacah')<div class="invalid-feedback">{{ $message }}</div>@enderror </div>
                    <div class="mb-3 autocomplete-container"> <label for="edit_pengawas" class="form-label">Pengawas <span class="text-danger">*</span></label> <input type="text" class="form-control @error('pengawas') is-invalid @enderror" id="edit_pengawas" name="pengawas" required autocomplete="off"> <div class="autocomplete-suggestions" id="edit-pengawas-suggestions"></div> @error('pengawas')<div class="invalid-feedback">{{ $message }}</div>@enderror </div>
                    <div class="mb-3"> <label for="edit_target_penyelesaian" class="form-label">Target Penyelesaian <span class="text-danger">*</span></label> <input type="date" class="form-control @error('target_penyelesaian') is-invalid @enderror" id="edit_target_penyelesaian" name="target_penyelesaian" required> @error('target_penyelesaian')<div class="invalid-feedback">{{ $message }}</div>@enderror </div>
                    <div class="mb-3"> <label for="edit_flag_progress" class="form-label">Progress <span class="text-danger">*</span></label> <select class="form-select @error('flag_progress') is-invalid @enderror" id="edit_flag_progress" name="flag_progress" required> <option value="Belum Selesai">Belum Selesai</option> <option value="Selesai">Selesai</option> </select> @error('flag_progress')<div class="invalid-feedback">{{ $message }}</div>@enderror </div>
                    <div class="mb-3"> <label for="edit_tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label> <input type="date" class="form-control @error('tanggal_pengumpulan') is-invalid @enderror" id="edit_tanggal_pengumpulan" name="tanggal_pengumpulan"> @error('tanggal_pengumpulan')<div class="invalid-feedback">{{ $message }}</div>@enderror </div>
                </div>
                <div class="modal-footer"> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button> <button type="submit" class="btn btn-primary">Simpan Perubahan</button> </div>
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
    /**
     * Inisialisasi Autocomplete Input Field
     * @param {string} inputId - ID dari elemen input
     * @param {string} suggestionsId - ID dari container untuk menampilkan suggestions
     * @param {string} searchUrl - URL endpoint untuk fetch data autocomplete
     */
    function initAutocomplete(inputId, suggestionsId, searchUrl) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const suggestionsContainer = document.getElementById(suggestionsId);
        let debounceTimer;
        let activeSuggestionIndex = -1; // Untuk navigasi keyboard

        input.addEventListener('input', function() {
            const query = this.value;
            clearTimeout(debounceTimer);
            if (query.length < 1) { // Tampilkan rekomendasi bahkan dari 1 karakter
                if(suggestionsContainer) suggestionsContainer.innerHTML = '';
                activeSuggestionIndex = -1;
                return;
            }
            debounceTimer = setTimeout(() => {
                const finalSearchUrl = `${searchUrl}?query=${query}`;
                fetch(finalSearchUrl)
                    .then(response => response.json())
                    .then(data => {
                        suggestionsContainer.innerHTML = ''; // Kosongkan suggestion lama
                        activeSuggestionIndex = -1;
                        data.forEach((item, index) => {
                            const div = document.createElement('div');
                            div.textContent = item;
                            div.classList.add('autocomplete-suggestion-item');
                            
                            // Saat suggestion diklik, isi input dan tutup box
                            div.onclick = () => {
                                input.value = item;
                                suggestionsContainer.innerHTML = '';
                            };
                            // Saat mouse hover, update active index
                            div.onmouseover = () => {
                                document.querySelectorAll(`#${suggestionsId} .autocomplete-suggestion-item`).forEach(el => el.classList.remove('active'));
                                div.classList.add('active');
                                activeSuggestionIndex = index;
                            };
                            suggestionsContainer.appendChild(div);
                        });
                    })
                    .catch(error => console.error('Autocomplete error:', error));
            }, 300); // Delay 300ms sebelum fetch
        });
        
        // Navigasi Keyboard (Arrow Up, Arrow Down, Enter, Escape)
        input.addEventListener('keydown', function(e) {
            const suggestions = suggestionsContainer.querySelectorAll('.autocomplete-suggestion-item');
            if (suggestions.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault(); // Hentikan kursor gerak di input
                activeSuggestionIndex = (activeSuggestionIndex + 1) % suggestions.length;
                updateActiveSuggestion(suggestions, activeSuggestionIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault(); // Hentikan kursor gerak di input
                activeSuggestionIndex = (activeSuggestionIndex - 1 + suggestions.length) % suggestions.length;
                updateActiveSuggestion(suggestions, activeSuggestionIndex);
            } else if (e.key === 'Enter') {
                if (activeSuggestionIndex > -1) {
                    e.preventDefault(); // Hentikan form submit
                    input.value = suggestions[activeSuggestionIndex].textContent;
                    suggestionsContainer.innerHTML = '';
                    activeSuggestionIndex = -1;
                }
            } else if (e.key === 'Escape') {
                suggestionsContainer.innerHTML = '';
                activeSuggestionIndex = -1;
            }
        });

        /** Helper untuk update suggestion yg aktif (highlight) */
        function updateActiveSuggestion(suggestions, index) {
            suggestions.forEach(el => el.classList.remove('active'));
            if (suggestions[index]) {
                suggestions[index].classList.add('active');
            }
        }

        // Tutup suggestion box jika klik di luar input
        document.addEventListener('click', (e) => {
            if (e.target.id !== inputId && suggestionsContainer) {
                suggestionsContainer.innerHTML = '';
                activeSuggestionIndex = -1;
            }
        });
    }

    /**
     * Tampilkan modal edit dan isi datanya dari server
     * @param {number} id - ID data yang akan diedit
     */
    function editData(id) {
        const editModalEl = document.getElementById('editDataModal');
        if (!editModalEl) return;
        const editModal = new bootstrap.Modal(editModalEl);
        const editForm = document.getElementById('editForm');
        editForm.action = `/tim-distribusi/tahunan/${id}`; // Set action form edit

        // Ambil data dari server
        fetch(`/tim-distribusi/tahunan/${id}/edit`) // Panggil rute edit
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(text || 'Data tidak ditemukan'); });
                }
                return response.json();
            })
            .then(data => {
                // Isi field di modal edit
                document.getElementById('edit_nama_kegiatan').value = data.nama_kegiatan || '';
                document.getElementById('edit_BS_Responden').value = data.BS_Responden || '';
                document.getElementById('edit_pencacah').value = data.pencacah || '';
                document.getElementById('edit_pengawas').value = data.pengawas || '';
                document.getElementById('edit_target_penyelesaian').value = data.target_penyelesaian || '';
                document.getElementById('edit_flag_progress').value = data.flag_progress || 'Belum Selesai';
                document.getElementById('edit_tanggal_pengumpulan').value = data.tanggal_pengumpulan || '';

                // Hapus kelas error validasi sebelumnya
                editForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                editForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                editModal.show(); // Tampilkan modal
            })
            .catch(error => {
                console.error("Error loading edit data:", error);
                alert('Tidak dapat memuat data untuk diedit. Error: ' + error.message);
            });
    }

    /**
     * Konfigurasi modal hapus untuk satu data
     * @param {number} id - ID data yang akan dihapus
     */
    function deleteData(id) {
        const deleteModalEl = document.getElementById('deleteDataModal');
        if (!deleteModalEl) return;
        const deleteModal = new bootstrap.Modal(deleteModalEl);
        const deleteForm = document.getElementById('deleteForm');
        
        deleteForm.action = `/tim-distribusi/tahunan/${id}`; // Set action untuk hapus satu data

        // Pastikan method DELETE digunakan
        let methodInput = deleteForm.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            deleteForm.appendChild(methodInput);
        }
        methodInput.value = 'DELETE';
        
        // Hapus input 'ids[]' jika ada dari bulk delete sebelumnya
        deleteForm.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());

        document.getElementById('deleteModalBody').innerText = 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.';
        
        // Atasi duplikasi event listener
        const newConfirmButton = document.getElementById('confirmDeleteButton').cloneNode(true);
        document.getElementById('confirmDeleteButton').parentNode.replaceChild(newConfirmButton, document.getElementById('confirmDeleteButton'));
        newConfirmButton.addEventListener('click', (e) => {
            e.preventDefault();
            deleteForm.submit();
        });

        deleteModal.show();
    }

    // --- SCRIPT UTAMA SETELAH DOKUMEN SIAP (DOM Ready) ---
    document.addEventListener('DOMContentLoaded', function() {

        // ==================================================================
        // INI BAGIAN PENTING YANG MEMANGGIL FUNGSI AUTOCOMPLETE
        // ==================================================================

        // Init autocomplete untuk KEGIATAN
        @if(Route::has('master.kegiatan.search'))
            initAutocomplete('nama_kegiatan', 'kegiatan-suggestions', '{{ route("master.kegiatan.search") }}');
            initAutocomplete('edit_nama_kegiatan', 'edit-kegiatan-suggestions', '{{ route("master.kegiatan.search") }}');
        @else
            console.warn('Rute "master.kegiatan.search" tidak ditemukan. Autocomplete kegiatan tidak akan berfungsi.');
        @endif

        // Init autocomplete untuk PETUGAS
        @if(Route::has('master.petugas.search'))
            initAutocomplete('pencacah', 'pencacah-suggestions', '{{ route("master.petugas.search") }}');
            initAutocomplete('pengawas', 'pengawas-suggestions', '{{ route("master.petugas.search") }}');
            initAutocomplete('edit_pencacah', 'edit-pencacah-suggestions', '{{ route("master.petugas.search") }}');
            initAutocomplete('edit_pengawas', 'edit-pengawas-suggestions', '{{ route("master.petugas.search") }}');
        @else
            console.warn('Rute "master.petugas.search" tidak ditemukan. Autocomplete petugas tidak akan berfungsi.');
        @endif

        // ------------------------------------------------------------------
        // Logika Select All & Bulk Delete Button
        // ------------------------------------------------------------------
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const deleteForm = document.getElementById('deleteForm'); // Form di modal hapus

        function updateBulkDeleteBtnState() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            if (bulkDeleteBtn) bulkDeleteBtn.disabled = checkedCount === 0;
        }

        selectAll?.addEventListener('change', () => {
            rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkDeleteBtnState();
        });

        rowCheckboxes.forEach(cb => cb.addEventListener('change', updateBulkDeleteBtnState));
        updateBulkDeleteBtnState(); // Panggil saat load

        // Event listener untuk tombol 'Hapus Terpilih' (Bulk Delete)
        bulkDeleteBtn?.addEventListener('click', () => {
            const count = document.querySelectorAll('.row-checkbox:checked').length;
            if (count === 0) return;

            const deleteModalEl = document.getElementById('deleteDataModal');
            if (!deleteModalEl || !deleteForm) return;
            const deleteModal = new bootstrap.Modal(deleteModalEl);

            deleteForm.action = '{{ route("tim-distribusi.tahunan.bulkDelete") }}'; // Set action ke bulk delete
            
            // Pastikan method POST digunakan
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
                params.set('page', 1); // Reset ke halaman 1
                window.location.href = currentUrl.pathname + '?' + params.toString();
            });
        }

        // ------------------------------------------------------------------
        // Logika buka modal jika ada error validasi
        // ------------------------------------------------------------------
        @if (session('error_modal') == 'tambahDataModal' && $errors->any())
            const tambahModalEl = document.getElementById('tambahDataModal');
            if (tambahModalEl) {
                const tambahModal = new bootstrap.Modal(tambahModalEl);
                tambahModal.show();
            }
        @endif

        // Jika ada error saat EDIT
        @if (session('error_modal') == 'editDataModal' && $errors->any() && session('edit_id'))
            const editId = {{ session('edit_id') }};
            if (editId) {
                editData(editId); // Panggil fungsi editData untuk isi data & tampilkan modal
                
                // Tandai error setelah modal show (butuh delay)
                setTimeout(() => {
                    @foreach ($errors->keys() as $field)
                        const fieldElement = document.getElementById('edit_{{ $field }}');
                        if (fieldElement) {
                            fieldElement.classList.add('is-invalid');
                            const errorElement = fieldElement.closest('.mb-3')?.querySelector('.invalid-feedback');
                            if (errorElement) {
                                errorElement.textContent = '{{ $errors->first($field) }}';
                            }
                        }
                    @endforeach
                }, 500); // Delay 500ms agar modal sempat render
            }
        @endif

        // ------------------------------------------------------------------
        // Auto-hide alert sukses/error (bukan di modal)
        // ------------------------------------------------------------------
        const autoHideAlerts = document.querySelectorAll('.alert-dismissible[role="alert"]');
        autoHideAlerts.forEach(alert => {
            if (!alert.closest('.modal')) { // Pastikan ini bukan bagian dari modal
                setTimeout(() => {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert); 
                    if (bsAlert) {
                        bsAlert.close();
                    }
                }, 5000); // Hilang setelah 5 detik
            }
        });

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
        border: 1px solid #d1d3e2;
        border-top: none;
        z-index: 1056; /* Di atas modal (1055) */
        width: 100%;
        background-color: #fff;
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