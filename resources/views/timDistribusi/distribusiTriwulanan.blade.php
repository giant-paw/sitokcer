@extends('layouts.app')

@section('title', 'Distribusi Triwulanan - Sitokcer')
@section('header-title', 'List Target Kegiatan Triwulanan Tim Distribusi')

@push('styles')
<style>
    .autocomplete-container {
        position: relative;
    }
    .autocomplete-suggestions {
        position: absolute;
        border: 1px solid #d1d3e2;
        border-top: none;
        z-index: 1056;
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


@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">LIST TARGET KEGIATAN TRIWULANAN: {{ strtoupper($jenisKegiatan) }}</h4>
            </div>
            <div class="card-body">
                <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal">
                            <i class="bi bi-plus-circle"></i> Tambah Baru
                        </button>
                        <button type="button" class="btn btn-secondary">
                            <i class="bi bi-upload"></i> Import
                        </button>
                        <button type="button" class="btn btn-success">
                            <i class="bi bi-download"></i> Ekspor Hasil
                        </button>
                        <button 
                            type="button" 
                            class="btn btn-danger"
                            data-bs-target="#deleteDataModal" 
                            id="bulkDeleteBtn"
                            disabled>
                            <i class="bi bi-trash"></i> Hapus Data Terpilih
                        </button>
                    </div>
                    <div class="d-flex align-items-center">
                        <label for="perPageSelect" class="form-label me-2 mb-0">Display:</label>
                        <select class="form-select form-select-sm" id="perPageSelect" style="width: auto;">
                            @php $options = [10, 20, 30, 50, 100, 500, 'all']; @endphp
                            @foreach($options as $option)
                                <option value="{{ $option }}" {{ (request('per_page', 20) == $option) ? 'selected' : '' }}>
                                    {{ $option == 'all' ? 'All' : $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Alert biarkan apa adanya --}}
                @if (session('success'))
                ...
                @endif
                @if ($errors->any())
                ...
                @endif

                <ul class="nav nav-pills mb-3 d-flex flex-wrap gap-8" >
                    <li class="nav-item"> 
                        <a class="nav-link {{ request('kegiatan') == '' ? 'active' : '' }}" href="{{ route('tim-distribusi.triwulanan.index', ['jenisKegiatan' => $jenisKegiatan]) }}">All data</a>
                    </li>
                    @foreach($kegiatanCounts as $kegiatan)
                        <li class="nav-item">
                            <a class="nav-link {{ request('kegiatan') == $kegiatan->nama_kegiatan ? 'active' : '' }}" 
                            href="{{ route('tim-distribusi.triwulanan.index', ['jenisKegiatan' => $jenisKegiatan, 'kegiatan' => $kegiatan->nama_kegiatan]) }}">
                                
                                {{ $kegiatan->nama_kegiatan }} <span class="badge bg-secondary">{{ $kegiatan->total }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <form action="{{ route('tim-distribusi.triwulanan.index', ['jenisKegiatan' => $jenisKegiatan]) }}" method="GET" class="mb-4">
                    {{-- ... (Form pencarian biarkan apa adanya) ... --}}
                </form>


                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
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
                                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $item->id_distribusi_triwulanan }}">
                                    </td>
                                    <td>{{ $item->nama_kegiatan }}</td>
                                    <td>{{ $item->BS_Responden }}</td>
                                    <td>{{ $item->pencacah }}</td>
                                    <td>{{ $item->pengawas }}</td>
                                    
                                    {{-- PERBAIKAN FORMAT TANGGAL --}}
                                    <td>{{ $item->target_penyelesaian ? $item->target_penyelesaian->format('d/m/Y') : '-' }}</td>
                                    
                                    <td><span class="badge {{ $item->flag_progress == 'Selesai' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $item->flag_progress }}</span></td>
                                    
                                    {{-- PERBAIKAN FORMAT TANGGAL --}}
                                    <td>{{ $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format('d/m/Y') : '-' }}</td>
                                    
                                    <td class="d-flex gap-2">
                                        <button class="btn btn-sm btn-warning" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editDataModal" 
                                                onclick="editData({{ $item->id_distribusi_triwulanan }})">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <button class="btn btn-sm btn-danger" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteDataModal" 
                                                onclick="deleteData({{ $item->id_distribusi_triwulanan }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center"> {{-- Perbaikan layout footer --}}
                <div class="text-muted small">
                    Displaying {{ $listData->firstItem() ?? 0 }} - {{ $listData->lastItem() ?? 0 }} of {{ $listData->total() }}
                </div>
                <div>
                    {{ $listData->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            {{-- TAMBAHKAN ID FORM --}}
            <form action="{{ route('tim-distribusi.triwulanan.store') }}" method="POST" id="tambahForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="tambahDataModalLabel">Tambah Data Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                
                {{-- UBAH INPUT NAMA KEGIATAN --}}
                <div class="mb-3 autocomplete-container">
                    <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                           id="nama_kegiatan" name="nama_kegiatan" 
                           value="{{ old('nama_kegiatan') }}" 
                           placeholder="Ketik untuk mencari kegiatan..." required autocomplete="off">
                    <div class="autocomplete-suggestions" id="kegiatan-suggestions"></div>
                    <div class="invalid-feedback" data-field="nama_kegiatan">@error('nama_kegiatan') {{ $message }} @enderror</div>
                </div>

                <div class="mb-3">
                    <label for="BS_Responden" class="form-label">Blok Sensus/Responden <span class="text-danger">*</span></label>
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
                    <label for="target_penyelesaian" class="form-label">Tanggal Target Penyelesaian <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('target_penyelesaian') is-invalid @enderror" id="target_penyelesaian" name="target_penyelesaian" value="{{ old('target_penyelesaian') }}" required>
                    <div class="invalid-feedback" data-field="target_penyelesaian">@error('target_penyelesaian'){{ $message }}@enderror</div>
                </div>
                
                <div class="mb-3">
                    <label for="flag_progress" class="form-label">Flag Progress <span class="text-danger">*</span></label>
                    <select class="form-select @error('flag_progress') is-invalid @enderror" id="flag_progress" name="flag_progress" required>
                    <option value="Belum Selesai" {{ old('flag_progress') == 'Belum Selesai' ? 'selected' : '' }}>Belum Selesai</option>
                    <option value="Selesai" {{ old('flag_progress') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    <div class="invalid-feedback" data-field="flag_progress">@error('flag_progress'){{ $message }}@enderror</div>
                </div>
                
                <div class="mb-3">
                    <label for="tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                    <input type="date" class="form-control @error('tanggal_pengumpulan') is-invalid @enderror" id="tanggal_pengumpulan" name="tanggal_pengumpulan" value="{{ old('tanggal_pengumpulan') }}" required>
                    <div class="invalid-feedback" data-field="tanggal_pengumpulan">@error('tanggal_pengumpulan'){{ $message }}@enderror</div>
                </div>
                </div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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
            <form id="editForm" method="POST"> {{-- ID sudah ada --}}
                @csrf
                @method('PUT') 
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDataModalLabel">Edit Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        
                        {{-- UBAH INPUT NAMA KEGIATAN --}}
                        <div class="mb-3 autocomplete-container">
                            <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                                   id="edit_nama_kegiatan" name="nama_kegiatan" required autocomplete="off">
                            <div class="autocomplete-suggestions" id="edit-kegiatan-suggestions"></div>
                            <div class="invalid-feedback" data-field="nama_kegiatan">@error('nama_kegiatan') {{ $message }} @enderror</div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_BS_Responden" class="form-label">Blok Sensus/Responden <span class="text-danger">*</span></label>
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
                            <label for="edit_target_penyelesaian" class="form-label">Tanggal Target Penyelesaian <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('target_penyelesaian') is-invalid @enderror" id="edit_target_penyelesaian" name="target_penyelesaian" required>
                            <div class="invalid-feedback" data-field="target_penyelesaian">@error('target_penyelesaian'){{ $message }}@enderror</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_flag_progress" class="form-label">Flag Progress <span class="text-danger">*</span></label>
                            <select class="form-select @error('flag_progress') is-invalid @enderror" id="edit_flag_progress" name="flag_progress" required>
                                <option value="Belum Selesai">Belum Selesai</option>
                                <option value="Selesai">Selesai</option>
                            </select>
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
                        Apakah Anda yakin ingin menghapus data ini?
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
                if (suggestionsContainer) suggestionsContainer.innerHTML = '';
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

        // PERBAIKAN BUG MODAL: Gunakan getOrCreateInstance
        const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
        
        const editForm = document.getElementById('editForm');
        
        editForm.action = `/tim-distribusi/triwulanan/${id}`; 
        clearFormErrors(editForm);

        fetch(`/tim-distribusi/triwulanan/${id}/edit`) 
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

        // PERBAIKAN BUG MODAL: Gunakan getOrCreateInstance
        const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);

        const deleteForm = document.getElementById('deleteForm');
        
        deleteForm.action = `/tim-distribusi/triwulanan/${id}`; 

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

    /**
     * Menghapus semua pesan error validasi dari form
     * @param {HTMLFormElement} form - Elemen form
     */
    function clearFormErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback[data-field]').forEach(el => el.textContent = '');
    }

    /**
     * Menampilkan error validasi di form
     * @param {HTMLFormElement} form - Elemen form
     * @param {object} errors - Objek error dari Laravel (cth: {nama_kegiatan: ["pesan"]})
     */
    function showFormErrors(form, errors) {
        for (const [field, messages] of Object.entries(errors)) {
            const input = form.querySelector(`[name="${field}"]`);
            const errorDiv = form.querySelector(`.invalid-feedback[data-field="${field}"]`);
            
            if (input) input.classList.add('is-invalid');
            if (errorDiv) errorDiv.textContent = messages[0];
        }
    }

    /**
     * Meng-handle submit form via AJAX
     * @param {Event} event - Event submit
     * @param {HTMLFormElement} form - Elemen form
     * @param {bootstrap.Modal} modalInstance - Instance modal Bootstrap
     */
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

    // --- SCRIPT UTAMA SETELAH DOKUMEN SIAP (DOM Ready) ---
    document.addEventListener('DOMContentLoaded', function() {

        // --- Inisialisasi Autocomplete ---
        // Pastikan rute ini ada di web.php dan controllernya ada fungsi search
        @if(Route::has('master.kegiatan.search'))
            initAutocomplete('nama_kegiatan', 'kegiatan-suggestions', '{{ route("master.kegiatan.search") }}');
            initAutocomplete('edit_nama_kegiatan', 'edit-kegiatan-suggestions', '{{ route("master.kegiatan.search") }}');
        @else
            console.warn('Rute "master.kegiatan.search" tidak ditemukan.');
        @endif

        // Pastikan rute ini ada di web.php dan controllernya ada fungsi search
        @if(Route::has('master.petugas.search'))
            initAutocomplete('pencacah', 'pencacah-suggestions', '{{ route("master.petugas.search") }}');
            initAutocomplete('pengawas', 'pengawas-suggestions', '{{ route("master.petugas.search") }}');
            initAutocomplete('edit_pencacah', 'edit-pencacah-suggestions', '{{ route("master.petugas.search") }}');
            initAutocomplete('edit_pengawas', 'edit-pengawas-suggestions', '{{ route("master.petugas.search") }}');
        @else
            console.warn('Rute "master.petugas.search" tidak ditemukan.');
        @endif


        // --- Inisialisasi Handler AJAX Form ---
        const tambahModalEl = document.getElementById('tambahDataModal');
        const tambahForm = document.getElementById('tambahForm');
        if (tambahModalEl && tambahForm) {
            // PERBAIKAN BUG MODAL: Gunakan getOrCreateInstance
            const tambahModal = bootstrap.Modal.getOrCreateInstance(tambahModalEl);
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
            // PERBAIKAN BUG MODAL: Gunakan getOrCreateInstance
            const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
            editForm.addEventListener('submit', (event) => {
                handleFormSubmitAjax(event, editForm, editModal);
            });
            editModalEl.addEventListener('hidden.bs.modal', () => {
                clearFormErrors(editForm);
            });
        }


        // --- Logika Select All & Bulk Delete ---
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

            // PERBAIKAN BUG MODAL: Gunakan getOrCreateInstance
            const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);

            deleteForm.action = '{{ route("tim-distribusi.triwulanan.bulkDelete") }}';
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

        // --- Logika Dropdown Per Page ---
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

        // --- Logika Dropdown TAHUN ---
        const tahunSelect = document.getElementById('tahunSelect');
        if (tahunSelect) {
            tahunSelect.addEventListener('change', function() {
                const selectedTahun = this.value;
                const currentUrl = new URL(window.location.href);
                const params = currentUrl.searchParams;
                params.set('tahun', selectedTahun);
                params.set('page', 1); 
                window.location.href = currentUrl.pathname + '?' + params.toString();
            });
        }

        @if (session('error_modal') == 'tambahDataModal' && $errors->any())
        const tambahModalEl_fallback = document.getElementById('tambahDataModal');
        if (tambahModalEl_fallback) {
            // PERBAIKAN BUG MODAL
            bootstrap.Modal.getOrCreateInstance(tambahModalEl_fallback).show();
        }
        @endif

        @if (session('error_modal') == 'editDataModal' && $errors->any() && session('edit_id'))
        const editId = {{ session('edit_id') }};
        if (editId) {
            editData(editId); // Panggil fungsi editData (yang sudah diperbaiki)
            
            setTimeout(() => {
                const editForm_fallback = document.getElementById('editForm');
                @foreach ($errors->keys() as $field)
                    const fieldElement = editForm_fallback.querySelector('[name="{{ $field }}"]');
                    if (fieldElement) {
                        fieldElement.classList.add('is-invalid');
                    }
                    const errorElement = editForm_fallback.querySelector(`.invalid-feedback[data-field="{{ $field }}"]`);
                    if (errorElement) {
                         errorElement.textContent = '{{ $errors->first($field) }}';
                    }
                @endforeach
            }, 500);
        }
        @endif

        // --- Auto-hide Alert Sukses/Error (Non-Modal) ---
        const autoHideAlerts = document.querySelectorAll('.alert-dismissible[role="alert"]');
        autoHideAlerts.forEach(alert => {
            if (!alert.closest('.modal')) { 
                setTimeout(() => {
                    // PERBAIKAN BUG MODAL
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