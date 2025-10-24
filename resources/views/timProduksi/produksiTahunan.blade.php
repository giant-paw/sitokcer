@extends('layouts.app')

@section('title', 'Produksi Tahunan - Sitokcer')
@section('header-title', 'List Target Kegiatan Tahunan Tim Produksi')

{{-- STYLES UNTUK AUTOCOMPLETE --}}
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
                <h4 class="card-title mb-0">LIST TARGET KEGIATAN TAHUNAN TIM PRODUKSI</h4>
            </div>
            <div class="card-body">
                <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#tambahDataModal"><i class="bi bi-plus-circle"></i> Tambah Baru</button>
                        <button type="button" class="btn btn-secondary"><i class="bi bi-upload"></i> Import</button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="bi bi-download"></i> Ekspor Hasil
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-target="#deleteDataModal" id="bulkDeleteBtn"
                            disabled><i class="bi bi-trash"></i> Hapus Data Terpilih</button>
                    </div>

                    {{-- ===== FILTER TAHUN ===== --}}
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <label for="perPageSelect" class="form-label me-2 mb-0">Display:</label>
                            <select class="form-select form-select-sm" id="perPageSelect" style="width: auto;">
                                @php $options = [10, 20, 30, 50, 100, 500, 'all']; @endphp
                                @foreach ($options as $option)
                                    <option value="{{ $option }}"
                                        {{ request('per_page', 20) == $option ? 'selected' : '' }}>
                                        {{ $option == 'all' ? 'All' : $option }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex align-items-center">
                            <label for="tahunSelect" class="form-label me-2 mb-0">Tahun:</label>
                            <select class="form-select form-select-sm" id="tahunSelect" style="width: auto;">
                                {{-- Pastikan $availableTahun dan $selectedTahun dikirim dari Controller --}}
                                @foreach ($availableTahun ?? [date('Y')] as $tahun)
                                    <option value="{{ $tahun }}"
                                        {{ ($selectedTahun ?? date('Y')) == $tahun ? 'selected' : '' }}>
                                        {{ $tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    {{-- ===== AKHIR FILTER TAHUN ===== --}}
                </div>

                {{-- Alert Sukses/Error --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if ($errors->any() && !session('error_modal'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Terjadi Kesalahan!</strong> Mohon periksa kembali isian form Anda.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- ===== TAB KEGIATAN (Filter Tahun Ditambahkan) ===== --}}
                <ul class="nav nav-pills mb-3 d-flex flex-wrap gap-8">
                    <li class="nav-item">
                        <a class="nav-link {{ request('kegiatan') == '' ? 'active' : '' }}"
                            href="{{ route('tim-produksi.tahunan.index', ['tahun' => $selectedTahun ?? date('Y')]) }}">
                            All data
                        </a>
                    </li>
                    {{-- Pastikan $kegiatanCounts dikirim dari Controller --}}
                    @foreach ($kegiatanCounts ?? [] as $kegiatan)
                        <li class="nav-item">
                            <a class="nav-link {{ request('kegiatan') == $kegiatan->nama_kegiatan ? 'active' : '' }}"
                                href="{{ route('tim-produksi.tahunan.index', ['kegiatan' => $kegiatan->nama_kegiatan, 'tahun' => $selectedTahun ?? date('Y')]) }}">
                                {{ $kegiatan->nama_kegiatan }} <span
                                    class="badge bg-secondary">{{ $kegiatan->total }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- ===== FORM PENCARIAN (Filter Tahun Ditambahkan) ===== --}}
                <form action="{{ route('tim-produksi.tahunan.index') }}" method="GET" class="mb-4">
                    <div class="row g-2 align-items-center">
                        <input type="hidden" name="tahun" value="{{ $selectedTahun ?? date('Y') }}">
                        @if (request('kegiatan'))
                            <input type="hidden" name="kegiatan" value="{{ request('kegiatan') }}">
                        @endif
                        <div class="col-md-9 col-12">
                            <input type="text" class="form-control" placeholder="Cari..." name="search"
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 col-12">
                            <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i> Cari</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                                <th scope="col">Nama Kegiatan</th>
                                <th scope="col">Blok Sensus/Responden</th>
                                <th scope="col">Pencacah</th>
                                <th scope="col">Pengawas</th>
                                <th scope="col">Target Penyelesaian</th>
                                <th scope="col">Flag Progress</th>
                                <th scope="col">Tanggal Pengumpulan</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Pastikan $listData dikirim dari Controller --}}
                            @forelse ($listData ?? [] as $item)
                                <tr>
                                    <td><input type="checkbox" class="form-check-input row-checkbox"
                                            value="{{ $item->id_produksi }}"></td>
                                    <td>{{ $item->nama_kegiatan }}</td>
                                    <td>{{ $item->BS_Responden }}</td>
                                    <td>{{ $item->pencacah }}</td>
                                    <td>{{ $item->pengawas }}</td>
                                    {{-- Format Tanggal dari Model Casting --}}
                                    <td>{{ $item->target_penyelesaian ? $item->target_penyelesaian->format('d/m/Y') : '-' }}
                                    </td>
                                    <td><span
                                            class="badge {{ $item->flag_progress == 'Selesai' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $item->flag_progress }}</span>
                                    </td>
                                    {{-- Format Tanggal dari Model Casting --}}
                                    <td>{{ $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="d-flex gap-2">
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#editDataModal"
                                            onclick="editData({{ $item->id_produksi }})"><i
                                                class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteDataModal"
                                            onclick="deleteData({{ $item->id_produksi }})"><i
                                                class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data yang ditemukan.</td>
                                    {{-- Sesuaikan colspan --}}
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Displaying {{ $listData->firstItem() ?? 0 }} - {{ $listData->lastItem() ?? 0 }} of
                    {{ $listData->total() ?? 0 }}
                </div>
                <div>
                    {{ $listData->links() ?? '' }}
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Ekspor -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('tim-produksi.tahunan.export') }}" method="GET">
                    @csrf
                    <input type="hidden" name="kegiatan" value="{{ request('kegiatan') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="tahun" value="{{ request('tahun', date('Y')) }}">
                    <input type="hidden" name="page" value="{{ request('page', 1) }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportModalLabel">Ekspor Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Jangkauan Data -->
                        <div class="mb-3">
                            <label for="dataRange" class="form-label">Jangkauan Data</label>
                            <select class="form-select" id="dataRange" name="dataRange" required>
                                <option value="all">Semua Catatan</option>
                                <option value="current_page">Hanya Halaman Terkini</option>
                            </select>
                        </div>

                        <!-- Filter Kegiatan -->
                        <input type="hidden" name="kegiatan" value="{{ request('kegiatan') }}">

                        <!-- Format Data -->
                        <div class="mb-3">
                            <label for="dataFormat" class="form-label">Format Data</label>
                            <select class="form-select" id="dataFormat" name="dataFormat" required>
                                <option value="formatted_values">Formatted Values</option>
                                <option value="raw_values">Raw Values</option>
                            </select>
                        </div>

                        <!-- Format Keluaran -->
                        <div class="mb-3">
                            <label for="exportFormat" class="form-label">Format Keluaran</label>
                            <select class="form-select" id="exportFormat" name="exportFormat" required>
                                <option value="excel">Excel 2007</option>
                                <option value="csv">CSV (Nilai Terpisah Koma)</option>
                                <option value="word">Word (.docx)</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Ekspor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('tim-produksi.tahunan.store') }}" method="POST" id="tambahForm">
                {{-- ID Form & Rute --}}
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahDataModalLabel">Tambah Data Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Pastikan $masterKegiatanList dikirim dari Controller --}}
                        <div class="mb-3 autocomplete-container">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}"
                                placeholder="Ketik untuk mencari..." required autocomplete="off">
                            <div class="autocomplete-suggestions" id="kegiatan-suggestions"></div>
                            <div class="invalid-feedback" data-field="nama_kegiatan">
                                @error('nama_kegiatan')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="BS_Responden" class="form-label">Blok Sensus/Responden <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('BS_Responden') is-invalid @enderror"
                                id="BS_Responden" name="BS_Responden" value="{{ old('BS_Responden') }}" required>
                            <div class="invalid-feedback" data-field="BS_Responden">
                                @error('BS_Responden')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3 autocomplete-container">
                            <label for="pencacah" class="form-label">Pencacah <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pencacah') is-invalid @enderror"
                                id="pencacah" name="pencacah" value="{{ old('pencacah') }}" required
                                autocomplete="off">
                            <div class="autocomplete-suggestions" id="pencacah-suggestions"></div>
                            <div class="invalid-feedback" data-field="pencacah">
                                @error('pencacah')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3 autocomplete-container">
                            <label for="pengawas" class="form-label">Pengawas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pengawas') is-invalid @enderror"
                                id="pengawas" name="pengawas" value="{{ old('pengawas') }}" required
                                autocomplete="off">
                            <div class="autocomplete-suggestions" id="pengawas-suggestions"></div>
                            <div class="invalid-feedback" data-field="pengawas">
                                @error('pengawas')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="target_penyelesaian" class="form-label">Target Penyelesaian <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('target_penyelesaian') is-invalid @enderror"
                                id="target_penyelesaian" name="target_penyelesaian"
                                value="{{ old('target_penyelesaian') }}" required>
                            <div class="invalid-feedback" data-field="target_penyelesaian">
                                @error('target_penyelesaian')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="flag_progress" class="form-label">Flag Progress <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('flag_progress') is-invalid @enderror" id="flag_progress"
                                name="flag_progress" required>
                                <option value="Belum Selesai"
                                    {{ old('flag_progress') == 'Belum Selesai' ? 'selected' : '' }}>Belum Selesai</option>
                                <option value="Selesai" {{ old('flag_progress') == 'Selesai' ? 'selected' : '' }}>Selesai
                                </option>
                            </select>
                            <div class="invalid-feedback" data-field="flag_progress">
                                @error('flag_progress')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            {{-- Sesuaikan label dan required (nullable di controller) --}}
                            <label for="tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                            <input type="date" class="form-control @error('tanggal_pengumpulan') is-invalid @enderror"
                                id="tanggal_pengumpulan" name="tanggal_pengumpulan"
                                value="{{ old('tanggal_pengumpulan') }}">
                            <div class="invalid-feedback" data-field="tanggal_pengumpulan">
                                @error('tanggal_pengumpulan')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                            Simpan
                        </button>
                    </div>
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
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDataModalLabel">Edit Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        {{-- Nama Kegiatan --}}
                        <div class="mb-3 autocomplete-container">
                            <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                id="edit_nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" required
                                autocomplete="off">
                            <div class="autocomplete-suggestions" id="edit-kegiatan-suggestions"></div>
                            <div class="invalid-feedback" data-field="nama_kegiatan">
                                @error('nama_kegiatan')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>

                        {{-- BS Responden --}}
                        <div class="mb-3">
                            <label for="edit_BS_Responden" class="form-label">Blok Sensus/Responden <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('BS_Responden') is-invalid @enderror"
                                id="edit_BS_Responden" name="BS_Responden" value="{{ old('BS_Responden') }}" required>
                            <div class="invalid-feedback" data-field="BS_Responden">
                                @error('BS_Responden')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>

                        {{-- Pencacah --}}
                        <div class="mb-3 autocomplete-container">
                            <label for="edit_pencacah" class="form-label">Pencacah <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pencacah') is-invalid @enderror"
                                id="edit_pencacah" name="pencacah" value="{{ old('pencacah') }}" required
                                autocomplete="off">
                            <div class="autocomplete-suggestions" id="edit-pencacah-suggestions"></div>
                            <div class="invalid-feedback" data-field="pencacah">
                                @error('pencacah')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>

                        {{-- Pengawas --}}
                        <div class="mb-3 autocomplete-container">
                            <label for="edit_pengawas" class="form-label">Pengawas <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pengawas') is-invalid @enderror"
                                id="edit_pengawas" name="pengawas" value="{{ old('pengawas') }}" required
                                autocomplete="off">
                            <div class="autocomplete-suggestions" id="edit-pengawas-suggestions"></div>
                            <div class="invalid-feedback" data-field="pengawas">
                                @error('pengawas')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>

                        {{-- Target Penyelesaian --}}
                        <div class="mb-3">
                            <label for="edit_target_penyelesaian" class="form-label">Tanggal Target Penyelesaian <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('target_penyelesaian') is-invalid @enderror"
                                id="edit_target_penyelesaian" name="target_penyelesaian"
                                value="{{ old('target_penyelesaian') }}" required>
                            <div class="invalid-feedback" data-field="target_penyelesaian">
                                @error('target_penyelesaian')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>

                        {{-- Flag Progress --}}
                        <div class="mb-3">
                            <label for="edit_flag_progress" class="form-label">Flag Progress <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('flag_progress') is-invalid @enderror"
                                id="edit_flag_progress" name="flag_progress" required>

                                {{-- Tambahkan logic 'selected' di sini --}}
                                <option value="Belum Selesai"
                                    {{ old('flag_progress') == 'Belum Selesai' ? 'selected' : '' }}>Belum Selesai</option>
                                <option value="Selesai" {{ old('flag_progress') == 'Selesai' ? 'selected' : '' }}>Selesai
                                </option>

                            </select>
                            <div class="invalid-feedback" data-field="flag_progress">
                                @error('flag_progress')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>

                        {{-- Tanggal Pengumpulan --}}
                        <div class="mb-3">
                            <label for="edit_tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                            <input type="date" class="form-control @error('tanggal_pengumpulan') is-invalid @enderror"
                                id="edit_tanggal_pengumpulan" name="tanggal_pengumpulan"
                                value="{{ old('tanggal_pengumpulan') }}">
                            <div class="invalid-feedback" data-field="tanggal_pengumpulan">
                                @error('tanggal_pengumpulan')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true"></span>
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="deleteForm" method="POST"> @csrf @method('DELETE') <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5> <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="deleteModalBody"> Hapus data ini? </div>
                    <div class="modal-footer"> <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">Batal</button> <button type="submit" class="btn btn-danger"
                            id="confirmDeleteButton">Hapus</button> </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        /** Autocomplete */
        function initAutocomplete(inputId, suggestionsId, searchUrl) {
            // ... (Fungsi ini sudah SANGAT BAGUS, tidak perlu diubah) ...
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
                    fetch(finalSearchUrl).then(response => response.json()).then(data => {
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
                                document.querySelectorAll(
                                    `#${suggestionsId} .autocomplete-suggestion-item`
                                ).forEach(el => el.classList.remove('active'));
                                div.classList.add('active');
                                activeSuggestionIndex = index;
                            };
                            suggestionsContainer.appendChild(div);
                        });
                    }).catch(error => console.error('Autocomplete error:', error));
                }, 300);
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
                if (suggestions[index]) suggestions[index].classList.add('active');
            }
            document.addEventListener('click', (e) => {
                if (e.target.id !== inputId && suggestionsContainer) {
                    suggestionsContainer.innerHTML = '';
                    activeSuggestionIndex = -1;
                }
            });
        }

        // --- PERBAIKAN 1: Tentukan URL basis baru ---
        const produksiTahunanBaseUrl = '/tim-produksi/tahunan';

        /** Edit Data */
        function editData(id) {
            const editModalEl = document.getElementById('editDataModal');
            if (!editModalEl) return;
            const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
            const editForm = document.getElementById('editForm');

            // --- PERBAIKAN 2: Gunakan URL baru ---
            editForm.action = `${produksiTahunanBaseUrl}/${id}`;
            clearFormErrors(editForm);

            // --- PERBAIKAN 2: Gunakan URL baru ---
            fetch(`${produksiTahunanBaseUrl}/${id}/edit`)
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

                    // --- PERBAIKAN 3: Sederhanakan (karena controller sudah kirim Y-m-d) ---
                    document.getElementById('edit_target_penyelesaian').value = data.target_penyelesaian || '';
                    document.getElementById('edit_flag_progress').value = data.flag_progress || 'Belum Selesai';
                    document.getElementById('edit_tanggal_pengumpulan').value = data.tanggal_pengumpulan || '';

                    editModal.show();
                })
                .catch(error => {
                    console.error("Error loading edit data:", error);
                    alert('Tidak dapat memuat data. Error: ' + error.message);
                });
        }

        /** Delete Data */
        function deleteData(id) {
            const deleteModalEl = document.getElementById('deleteDataModal');
            if (!deleteModalEl) return;
            const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
            const deleteForm = document.getElementById('deleteForm');

            // --- PERBAIKAN 2: Gunakan URL baru ---
            deleteForm.action = `${produksiTahunanBaseUrl}/${id}`;

            let methodInput = deleteForm.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                deleteForm.appendChild(methodInput);
            }
            methodInput.value = 'DELETE';
            deleteForm.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
            document.getElementById('deleteModalBody').innerText = 'Apakah Anda yakin ingin menghapus data ini?';
            const newConfirmButton = document.getElementById('confirmDeleteButton').cloneNode(true);
            document.getElementById('confirmDeleteButton').parentNode.replaceChild(newConfirmButton, document
                .getElementById('confirmDeleteButton'));
            newConfirmButton.addEventListener('click', (e) => {
                e.preventDefault();
                deleteForm.submit();
            });
            deleteModal.show();
        }

        /** AJAX Helpers */
        // ... (Fungsi clearFormErrors, showFormErrors, handleFormSubmitAjax sudah SANGAT BAGUS, tidak perlu diubah) ...
        function clearFormErrors(form) {
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('.invalid-feedback[data-field]').forEach(el => el.textContent = '');
        }

        function showFormErrors(form, errors) {
            for (const [field, messages] of Object.entries(errors)) {
                const input = form.querySelector(`[name="${field}"]`);
                const errorDiv = form.querySelector(`.invalid-feedback[data-field="${field}"]`);
                if (input) input.classList.add('is-invalid');
                if (errorDiv) errorDiv.textContent = messages[0];
            }
        }
        async function handleFormSubmitAjax(event, form, modalInstance) {
            event.preventDefault();
            const sb = form.querySelector('button[type="submit"]');
            const sp = sb.querySelector('.spinner-border');
            sb.disabled = true;
            if (sp) sp.classList.remove('d-none');
            clearFormErrors(form);
            try {
                const fd = new FormData(form);
                const response = await fetch(form.action, {
                    method: form.method,
                    body: fd,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': fd.get('_token')
                    }
                });
                const data = await response.json();
                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        showFormErrors(form, data.errors);
                    } else {
                        alert(data.message || 'Error.');
                    }
                } else {
                    modalInstance.hide();
                    location.reload();
                }
            } catch (error) {
                console.error('Fetch error:', error);
                alert('Tidak terhubung.');
            } finally {
                sb.disabled = false;
                if (sp) sp.classList.add('d-none');
            }
        }

        /** DOM Ready */
        document.addEventListener('DOMContentLoaded', function() {

            // --- Init Autocomplete ---
            @if (Route::has('master.kegiatan.search'))
                initAutocomplete('nama_kegiatan', 'kegiatan-suggestions', '{{ route('master.kegiatan.search') }}');
                initAutocomplete('edit_nama_kegiatan', 'edit-kegiatan-suggestions',
                    '{{ route('master.kegiatan.search') }}');
            @else
                console.warn('Rute kegiatan search tidak ada.');
            @endif
            @if (Route::has('tim-produksi.tahunan.searchPetugas'))
                // Gunakan rute searchPetugas yang benar
                const petugasSearchUrl = '{{ route('tim-produksi.tahunan.searchPetugas') }}';
                initAutocomplete('pencacah', 'pencacah-suggestions', petugasSearchUrl);
                initAutocomplete('pengawas', 'pengawas-suggestions', petugasSearchUrl);
                initAutocomplete('edit_pencacah', 'edit-pencacah-suggestions', petugasSearchUrl);
                initAutocomplete('edit_pengawas', 'edit-pengawas-suggestions', petugasSearchUrl);
            @else
                console.warn('Rute tim-produksi.tahunan.searchPetugas tidak ada.');
            @endif

            // --- Init AJAX Form Handlers ---
            // (Logika ini sudah benar)
            const tme = document.getElementById('tambahDataModal');
            const tf = document.getElementById('tambahForm');
            if (tme && tf) {
                const tm = bootstrap.Modal.getOrCreateInstance(tme);
                tf.addEventListener('submit', (e) => handleFormSubmitAjax(e, tf, tm));
                tme.addEventListener('hidden.bs.modal', () => {
                    clearFormErrors(tf);
                    tf.reset();
                });
            }
            const eme = document.getElementById('editDataModal');
            const ef = document.getElementById('editForm');
            if (eme && ef) {
                const em = bootstrap.Modal.getOrCreateInstance(eme);
                ef.addEventListener('submit', (e) => handleFormSubmitAjax(e, ef, em));
                eme.addEventListener('hidden.bs.modal', () => clearFormErrors(ef));
            }

            // --- Select All & Bulk Delete ---
            // (Logika ini sudah benar, pastikan route() helper-nya)
            const sa = document.getElementById('selectAll');
            const rcb = document.querySelectorAll('.row-checkbox');
            const bdb = document.getElementById('bulkDeleteBtn');
            const df = document.getElementById('deleteForm');

            function ubdbs() {
                const cc = document.querySelectorAll('.row-checkbox:checked').length;
                if (bdb) bdb.disabled = cc === 0;
            }
            sa?.addEventListener('change', () => {
                rcb.forEach(cb => cb.checked = sa.checked);
                ubdbs();
            });
            rcb.forEach(cb => cb.addEventListener('change', ubdbs));
            ubdbs();
            bdb?.addEventListener('click', () => {
                const count = document.querySelectorAll('.row-checkbox:checked').length;
                if (count === 0) return;
                const dme = document.getElementById('deleteDataModal');
                if (!dme || !df) return;
                const dm = bootstrap.Modal.getOrCreateInstance(dme);
                df.action = '{{ route('tim-produksi.tahunan.bulkDelete') }}';
                let mi = df.querySelector('input[name="_method"]');
                if (mi) mi.value = 'POST';
                df.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
                document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                    const i = document.createElement('input');
                    i.type = 'hidden';
                    i.name = 'ids[]';
                    i.value = cb.value;
                    df.appendChild(i);
                });
                document.getElementById('deleteModalBody').innerText = `Hapus ${count} data?`;
                const ncb = document.getElementById('confirmDeleteButton').cloneNode(true);
                document.getElementById('confirmDeleteButton').parentNode.replaceChild(ncb, document
                    .getElementById('confirmDeleteButton'));
                ncb.addEventListener('click', (e) => {
                    e.preventDefault();
                    df.submit();
                });
                dm.show();
            });

            // --- Filters Per Page & Tahun ---
            // (Logika ini sudah benar)
            const pps = document.getElementById('perPageSelect');
            const ts = document.getElementById('tahunSelect');

            function hfc() {
                const cu = new URL(window.location.href);
                const p = cu.searchParams;
                if (pps) p.set('per_page', pps.value);
                if (ts) p.set('tahun', ts.value);
                p.set('page', 1);
                window.location.href = cu.pathname + '?' + p.toString();
            }
            if (pps) pps.addEventListener('change', hfc);
            if (ts) ts.addEventListener('change', hfc);


            // --- PERBAIKAN 4: Fallback Error Modals ---

            @if (session('error_modal') == 'tambahDataModal' && $errors->any())
                const tmef = document.getElementById('tambahDataModal');
                if (tmef) bootstrap.Modal.getOrCreateInstance(tmef).show();
            @endif

            @if (session('error_modal') == 'editDataModal' && $errors->any() && session('edit_id'))
                const eid = {{ session('edit_id') }};
                if (eid) {
                    const editModalEl = document.getElementById('editDataModal');
                    const editForm = document.getElementById('editForm');

                    if (editForm) {
                        // 1. Set action form ke URL update yang benar
                        editForm.action = `${produksiTahunanBaseUrl}/${eid}`;

                        // 2. Terapkan error classes & messages dari Blade
                        @foreach ($errors->keys() as $f)
                            const fel = editForm.querySelector('[name="{{ $f }}"]');
                            if (fel) fel.classList.add('is-invalid');

                            const erel = editForm.querySelector(
                                `.invalid-feedback[data-field="{{ $f }}"]`);
                            if (erel) erel.textContent = '{{ $errors->first($f) }}';
                        @endforeach
                    }

                    // 3. Tampilkan modal. 
                    if (editModalEl) {
                        bootstrap.Modal.getOrCreateInstance(editModalEl).show();
                    }
                }
            @endif

            // SCRIPT UNTUK EXPORT DATA
            // ========================================
            document.getElementById('exportForm').addEventListener('submit', function(e) {
                const dataRange = document.getElementById('dataRange').value;
                const exportFormat = document.getElementById('exportFormat').value;
                // Validasi input
                if (!dataRange) {
                    e.preventDefault();
                    alert('Silakan pilih range data yang akan di-export!');
                    return false;
                }
                if (!exportFormat) {
                    e.preventDefault();
                    alert('Silakan pilih format export!');
                    return false;
                }
                // Update hidden inputs dengan nilai terbaru dari filter aktif
                document.getElementById('export_tahun').value = '{{ $selectedTahun }}';
                document.getElementById('export_kegiatan').value = '{{ request('kegiatan') }}';
                document.getElementById('export_search').value = '{{ request('search') }}';
                document.getElementById('export_page').value = '{{ $listData->currentPage() }}';
                document.getElementById('export_per_page').value = '{{ $listData->perPage() }}';
                // Biarkan form submit secara normal (GET request)
                return true;
            });
            // Update jumlah data pada dropdown saat modal dibuka
            document.getElementById('exportDataModal').addEventListener('show.bs.modal', function() {
                const currentPageOption = document.querySelector('#dataRange option[value="current_page"]');
                const totalData = {{ $listData->total() }};
                const currentPageData = {{ $listData->count() }};

                currentPageOption.textContent = `Halaman Saat Ini (${currentPageData} data)`;

                const allDataOption = document.querySelector('#dataRange option[value="all"]');
                allDataOption.textContent = `Semua Data yang Difilter (${totalData} data)`;
            });


            // --- Auto-hide Alerts ---
            @if (session('success') && session('auto_hide'))
                const successAlert = document.querySelector('.alert-success.alert-dismissible');
                if (successAlert && !successAlert.closest('.modal')) {
                    setTimeout(() => {
                        bootstrap.Alert.getOrCreateInstance(successAlert).close();
                    }, 5000);
                }
            @endif

        });
    </script>
@endpush
