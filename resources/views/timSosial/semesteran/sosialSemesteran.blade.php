@extends('layouts.app')

{{-- Judul Disesuaikan --}}
@section('title', 'Sosial Semesteran (' . ucfirst($jenisKegiatan) . ') - Sitokcer')
@section('header-title', 'List Target Kegiatan ' . ucfirst($jenisKegiatan))

{{-- STYLES UNTUK AUTOCOMPLETE --}}
@push('styles')
<style>
    .autocomplete-container { position: relative; }
    .autocomplete-suggestions {
        position: absolute; border: 1px solid #d1d3e2; border-top: none;
        z-index: 1056; width: 100%; background-color: #fff;
        max-height: 200px; overflow-y: auto;
    }
    .autocomplete-suggestion-item { padding: 8px 12px; cursor: pointer; }
    .autocomplete-suggestion-item:hover,
    .autocomplete-suggestion-item.active { background-color: #0d6efd; color: #fff; }
</style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-light">
                {{-- Judul Disesuaikan --}}
                <h4 class="card-title mb-0">LIST TARGET KEGIATAN {{ strtoupper($jenisKegiatan) }}</h4>
            </div>
            <div class="card-body">
                <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal"><i class="bi bi-plus-circle"></i> Tambah Baru</button>
                        <button type="button" class="btn btn-secondary"><i class="bi bi-upload"></i> Import</button>
                        <button type="button" class="btn btn-success"><i class="bi bi-download"></i> Ekspor Hasil</button>
                        <button type="button" class="btn btn-danger" data-bs-target="#deleteDataModal" id="bulkDeleteBtn" disabled><i class="bi bi-trash"></i> Hapus Data Terpilih</button>
                    </div>

                    {{-- Filter Tahun & Per Page --}}
                    <div class="d-flex align-items-center gap-2">
                        <div>
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
                        <div class="d-flex align-items-center">
                            <label for="tahunSelect" class="form-label me-2 mb-0">Tahun:</label>
                            <select class="form-select form-select-sm" id="tahunSelect" style="width: auto;">
                                @foreach($availableTahun ?? [date('Y')] as $tahun)
                                    <option value="{{ $tahun }}" {{ ($selectedTahun ?? date('Y')) == $tahun ? 'selected' : '' }}>
                                        {{ $tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Alert Sukses/Error --}}
                @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert"> {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button> </div>
                @endif
                @if ($errors->any() && !session('error_modal'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert"> <strong>Error!</strong> Periksa form. <button type="button" class="btn-close" data-bs-dismiss="alert"></button> </div>
                @endif

                {{-- Tab Kegiatan (misal: Sakernas Semester 1, Sakernas Semester 2) --}}
                <ul class="nav nav-pills mb-3 d-flex flex-wrap gap-2" >
                    <li class="nav-item">
                        {{-- Link ke index dengan jenisKegiatan saat ini --}}
                        <a class="nav-link {{ empty($selectedKegiatan) ? 'active' : '' }}"
                           href="{{ route('sosial.semesteran.index', ['jenisKegiatan' => $jenisKegiatan, 'tahun' => $selectedTahun ?? date('Y')]) }}">
                           Semua {{ ucfirst($jenisKegiatan) }}
                        </a>
                    </li>
                    @foreach($kegiatanCounts ?? [] as $kegiatan)
                        <li class="nav-item">
                            <a class="nav-link {{ ($selectedKegiatan ?? '') == $kegiatan->nama_kegiatan ? 'active' : '' }}"
                               href="{{ route('sosial.semesteran.index', ['jenisKegiatan' => $jenisKegiatan, 'kegiatan' => $kegiatan->nama_kegiatan, 'tahun' => $selectedTahun ?? date('Y')]) }}">
                                {{-- Tampilkan nama kegiatan spesifik (misal: Sakernas Semester 1) --}}
                                {{ $kegiatan->nama_kegiatan }} <span class="badge bg-secondary">{{ $kegiatan->total }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Form Pencarian --}}
                <form action="{{ route('sosial.semesteran.index', ['jenisKegiatan' => $jenisKegiatan]) }}" method="GET" class="mb-4">
                    <div class="row g-2 align-items-center">
                        <input type="hidden" name="tahun" value="{{ $selectedTahun ?? date('Y') }}">
                        @if(!empty($selectedKegiatan))
                            <input type="hidden" name="kegiatan" value="{{ $selectedKegiatan }}">
                        @endif
                        <div class="col-md-9 col-12">
                            <input type="text" class="form-control" placeholder="Cari (Kegiatan, BS, Petugas...)" name="search" value="{{ $search ?? '' }}">
                        </div>
                        <div class="col-md-3 col-12">
                            <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i> Cari</button>
                        </div>
                    </div>
                </form>

                {{-- Tabel Data --}}
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
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
                            @forelse ($listData ?? [] as $item)
                                <tr>
                                    {{-- Primary key disesuaikan --}}
                                    <td class="text-center"><input type="checkbox" class="form-check-input row-checkbox" value="{{ $item->id_sosial_semesteran }}"></td>
                                    <td>{{ $item->nama_kegiatan }}</td>
                                    <td>{{ $item->BS_Responden }}</td>
                                    <td>{{ $item->pencacah }}</td>
                                    <td>{{ $item->pengawas }}</td>
                                    {{-- Menggunakan $casts, format() bisa langsung dipakai --}}
                                    <td class="text-center">{{ $item->target_penyelesaian ? $item->target_penyelesaian->format('d/m/Y') : '-' }}</td>
                                    <td class="text-center">
                                         @php
                                            $flag = $item->flag_progress;
                                            $badgeClass = 'bg-secondary'; // Default 'Belum Mulai'
                                            if ($flag === 'Selesai') $badgeClass = 'bg-success';
                                            elseif ($flag === 'Proses') $badgeClass = 'bg-warning text-dark';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $flag }}</span>
                                    </td>
                                     {{-- Menggunakan $casts, format() bisa langsung dipakai --}}
                                    <td class="text-center">{{ $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format('d/m/Y') : '-' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            {{-- Primary key disesuaikan --}}
                                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editDataModal" onclick="editData({{ $item->id_sosial_semesteran }})"><i class="bi bi-pencil-square"></i></button>
                                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteDataModal" onclick="deleteData({{ $item->id_sosial_semesteran }})"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- Pesan disesuaikan --}}
                                    <td colspan="9" class="text-center">Tidak ada data {{ ucfirst($jenisKegiatan) }} yang ditemukan untuk tahun {{ $selectedTahun }}.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
             <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted small"> Displaying {{ $listData->firstItem() ?? 0 }} - {{ $listData->lastItem() ?? 0 }} of {{ $listData->total() ?? 0 }} </div>
                <div> {{ $listData->links() ?? '' }} </div>
            </div>
        </div>
    </div>

    {{-- ================== MODAL TAMBAH ================== --}}
    <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            {{-- Action ke route store yang baru --}}
            <form action="{{ route('sosial.semesteran.store') }}" method="POST" id="tambahForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahDataModalLabel">Tambah Data {{ ucfirst($jenisKegiatan) }} Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Nama Kegiatan (Autocomplete difilter berdasarkan jenisKegiatan) --}}
                    <div class="mb-3 autocomplete-container">
                        <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" placeholder="Ketik {{ ucfirst($jenisKegiatan) }}..." required autocomplete="off">
                        <div class="autocomplete-suggestions" id="kegiatan-suggestions"></div>
                        <div class="invalid-feedback" data-field="nama_kegiatan">@error('nama_kegiatan') {{ $message }} @enderror</div>
                        <small class="form-text text-muted">Contoh: {{ ucfirst($jenisKegiatan) }} Semester 1, {{ ucfirst($jenisKegiatan) }} Semester 2</small>
                    </div>
                    {{-- BS Responden (nullable) --}}
                    <div class="mb-3">
                        <label for="BS_Responden" class="form-label">Blok Sensus/Responden</label>
                        <input type="text" class="form-control @error('BS_Responden') is-invalid @enderror" id="BS_Responden" name="BS_Responden" value="{{ old('BS_Responden') }}">
                        <div class="invalid-feedback" data-field="BS_Responden">@error('BS_Responden'){{ $message }}@enderror</div>
                    </div>
                    {{-- Pencacah --}}
                    <div class="mb-3 autocomplete-container">
                        <label for="pencacah" class="form-label">Pencacah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('pencacah') is-invalid @enderror" id="pencacah" name="pencacah" value="{{ old('pencacah') }}" required autocomplete="off">
                        <div class="autocomplete-suggestions" id="pencacah-suggestions"></div>
                        <div class="invalid-feedback" data-field="pencacah">@error('pencacah'){{ $message }}@enderror</div>
                    </div>
                    {{-- Pengawas --}}
                    <div class="mb-3 autocomplete-container">
                        <label for="pengawas" class="form-label">Pengawas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('pengawas') is-invalid @enderror" id="pengawas" name="pengawas" value="{{ old('pengawas') }}" required autocomplete="off">
                        <div class="autocomplete-suggestions" id="pengawas-suggestions"></div>
                        <div class="invalid-feedback" data-field="pengawas">@error('pengawas'){{ $message }}@enderror</div>
                    </div>
                    {{-- Target Penyelesaian (nullable) --}}
                    <div class="mb-3">
                        <label for="target_penyelesaian" class="form-label">Target Penyelesaian</label>
                        <input type="date" class="form-control @error('target_penyelesaian') is-invalid @enderror" id="target_penyelesaian" name="target_penyelesaian" value="{{ old('target_penyelesaian') }}">
                        <div class="invalid-feedback" data-field="target_penyelesaian">@error('target_penyelesaian'){{ $message }}@enderror</div>
                    </div>
                    {{-- Flag Progress --}}
                    <div class="mb-3">
                        <label for="flag_progress" class="form-label">Flag Progress <span class="text-danger">*</span></label>
                        <select class="form-select @error('flag_progress') is-invalid @enderror" id="flag_progress" name="flag_progress" required>
                            @php $oldFlag = old('flag_progress', 'Belum Mulai'); @endphp
                            @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                <option value="{{ $opt }}" @selected($oldFlag === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" data-field="flag_progress">@error('flag_progress'){{ $message }}@enderror</div>
                    </div>
                    {{-- Tanggal Pengumpulan (nullable) --}}
                    <div class="mb-3">
                        <label for="tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                        <input type="date" class="form-control @error('tanggal_pengumpulan') is-invalid @enderror" id="tanggal_pengumpulan" name="tanggal_pengumpulan" value="{{ old('tanggal_pengumpulan') }}">
                        <div class="invalid-feedback" data-field="tanggal_pengumpulan">@error('tanggal_pengumpulan'){{ $message }}@enderror</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"> <span class="spinner-border spinner-border-sm d-none"></span> Simpan </button>
                </div>
            </div>
            </form>
        </div>
    </div>

    {{-- ================== MODAL EDIT ================== --}}
    <div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST"> {{-- Action diatur JS --}}
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDataModalLabel">Edit Data {{ ucfirst($jenisKegiatan) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                         <input type="hidden" id="edit_id_fallback" value="{{ session('edit_id') ?? '' }}"> {{-- Untuk fallback error --}}

                         <div class="mb-3 autocomplete-container">
                            <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" id="edit_nama_kegiatan" name="nama_kegiatan" required autocomplete="off">
                            <div class="autocomplete-suggestions" id="edit-kegiatan-suggestions"></div>
                            <div class="invalid-feedback" data-field="nama_kegiatan">@error('nama_kegiatan') {{ $message }} @enderror</div>
                            <small class="form-text text-muted">Contoh: {{ ucfirst($jenisKegiatan) }} Semester 1, {{ ucfirst($jenisKegiatan) }} Semester 2</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_BS_Responden" class="form-label">Blok Sensus/Responden</label>
                            <input type="text" class="form-control @error('BS_Responden') is-invalid @enderror" id="edit_BS_Responden" name="BS_Responden">
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
                            <label for="edit_target_penyelesaian" class="form-label">Target Penyelesaian</label>
                            <input type="date" class="form-control @error('target_penyelesaian') is-invalid @enderror" id="edit_target_penyelesaian" name="target_penyelesaian">
                            <div class="invalid-feedback" data-field="target_penyelesaian">@error('target_penyelesaian'){{ $message }}@enderror</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_flag_progress" class="form-label">Flag Progress <span class="text-danger">*</span></label>
                            <select class="form-select @error('flag_progress') is-invalid @enderror" id="edit_flag_progress" name="flag_progress" required>
                                {{-- Opsi disamakan --}}
                                <option value="Belum Mulai">Belum Mulai</option>
                                <option value="Proses">Proses</option>
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
                            <span class="spinner-border spinner-border-sm d-none"></span> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================== MODAL HAPUS ================== --}}
    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
             <form id="deleteForm" method="POST"> @csrf @method('DELETE') <div class="modal-content"> <div class="modal-header"> <h5 class="modal-title">Konfirmasi Hapus</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div> <div class="modal-body" id="deleteModalBody"> Hapus data {{ ucfirst($jenisKegiatan) }} ini? </div> <div class="modal-footer"> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button> <button type="submit" class="btn btn-danger" id="confirmDeleteButton">Hapus</button> </div> </div> </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    /** Autocomplete */
    // ... (Fungsi initAutocomplete tidak perlu diubah) ...
    function initAutocomplete(inputId, suggestionsId, searchUrl) { const input = document.getElementById(inputId); if (!input || !searchUrl) return; const suggestionsContainer = document.getElementById(suggestionsId); let debounceTimer; let activeSuggestionIndex = -1; input.addEventListener('input', function() { const query = this.value; clearTimeout(debounceTimer); if (query.length < 1) { if (suggestionsContainer) suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1; return; } debounceTimer = setTimeout(() => { const finalSearchUrl = `${searchUrl}&query=${encodeURIComponent(query)}`; fetch(finalSearchUrl).then(response => { if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); } return response.json(); }).then(data => { suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1; if (Array.isArray(data)) { data.forEach((item, index) => { const div = document.createElement('div'); div.textContent = item; div.classList.add('autocomplete-suggestion-item'); div.onclick = () => { input.value = item; suggestionsContainer.innerHTML = ''; }; div.onmouseover = () => { document.querySelectorAll(`#${suggestionsId} .autocomplete-suggestion-item`).forEach(el => el.classList.remove('active')); div.classList.add('active'); activeSuggestionIndex = index; }; suggestionsContainer.appendChild(div); }); } else { console.error('Autocomplete data is not an array:', data); } }).catch(error => console.error('Autocomplete error:', error)); }, 300); }); input.addEventListener('keydown', function(e) { const suggestions = suggestionsContainer.querySelectorAll('.autocomplete-suggestion-item'); if (suggestions.length === 0) return; if (e.key === 'ArrowDown') { e.preventDefault(); activeSuggestionIndex = (activeSuggestionIndex + 1) % suggestions.length; updateActiveSuggestion(suggestions, activeSuggestionIndex); } else if (e.key === 'ArrowUp') { e.preventDefault(); activeSuggestionIndex = (activeSuggestionIndex - 1 + suggestions.length) % suggestions.length; updateActiveSuggestion(suggestions, activeSuggestionIndex); } else if (e.key === 'Enter') { if (activeSuggestionIndex > -1) { e.preventDefault(); input.value = suggestions[activeSuggestionIndex].textContent; suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1; } } else if (e.key === 'Escape') { suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1; } }); function updateActiveSuggestion(suggestions, index) { suggestions.forEach(el => el.classList.remove('active')); if (suggestions[index]) suggestions[index].classList.add('active'); } document.addEventListener('click', (e) => { if (e.target.id !== inputId && suggestionsContainer) { suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1; } }); }

    // --- URL Basis BARU untuk Sosial Semesteran ---
    const sosialSemesteranBaseUrl = '/sosial/semesteran';
    // Ambil jenis kegiatan saat ini dari Blade
    const currentJenisKegiatan = '{{ $jenisKegiatan }}';

    /** Edit Data */
    function editData(id) {
        const editModalEl = document.getElementById('editDataModal'); if (!editModalEl) return;
        const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
        const editForm = document.getElementById('editForm');

        // Gunakan URL baru
        editForm.action = `${sosialSemesteranBaseUrl}/${id}`;
        clearFormErrors(editForm);
        document.getElementById('edit_id_fallback').value = id;

        // Gunakan URL baru
        fetch(`${sosialSemesteranBaseUrl}/${id}/edit`)
            .then(response => { if (!response.ok) { return response.text().then(text => { throw new Error(text || 'Data tidak ditemukan'); }); } return response.json(); })
            .then(data => {
                document.getElementById('edit_nama_kegiatan').value = data.nama_kegiatan || '';
                document.getElementById('edit_BS_Responden').value = data.BS_Responden || '';
                document.getElementById('edit_pencacah').value = data.pencacah || '';
                document.getElementById('edit_pengawas').value = data.pengawas || '';
                document.getElementById('edit_target_penyelesaian').value = data.target_penyelesaian || ''; // Sudah Y-m-d
                document.getElementById('edit_flag_progress').value = data.flag_progress || 'Belum Mulai';
                document.getElementById('edit_tanggal_pengumpulan').value = data.tanggal_pengumpulan || ''; // Sudah Y-m-d
                editModal.show();
            })
            .catch(error => { console.error("Error loading edit data:", error); alert('Tidak dapat memuat data Semesteran. Error: ' + error.message); });
    }

    /** Delete Data */
    function deleteData(id) {
        const deleteModalEl = document.getElementById('deleteDataModal'); if (!deleteModalEl) return;
        const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
        const deleteForm = document.getElementById('deleteForm');

        // Gunakan URL baru
        deleteForm.action = `${sosialSemesteranBaseUrl}/${id}`;

        let methodInput = deleteForm.querySelector('input[name="_method"]'); if (!methodInput) { methodInput = document.createElement('input'); methodInput.type = 'hidden'; methodInput.name = '_method'; deleteForm.appendChild(methodInput); } methodInput.value = 'DELETE';
        deleteForm.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
        document.getElementById('deleteModalBody').innerText = `Apakah Anda yakin ingin menghapus data ${currentJenisKegiatan.toUpperCase()} ini?`; // Teks disesuaikan
        const newConfirmButton = document.getElementById('confirmDeleteButton').cloneNode(true); document.getElementById('confirmDeleteButton').parentNode.replaceChild(newConfirmButton, document.getElementById('confirmDeleteButton')); newConfirmButton.addEventListener('click', (e) => { e.preventDefault(); deleteForm.submit(); });
        deleteModal.show();
    }

    /** AJAX Helpers */
    // ... (Fungsi clearFormErrors, showFormErrors, handleFormSubmitAjax tidak perlu diubah) ...
    function clearFormErrors(form) { form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid')); form.querySelectorAll('.invalid-feedback[data-field]').forEach(el => el.textContent = ''); }
    function showFormErrors(form, errors) { for (const [field, messages] of Object.entries(errors)) { const input = form.querySelector(`[name="${field}"]`); const errorDiv = form.querySelector(`.invalid-feedback[data-field="${field}"]`); if (input) input.classList.add('is-invalid'); if (errorDiv) errorDiv.textContent = messages[0]; } }
    async function handleFormSubmitAjax(event, form, modalInstance) { event.preventDefault(); const sb = form.querySelector('button[type="submit"]'); const sp = sb.querySelector('.spinner-border'); sb.disabled = true; if (sp) sp.classList.remove('d-none'); clearFormErrors(form); try { const fd = new FormData(form); const response = await fetch(form.action, { method: form.method, body: fd, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': fd.get('_token') } }); const data = await response.json(); if (!response.ok) { if (response.status === 422 && data.errors) { showFormErrors(form, data.errors); } else { alert(data.message || 'Error.'); } } else { modalInstance.hide(); location.reload(); } } catch (error) { console.error('Fetch error:', error); alert('Tidak terhubung.'); } finally { sb.disabled = false; if (sp) sp.classList.add('d-none'); } }

    /** DOM Ready */
    document.addEventListener('DOMContentLoaded', function() {

        // --- Init Autocomplete ---
        // Gunakan route helper untuk URL yang benar
        @if(Route::has('sosial.semesteran.searchKegiatan'))
            // Tambahkan parameter ?jenis=... ke URL
            const kegiatanSearchUrl = `{{ route("sosial.semesteran.searchKegiatan") }}?jenis=${currentJenisKegiatan}`;
            initAutocomplete('nama_kegiatan', 'kegiatan-suggestions', kegiatanSearchUrl);
            initAutocomplete('edit_nama_kegiatan', 'edit-kegiatan-suggestions', kegiatanSearchUrl);
        @else console.warn('Rute sosial.semesteran.searchKegiatan tidak ditemukan.'); @endif

        @if(Route::has('sosial.semesteran.searchPetugas'))
            const petugasSearchUrl = '{{ route("sosial.semesteran.searchPetugas") }}?'; // Tambah ?
            initAutocomplete('pencacah', 'pencacah-suggestions', petugasSearchUrl);
            initAutocomplete('pengawas', 'pengawas-suggestions', petugasSearchUrl);
            initAutocomplete('edit_pencacah', 'edit-pencacah-suggestions', petugasSearchUrl);
            initAutocomplete('edit_pengawas', 'edit-pengawas-suggestions', petugasSearchUrl);
        @else console.warn('Rute sosial.semesteran.searchPetugas tidak ditemukan.'); @endif


        // --- Init AJAX Form Handlers ---
        const tme = document.getElementById('tambahDataModal'); const tf = document.getElementById('tambahForm'); if (tme && tf) { const tm = bootstrap.Modal.getOrCreateInstance(tme); tf.addEventListener('submit', (e) => handleFormSubmitAjax(e, tf, tm)); tme.addEventListener('hidden.bs.modal', () => { clearFormErrors(tf); tf.reset(); }); }
        const eme = document.getElementById('editDataModal'); const ef = document.getElementById('editForm'); if (eme && ef) { const em = bootstrap.Modal.getOrCreateInstance(eme); ef.addEventListener('submit', (e) => handleFormSubmitAjax(e, ef, em)); eme.addEventListener('hidden.bs.modal', () => clearFormErrors(ef)); }

        // --- Select All & Bulk Delete ---
        const sa = document.getElementById('selectAll'); const rcb = document.querySelectorAll('.row-checkbox'); const bdb = document.getElementById('bulkDeleteBtn'); const df = document.getElementById('deleteForm'); function ubdbs() { const cc = document.querySelectorAll('.row-checkbox:checked').length; if (bdb) bdb.disabled = cc === 0; } sa?.addEventListener('change', () => { rcb.forEach(cb => cb.checked = sa.checked); ubdbs(); }); rcb.forEach(cb => cb.addEventListener('change', ubdbs)); ubdbs(); bdb?.addEventListener('click', () => { const count = document.querySelectorAll('.row-checkbox:checked').length; if (count === 0) return; const dme = document.getElementById('deleteDataModal'); if (!dme || !df) return; const dm = bootstrap.Modal.getOrCreateInstance(dme);
        df.action = '{{ route("sosial.semesteran.bulkDelete") }}'; // Gunakan route baru
        let mi = df.querySelector('input[name="_method"]'); if (mi) mi.value = 'POST'; df.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove()); document.querySelectorAll('.row-checkbox:checked').forEach(cb => { const i = document.createElement('input'); i.type = 'hidden'; i.name = 'ids[]'; i.value = cb.value; df.appendChild(i); }); document.getElementById('deleteModalBody').innerText = `Hapus ${count} data ${currentJenisKegiatan.toUpperCase()}?`; const ncb = document.getElementById('confirmDeleteButton').cloneNode(true); document.getElementById('confirmDeleteButton').parentNode.replaceChild(ncb, document.getElementById('confirmDeleteButton')); ncb.addEventListener('click', (e) => { e.preventDefault(); df.submit(); }); dm.show(); });

        // --- Filters Per Page & Tahun ---
        const pps = document.getElementById('perPageSelect'); const ts = document.getElementById('tahunSelect'); function hfc() { const cu = new URL(window.location.href); const p = cu.searchParams; if (pps) p.set('per_page', pps.value); if (ts) p.set('tahun', ts.value); p.set('page', 1); // Reset ke halaman 1
        // Bangun ulang URL index
        let basePath = '{{ route("sosial.semesteran.index", ["jenisKegiatan" => $jenisKegiatan]) }}';
        window.location.href = basePath + '?' + p.toString();
        } if (pps) pps.addEventListener('change', hfc); if (ts) ts.addEventListener('change', hfc);


        // --- Fallback Error Modals ---
        @if (session('error_modal') == 'tambahDataModal' && $errors->any())
            const tmef = document.getElementById('tambahDataModal');
            if (tmef) bootstrap.Modal.getOrCreateInstance(tmef).show();
        @endif

        @if (session('error_modal') == 'editDataModal' && $errors->any())
            const eid_fb = document.getElementById('edit_id_fallback')?.value;
            if (eid_fb) {
                const emef = document.getElementById('editDataModal');
                if(emef) {
                    const edf = document.getElementById('editForm');
                    edf.action = `${sosialSemesteranBaseUrl}/${eid_fb}`; // Gunakan URL baru
                    bootstrap.Modal.getOrCreateInstance(emef).show();
                    setTimeout(() => {
                        @foreach ($errors->keys() as $f)
                            const fel = edf.querySelector('[name="{{ $f }}"]');
                            if (fel) fel.classList.add('is-invalid');
                            const erel = edf.querySelector(`.invalid-feedback[data-field="{{ $f }}"]`);
                            if (erel) erel.textContent = '{{ $errors->first($f) }}';
                        @endforeach
                    }, 500);
                }
            }
        @endif

        // --- Auto-hide Alerts ---
        @if(session('success') && session('auto_hide'))
            const successAlert = document.querySelector('.alert-success.alert-dismissible');
            if(successAlert && !successAlert.closest('.modal')) {
                setTimeout(() => {
                    bootstrap.Alert.getOrCreateInstance(successAlert).close();
                }, 5000);
            }
        @endif

    });
</script>
@endpush