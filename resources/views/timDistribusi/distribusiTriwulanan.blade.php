@extends('layouts.app')

{{-- Judul Disesuaikan --}}
@section('title', 'Distribusi Triwulanan (' . strtoupper($jenisKegiatan) . ') - Sitokcer')
@section('header-title', 'List Target Kegiatan ' . strtoupper($jenisKegiatan))

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
        {{-- Alert Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        {{-- Alert Error --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        {{-- Alert Import Errors - TARUH DI SINI --}}
        @if (session('import_errors'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <strong>Beberapa baris gagal diimport:</strong>
                <ul class="mb-0">
                    @foreach (session('import_errors') as $error)
                        <li>{{ $error['error'] }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('import_errors'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert" id="importErrorAlert">
                <strong>Beberapa baris gagal diimport:</strong>
                <ul class="mb-0">
                    @foreach (session('import_errors') as $error)
                        <li>{{ $error['error'] }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @push('scripts')
            <script>
                // Auto hide setelah 10 detik
                setTimeout(function() {
                    $('#importErrorAlert').fadeOut('slow');
                }, 10000);
            </script>
        @endpush
        <div class="card">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">LIST TARGET KEGIATAN TRIWULANAN: {{ strtoupper($jenisKegiatan) }}</h4>
            </div>
            <div class="card-body">
                <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#tambahDataModal"><i class="bi bi-plus-circle"></i> Tambah Baru</button>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                            <i class="bi bi-upload"></i> Import
                        </button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#exportModal"><i class="bi bi-download"></i> Ekspor Hasil</button>
                        <button type="button" class="btn btn-danger" data-bs-target="#deleteDataModal" id="bulkDeleteBtn"
                            disabled><i class="bi bi-trash"></i> Hapus Data Terpilih</button>
                    </div>

                    {{-- Filter Tahun & Per Page --}}
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
                                @foreach ($availableTahun ?? [date('Y')] as $tahun)
                                    <option value="{{ $tahun }}"
                                        {{ ($selectedTahun ?? date('Y')) == $tahun ? 'selected' : '' }}>{{ $tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Alert Sukses/Error --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert"> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @elseif (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert"> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if ($errors->any() && !session('error_modal'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert"> <strong>Error!</strong>
                        Periksa form.<button type="button" class="btn-close" data-bs-dismiss="alert"></button> </div>
                @endif

                {{-- Tab Kegiatan (misal: SPUNP-TW1, SHKK-TW3) --}}
                <ul class="nav nav-pills mb-3 d-flex flex-wrap gap-2">
                    <li class="nav-item">
                        <a class="nav-link {{ empty($selectedKegiatan) ? 'active' : '' }}"
                            href="{{ route('tim-distribusi.triwulanan.index', ['jenisKegiatan' => $jenisKegiatan, 'tahun' => $selectedTahun ?? date('Y')]) }}">
                            Semua TW ({{ strtoupper($jenisKegiatan) }})
                        </a>
                    </li>
                    @foreach ($kegiatanCounts ?? [] as $kegiatan)
                        <li class="nav-item">
                            <a class="nav-link {{ ($selectedKegiatan ?? '') == $kegiatan->nama_kegiatan ? 'active' : '' }}"
                                href="{{ route('tim-distribusi.triwulanan.index', ['jenisKegiatan' => $jenisKegiatan, 'kegiatan' => $kegiatan->nama_kegiatan, 'tahun' => $selectedTahun ?? date('Y')]) }}">
                                {{ $kegiatan->nama_kegiatan }} <span
                                    class="badge bg-secondary">{{ $kegiatan->total }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Form Pencarian --}}
                <form action="{{ route('tim-distribusi.triwulanan.index', ['jenisKegiatan' => $jenisKegiatan]) }}"
                    method="GET" class="mb-4">
                    <div class="row g-2 align-items-center">
                        <input type="hidden" name="tahun" value="{{ $selectedTahun ?? date('Y') }}">
                        @if (!empty($selectedKegiatan))
                            <input type="hidden" name="kegiatan" value="{{ $selectedKegiatan }}">
                        @endif
                        <div class="col-md-9 col-12">
                            <input type="text" class="form-control" placeholder="Cari Nama Kegiatan, BS, Petugas..."
                                name="search" value="{{ $search ?? '' }}">
                        </div>
                        <div class="col-md-3 col-12">
                            <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i>
                                Cari</button>
                        </div>
                    </div>
                </form>

                {{-- Tabel Data --}}
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 1%;"><input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
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
                                    <td class="text-center"><input type="checkbox" class="form-check-input row-checkbox"
                                            value="{{ $item->id_distribusi_triwulanan }}"></td>
                                    <td>{{ $item->nama_kegiatan }}</td>
                                    <td>{{ $item->BS_Responden }}</td>
                                    <td>{{ $item->pencacah }}</td>
                                    <td>{{ $item->pengawas }}</td>
                                    {{-- Menggunakan $casts, format() bisa langsung dipakai --}}
                                    <td class="text-center">
                                        {{ $item->target_penyelesaian ? $item->target_penyelesaian->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $flag = $item->flag_progress;
                                            $badgeClass = $flag == 'Selesai' ? 'bg-success' : 'bg-warning text-dark';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $flag }}</span>
                                    </td>
                                    {{-- Menggunakan $casts, format() bisa langsung dipakai --}}
                                    <td class="text-center">
                                        {{ $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            {{-- Primary key disesuaikan --}}
                                            <button class="btn btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#editDataModal"
                                                onclick="editData({{ $item->id_distribusi_triwulanan }})"><i
                                                    class="bi bi-pencil-square"></i></button>
                                            <button class="btn btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#deleteDataModal"
                                                onclick="deleteData({{ $item->id_distribusi_triwulanan }})"><i
                                                    class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data
                                        {{ strtoupper($jenisKegiatan) }} yang ditemukan untuk tahun {{ $selectedTahun }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted small"> Displaying {{ $listData->firstItem() ?? 0 }} -
                    {{ $listData->lastItem() ?? 0 }} of {{ $listData->total() ?? 0 }} </div>
                <div> {{ $listData->links() ?? '' }} </div>
            </div>
        </div>
    </div>
    <!-- Modal Import Data -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('tim-distribusi.triwulanan.import') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Data dari Excel</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <!-- Alert info -->
                        <div class="alert alert-info" role="alert">
                            <small>
                                <strong>Format yang didukung:</strong> Excel (.xlsx, .xls) atau CSV<br>
                                <strong>Ukuran maksimal:</strong> 10 MB<br>
                                <strong>Catatan:</strong> ID akan di-generate otomatis
                            </small>
                        </div>
                        <!-- Download template -->
                        <div class="mb-3">
                            <a href="{{ route('tim-distribusi.triwulanan.downloadTemplate') }}"
                                class="btn btn-sm btn-secondary">
                                <i class="bi bi-download"></i> Download Template Excel
                            </a>
                        </div>
                        <!-- File input -->
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Pilih File</label>
                            <input type="file" class="form-control" id="importFile" name="file" required
                                accept=".xlsx,.xls,.csv">
                            <div class="form-text">
                                Pastikan format kolom sesuai dengan template
                            </div>
                        </div>
                        <!-- Preview area (optional) -->
                        <div id="filePreview" class="d-none">
                            <small class="text-muted">File dipilih: <span id="fileName"></span></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- ================== MODAL EXPORT ================== --}}
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                {{-- Action ke route export BARU --}}
                <form action="{{ route('tim-distribusi.triwulanan.export', ['jenisKegiatan' => $jenisKegiatan]) }}"
                    method="GET" id="exportForm">
                    {{-- Hapus @csrf --}}
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportModalLabel">Ekspor Data {{ strtoupper($jenisKegiatan) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Hidden inputs untuk filter aktif --}}
                        <input type="hidden" name="tahun" id="export_tahun"
                            value="{{ $selectedTahun ?? date('Y') }}">
                        <input type="hidden" name="kegiatan" id="export_kegiatan"
                            value="{{ $selectedKegiatan ?? '' }}">
                        <input type="hidden" name="search" id="export_search" value="{{ $search ?? '' }}">
                        <input type="hidden" name="page" id="export_page" value="{{ $listData->currentPage() }}">
                        <input type="hidden" name="per_page" id="export_per_page"
                            value="{{ request('per_page', $listData->perPage()) }}">

                        <div class="mb-3">
                            <label for="dataRange" class="form-label">Jangkauan Data</label>
                            <select class="form-select" id="dataRange" name="dataRange" required>
                                <option value="all">Semua Data ({{ $listData->total() }} record)</option>
                                <option value="current_page">Hanya Halaman Ini ({{ $listData->count() }} record)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="dataFormat" class="form-label">Format Nilai Tanggal/Lainnya</label>
                            <select class="form-select" id="dataFormat" name="dataFormat">
                                <option value="formatted_values" selected>Gunakan Format Tampilan (misal: dd/mm/yyyy)
                                </option>
                                <option value="raw_values">Gunakan Nilai Asli Database (misal: yyyy-mm-dd)</option>
                            </select>
                            <small class="form-text text-muted">Pilih "Raw Values" jika ingin mengolah data lebih
                                lanjut.</small>
                        </div>
                        <div class="mb-3">
                            <label for="exportFormat" class="form-label">Format File Export</label>
                            <select class="form-select" id="exportFormat" name="exportFormat" required>
                                <option value="excel">Excel (.xlsx)</option>
                                <option value="csv">CSV (.csv)</option>
                                {{-- <option value="word">Word (.docx)</option> --}}
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

    {{-- ================== MODAL TAMBAH ================== --}}
    <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            {{-- Action ke route store yang baru --}}
            <form action="{{ route('tim-distribusi.triwulanan.store') }}" method="POST" id="tambahForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tambahDataModalLabel">Tambah Data {{ strtoupper($jenisKegiatan) }}
                            Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Nama Kegiatan (Autocomplete) --}}
                        <div class="mb-3 autocomplete-container">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}"
                                placeholder="Ketik {{ strtoupper($jenisKegiatan) }}-TW..." required autocomplete="off">
                            <div class="autocomplete-suggestions" id="kegiatan-suggestions"></div>
                            <div class="invalid-feedback" data-field="nama_kegiatan">
                                @error('nama_kegiatan')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        {{-- BS Responden --}}
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
                        {{-- Pencacah (Autocomplete) --}}
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
                        {{-- Pengawas (Autocomplete) --}}
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
                        {{-- Target Penyelesaian --}}
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
                        {{-- Flag Progress --}}
                        <div class="mb-3">
                            <label for="flag_progress" class="form-label">Flag Progress <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('flag_progress') is-invalid @enderror" id="flag_progress"
                                name="flag_progress" required>
                                <option value="Belum Selesai" @selected(old('flag_progress', 'Belum Selesai') == 'Belum Selesai')>Belum Selesai</option>
                                <option value="Selesai" @selected(old('flag_progress') == 'Selesai')>Selesai</option>
                            </select>
                            <div class="invalid-feedback" data-field="flag_progress">
                                @error('flag_progress')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        {{-- Tanggal Pengumpulan --}}
                        <div class="mb-3">
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
                        <button type="submit" class="btn btn-primary"> <span
                                class="spinner-border spinner-border-sm d-none"></span> Simpan </button>
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
                        <h5 class="modal-title" id="editDataModalLabel">Edit Data {{ strtoupper($jenisKegiatan) }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="edit_id_fallback"> {{-- Untuk fallback error --}}

                        <div class="mb-3 autocomplete-container">
                            <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                id="edit_nama_kegiatan" name="nama_kegiatan" required autocomplete="off">
                            <div class="autocomplete-suggestions" id="edit-kegiatan-suggestions"></div>
                            <div class="invalid-feedback" data-field="nama_kegiatan">
                                @error('nama_kegiatan')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_BS_Responden" class="form-label">Blok Sensus/Responden <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('BS_Responden') is-invalid @enderror"
                                id="edit_BS_Responden" name="BS_Responden" required>
                            <div class="invalid-feedback" data-field="BS_Responden">
                                @error('BS_Responden')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3 autocomplete-container">
                            <label for="edit_pencacah" class="form-label">Pencacah <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pencacah') is-invalid @enderror"
                                id="edit_pencacah" name="pencacah" required autocomplete="off">
                            <div class="autocomplete-suggestions" id="edit-pencacah-suggestions"></div>
                            <div class="invalid-feedback" data-field="pencacah">
                                @error('pencacah')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3 autocomplete-container">
                            <label for="edit_pengawas" class="form-label">Pengawas <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pengawas') is-invalid @enderror"
                                id="edit_pengawas" name="pengawas" required autocomplete="off">
                            <div class="autocomplete-suggestions" id="edit-pengawas-suggestions"></div>
                            <div class="invalid-feedback" data-field="pengawas">
                                @error('pengawas')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_target_penyelesaian" class="form-label">Target Penyelesaian <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('target_penyelesaian') is-invalid @enderror"
                                id="edit_target_penyelesaian" name="target_penyelesaian" required>
                            <div class="invalid-feedback" data-field="target_penyelesaian">
                                @error('target_penyelesaian')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_flag_progress" class="form-label">Flag Progress <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('flag_progress') is-invalid @enderror"
                                id="edit_flag_progress" name="flag_progress" required>
                                <option value="Belum Selesai">Belum Selesai</option>
                                <option value="Selesai">Selesai</option>
                            </select>
                            <div class="invalid-feedback" data-field="flag_progress">
                                @error('flag_progress')
                                    {{ $message }}
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                            <input type="date" class="form-control @error('tanggal_pengumpulan') is-invalid @enderror"
                                id="edit_tanggal_pengumpulan" name="tanggal_pengumpulan">
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
                            <span class="spinner-border spinner-border-sm d-none"></span> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================== MODAL HAPUS ================== --}}
    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="deleteForm" method="POST"> @csrf @method('DELETE') <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5> <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="deleteModalBody"> Hapus data {{ strtoupper($jenisKegiatan) }} ini? </div>
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
        // ... (Fungsi initAutocomplete tidak perlu diubah) ...
        function initAutocomplete(inputId, suggestionsId, searchUrl) {
            const input = document.getElementById(inputId);
            if (!input || !searchUrl) return;
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
                    const finalSearchUrl = `${searchUrl}&query=${encodeURIComponent(query)}`;
                    fetch(finalSearchUrl).then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    }).then(data => {
                        suggestionsContainer.innerHTML = '';
                        activeSuggestionIndex = -1;
                        if (Array.isArray(data)) {
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
                                    ).forEach(el => el.classList.remove(
                                        'active'));
                                    div.classList.add('active');
                                    activeSuggestionIndex = index;
                                };
                                suggestionsContainer.appendChild(div);
                            });
                        } else {
                            console.error('Autocomplete data is not an array:', data);
                        }
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

        // --- URL Basis BARU ---
        const distribusiTriwulananBaseUrl = '/tim-distribusi/triwulanan'; // Sesuaikan
        const currentJenisKegiatan = '{{ $jenisKegiatan }}'; // Ambil jenis kegiatan

        /** Edit Data (Sudah Benar) */
        function editData(id) {
            // ... (fungsi editData sudah benar dari jawaban sebelumnya) ...
            const editModalEl = document.getElementById('editDataModal');
            if (!editModalEl) return;
            const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
            const editForm = document.getElementById('editForm');
            editForm.action = `${distribusiTriwulananBaseUrl}/${id}`;
            clearFormErrors(editForm);
            const fallbackInput = editForm.querySelector('#edit_id_fallback');
            if (fallbackInput) fallbackInput.value = id;
            fetch(`${distribusiTriwulananBaseUrl}/${id}/edit`)
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
                    document.getElementById('edit_target_penyelesaian').value = data.target_penyelesaian ||
                        ''; // Sudah Y-m-d
                    document.getElementById('edit_flag_progress').value = data.flag_progress || 'Belum Selesai';
                    document.getElementById('edit_tanggal_pengumpulan').value = data.tanggal_pengumpulan ||
                        ''; // Sudah Y-m-d
                    editModal.show();
                })
                .catch(error => {
                    console.error("Error loading edit data:", error);
                    alert('Tidak dapat memuat data. Error: ' + error.message);
                });
        }

        /** Delete Data (Sudah Benar) */
        function deleteData(id) {
            // ... (fungsi deleteData sudah benar dari jawaban sebelumnya) ...
            const deleteModalEl = document.getElementById('deleteDataModal');
            if (!deleteModalEl) return;
            const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = `${distribusiTriwulananBaseUrl}/${id}`;
            let methodInput = deleteForm.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                deleteForm.appendChild(methodInput);
            }
            methodInput.value = 'DELETE';
            deleteForm.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
            document.getElementById('deleteModalBody').innerText =
                `Apakah Anda yakin ingin menghapus data ${currentJenisKegiatan.toUpperCase()} ini?`;
            const confirmBtn = document.getElementById('confirmDeleteButton');
            const newConfirmBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
            newConfirmBtn.addEventListener('click', (e) => {
                e.preventDefault();
                deleteForm.submit();
            });
            deleteModal.show();
        }

        /** AJAX Helpers (Sudah Benar) */
        // ... (fungsi clearFormErrors, showFormErrors, handleFormSubmitAjax tidak perlu diubah) ...
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

            // --- Init Autocomplete (PERBAIKAN URL) ---
            @if (Route::has('tim-distribusi.triwulanan.searchKegiatan'))
                // Kirim jenis kegiatan saat ini ke URL search
                const kegiatanSearchUrl =
                    `{{ route('tim-distribusi.triwulanan.searchKegiatan', ['jenisKegiatan' => $jenisKegiatan]) }}?`;
                initAutocomplete('nama_kegiatan', 'kegiatan-suggestions', kegiatanSearchUrl);
                initAutocomplete('edit_nama_kegiatan', 'edit-kegiatan-suggestions', kegiatanSearchUrl);
            @else
                console.warn(
                    'Rute tim-distribusi.triwulanan.searchKegiatan tidak ditemukan.');
            @endif

            @if (Route::has('tim-distribusi.triwulanan.searchPetugas'))
                const petugasSearchUrl =
                    '{{ route('tim-distribusi.triwulanan.searchPetugas') }}?'; // Hapus field=...
                initAutocomplete('pencacah', 'pencacah-suggestions', petugasSearchUrl);
                initAutocomplete('pengawas', 'pengawas-suggestions', petugasSearchUrl);
                initAutocomplete('edit_pencacah', 'edit-pencacah-suggestions', petugasSearchUrl);
                initAutocomplete('edit_pengawas', 'edit-pengawas-suggestions', petugasSearchUrl);
            @else
                console.warn(
                    'Rute tim-distribusi.triwulanan.searchPetugas tidak ditemukan.');
            @endif


            // --- Init AJAX Form Handlers (Sudah Benar) ---
            // ... (kode sama seperti sebelumnya) ...
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

            // --- Select All & Bulk Delete (Sudah Benar) ---
            // ... (kode sama seperti sebelumnya) ...
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
                df.action = '{{ route('tim-distribusi.triwulanan.bulkDelete') }}';
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
                document.getElementById('deleteModalBody').innerText = `Hapus ${count} data terpilih?`;
                const ncb = document.getElementById('confirmDeleteButton').cloneNode(true);
                document.getElementById('confirmDeleteButton').parentNode.replaceChild(ncb, document
                    .getElementById('confirmDeleteButton'));
                ncb.addEventListener('click', (e) => {
                    e.preventDefault();
                    df.submit();
                });
                dm.show();
            });

            // --- Filters Per Page & Tahun (Sudah Benar) ---
            const pps = document.getElementById('perPageSelect');
            const ts = document.getElementById('tahunSelect'); // Ambil elemen tahun
            function hfc() {
                const cu = new URL(window.location.href);
                const p = cu.searchParams;
                if (pps) p.set('per_page', pps.value);
                if (ts) p.set('tahun', ts.value);
                else p.delete('tahun'); // Handle jika filter tahun tidak ada
                p.set('page', 1);
                // Gunakan route helper dengan parameter jenisKegiatan
                window.location.href =
                    '{{ route('tim-distribusi.triwulanan.index', ['jenisKegiatan' => $jenisKegiatan]) }}' + '?' + p
                    .toString();
            }
            if (pps) pps.addEventListener('change', hfc);
            if (ts) ts.addEventListener('change', hfc); // Tambahkan listener


            // --- Fallback Error Modals (Sudah Benar) ---
            // ... (kode sama seperti sebelumnya) ...
            @if (session('error_modal') == 'tambahDataModal' && $errors->any())
                const tmef = document.getElementById('tambahDataModal');
                if (tmef) bootstrap.Modal.getOrCreateInstance(tmef).show();
            @endif
            @if (session('error_modal') == 'editDataModal' && $errors->any())
                const eid_fb_input = document.getElementById('edit_id_fallback');
                const eid_fb = eid_fb_input ? eid_fb_input.value : '{{ session('edit_id') }}';
                if (eid_fb) {
                    const emef = document.getElementById('editDataModal');
                    if (emef) {
                        const edf = document.getElementById('editForm');
                        edf.action = `${distribusiTriwulananBaseUrl}/${eid_fb}`;
                        bootstrap.Modal.getOrCreateInstance(emef).show();
                        @foreach ($errors->keys() as $f)
                            const fel = edf.querySelector('[name="{{ $f }}"]');
                            if (fel) fel.classList.add('is-invalid');
                            const erel = edf.querySelector(`.invalid-feedback[data-field="{{ $f }}"]`);
                            if (erel) erel.textContent = '{{ $errors->first($f) }}';
                            else {
                                const erelById = edf.querySelector(`#edit_{{ $f }}_error`);
                                if (erelById) erelById.textContent = '{{ $errors->first($f) }}';
                            }
                        @endforeach
                    }
                }
            @endif

            // --- Auto-hide Alerts (Sudah Benar) ---
            // ... (kode sama seperti sebelumnya) ...
            const alertList = document.querySelectorAll('.alert-dismissible[role="alert"]');
            alertList.forEach(function(alert) {
                if (!alert.closest('.modal')) {
                    const autoHide = {{ session('auto_hide', 'false') ? 'true' : 'false' }};
                    if (autoHide) {
                        setTimeout(() => {
                            bootstrap.Alert.getOrCreateInstance(alert).close();
                        }, 5000);
                    }
                }
            })

            // --- Script untuk Update Opsi Export Modal (Sudah Benar) ---
            // ... (kode sama seperti sebelumnya) ...
            const exportModalEl = document.getElementById('exportModal');
            if (exportModalEl) {
                exportModalEl.addEventListener('show.bs.modal', function() {
                    const currentPageOption = document.querySelector(
                        '#exportModal #dataRange option[value="current_page"]');
                    const allDataOption = document.querySelector(
                        '#exportModal #dataRange option[value="all"]');
                    const totalData = {{ $listData->total() }};
                    const currentPageData = {{ $listData->count() }};
                    if (currentPageOption) currentPageOption.textContent =
                        `Hanya Halaman Ini (${currentPageData} data)`;
                    if (allDataOption) allDataOption.textContent = `Semua Data (${totalData} data)`;
                    document.querySelector('#exportForm input[name="tahun"]').value =
                        '{{ $selectedTahun ?? date('Y') }}';
                    document.querySelector('#exportForm input[name="kegiatan"]').value =
                        '{{ $selectedKegiatan ?? '' }}';
                    document.querySelector('#exportForm input[name="search"]').value =
                        '{{ $search ?? '' }}';
                    document.querySelector('#exportForm input[name="page"]').value =
                        '{{ $listData->currentPage() }}';
                    document.querySelector('#exportForm input[name="per_page"]').value =
                        '{{ request('per_page', $listData->perPage()) }}';
                });
            }

        });
    </script>
@endpush
