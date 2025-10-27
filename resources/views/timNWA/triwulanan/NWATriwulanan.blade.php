@extends('layouts.app')

{{-- Sesuaikan Title --}}
@section('title', strtoupper($jenisKegiatan) . ' - NWA Triwulanan - Sitokcer')
@section('header-title', 'List Target Survei ' . ucwords($jenisKegiatan))

{{-- STYLES UNTUK AUTOCOMPLETE --}}
@push('styles')
    <style>
        /* Style Autocomplete disesuaikan dengan variabel CSS global */
        .autocomplete-container {
            position: relative;
        }

        .autocomplete-suggestions {
            position: absolute;
            border: 1px solid var(--border-color, #d1d3e2);
            border-top: none;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1056;
            width: 100%;
            background-color: var(--card-bg, #fff);
            max-height: 200px;
            overflow-y: auto;
            border-radius: 0 0 var(--border-radius-sm, 0.375rem) var(--border-radius-sm, 0.375rem);
            box-shadow: var(--box-shadow-md, 0 4px 6px rgba(0, 0, 0, 0.07));
        }

        .autocomplete-suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
            font-size: var(--font-size-sm, 0.875rem);
        }

        .autocomplete-suggestion-item:hover,
        .autocomplete-suggestion-item.active {
            background-color: var(--primary-color, #0d6efd);
            /* Gunakan primary color dari global */
            color: var(--card-bg, #fff);
        }
    </style>
@endpush

@section('content')
    {{-- MENGGUNAKAN PADDING GLOBAL --}}
    <!-- <div class="container-fluid px-4 py-4">

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
        @endpush -->
    
        {{-- 1. Menggunakan Page Header dari global.css --}}
        <div class="page-header mb-4">
            <div class="header-content">
                <h2 class="page-title">List Target Survei {{ ucwords($jenisKegiatan) }}</h2>
                <p class="page-subtitle">Kelola data target survei {{ $jenisKegiatan }}</p>
            </div>
        </div>

        {{-- 2. Menggunakan .data-card sebagai wrapper utama --}}
        <div class="data-card">
            {{-- 3. Menggunakan .toolbar untuk semua aksi dan filter --}}
            <div class="toolbar">
                <div class="toolbar-left">
                    {{-- 4. Menggunakan .btn-action dan icon SVG --}}
                    <button type="button" class="btn-action btn-primary" data-bs-toggle="modal"
                        data-bs-target="#tambahDataModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="16"></line>
                            <line x1="8" y1="12" x2="16" y2="12"></line>
                        </svg>
                        Tambah Baru
                    </button>
                   <button type="button" class="btn-action btn-secondary" data-bs-toggle="modal"
                        data-bs-target="#importModal">
                        {{-- Tetap pakai style inline sementara --}}
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                        Import
                    </button>
                    <button type="button" class="btn-action btn-success" data-bs-toggle="modal"
                        data-bs-target="#exportModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Ekspor Hasil
                    </button>
                    <button type="button" class="btn-action btn-danger" data-bs-target="#deleteDataModal" id="bulkDeleteBtn"
                        disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Hapus Terpilih
                    </button>
                </div>

                <div class="toolbar-right">
                    {{-- 5. Menggunakan .filter-group dan .filter-select --}}
                    <div class="filter-group">
                        <label class="filter-label">Display:</label>
                        <select class="filter-select" id="perPageSelect">
                            @php $options = [10, 20, 30, 50, 100, 500, 'all']; @endphp
                            @foreach ($options as $option)
                                <option value="{{ $option }}" {{ request('per_page', 20) == $option ? 'selected' : '' }}>
                                    {{ $option == 'all' ? 'All' : $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Kegiatan:</label>
                        <select class="filter-select" id="kegiatanSelect" name="kegiatan">
                            <option value="">Semua Kegiatan</option>
                            @foreach ($kegiatanCounts ?? [] as $kegiatan)
                                <option value="{{ $kegiatan->nama_kegiatan }}"
                                    {{ request('kegiatan') == $kegiatan->nama_kegiatan ? 'selected' : '' }}>
                                    {{ $kegiatan->nama_kegiatan }} ({{ $kegiatan->total }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Tahun:</label>
                        <select class="filter-select" id="tahunSelect">
                            @foreach ($availableTahun ?? [date('Y')] as $tahun)
                                <option value="{{ $tahun }}" {{ ($selectedTahun ?? date('Y')) == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 6. Search form dipindah ke toolbar kanan --}}
                    <form action="{{ route('nwa.triwulanan.index', ['jenisKegiatan' => $jenisKegiatan]) }}" method="GET"
                        class="search-form">
                        <input type="hidden" name="tahun" value="{{ $selectedTahun ?? date('Y') }}">
                        @if (request('kegiatan'))
                            <input type="hidden" name="kegiatan" value="{{ request('kegiatan') }}">
                        @endif
                        <input type="text" class="search-input" placeholder="Cari..." name="search"
                            value="{{ request('search') }}">
                        <button class="search-btn" type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.35-4.35"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

            {{-- 7. Menggunakan style .alert-success dari global.css --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mx-4" role="alert">
                    <div class="alert-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="alert-close" data-bs-dismiss="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            @endif
            {{-- Alert error biarkan standar, global.css akan menanganinya --}}
            @if ($errors->any() && !session('error_modal'))
                <div class="alert alert-danger alert-dismissible fade show mx-4" role="alert">
                    <strong>Error!</strong> Periksa form.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- 10. Search form sudah dipindah ke toolbar --}}

            {{-- 11. Menggunakan .table-wrapper dan .data-table --}}
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            {{-- 12. Menggunakan .th-checkbox dan .table-checkbox --}}
                            <th class="th-checkbox">
                                <input type="checkbox" class="table-checkbox" id="selectAll">
                            </th>
                            <th>Nama Kegiatan</th>
                            <th>BS/Responden</th>
                            <th>Pencacah</th>
                            <th>Pengawas</th>
                            <th>Target Selesai</th>
                            <th>Progress</th>
                            <th>Tgl Kumpul</th>
                            <th class="th-action">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($listData ?? [] as $item)
                            <tr>
                                {{-- 13. Menggunakan .td-checkbox dan .table-checkbox --}}
                                <td class="td-checkbox">
                                    <input type="checkbox" class="table-checkbox row-checkbox"
                                        value="{{ $item->id_nwa_triwulanan }}">
                                </td>
                                <td>{{ $item->nama_kegiatan }}</td>
                                <td class="text-secondary">{{ $item->BS_Responden }}</td> {{-- Tetap text-secondary dari global
                                --}}
                                <td class="text-secondary">{{ $item->pencacah }}</td>
                                <td class="text-secondary">{{ $item->pengawas }}</td>
                                <td class="text-secondary">
                                    {{ $item->target_penyelesaian ? $item->target_penyelesaian->format('d/m/Y') : '-' }}
                                </td>
                                <td>
                                    {{-- 14. Badge disesuaikan dengan style global.css --}}
                                    @php
                                        $flag = $item->flag_progress;
                                        $badgeClass = 'badge-secondary'; // Default 'Lainnya' atau null
                                        if ($flag === 'Selesai') {
                                            $badgeClass = 'badge-success';
                                        } elseif ($flag === 'Proses') {
                                            $badgeClass = 'badge-warning';
                                        } elseif ($flag === 'Belum Mulai') {
                                            $badgeClass = 'badge-danger'; // Menggunakan badge-danger untuk 'Belum Mulai'
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $flag ?? '-' }}</span>
                                </td>
                                <td class="text-secondary">
                                    {{ $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format('d/m/Y') : '-' }}
                                </td>
                                {{-- 15. Menggunakan .action-buttons dan .btn-icon --}}
                                <td class="td-action">
                                    <div class="action-buttons">
                                        <button type="button" class="btn-icon btn-icon-edit" title="Edit" data-bs-toggle="modal"
                                            data-bs-target="#editDataModal" onclick="editData({{ $item->id_nwa_triwulanan }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn-icon btn-icon-delete" title="Hapus"
                                            data-bs-toggle="modal" data-bs-target="#deleteDataModal"
                                            onclick="deleteData({{ $item->id_nwa_triwulanan }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- 16. Menggunakan style .empty-state --}}
                            <tr>
                                <td colspan="9" class="empty-state">
                                    <div class="empty-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
                                            fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <path d="m21 21-4.35-4.35"></path>
                                        </svg>
                                    </div>
                                    <p class="empty-text">Tidak ada data yang ditemukan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 17. Menggunakan .table-footer, .footer-info, .footer-pagination --}}
            <div class="table-footer">
                <div class="footer-info">
                    Displaying {{ $listData->firstItem() ?? 0 }} - {{ $listData->lastItem() ?? 0 }} of
                    {{ $listData->total() ?? 0 }}
                </div>
                <div class="footer-pagination">
                    {{ $listData->links() ?? '' }}
                </div>
            </div>
        </div>
    </div>

     <!-- Modal Import Data -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('nwa.triwulanan.import') }}" method="POST" enctype="multipart/form-data">
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
                            <a href="{{ route('nwa.triwulanan.downloadTemplate') }}" class="btn btn-sm btn-secondary">
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

    {{-- ================================================= --}}
    {{-- == MODAL SECTIONS == --}}
    {{-- ================================================= --}}

    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            {{-- 18. Menerapkan .modern-modal pada semua modal --}}
            <form action="{{ route('nwa.triwulanan.export', ['jenisKegiatan' => $jenisKegiatan]) }}" method="GET">
                @csrf
                {{-- (hidden inputs... TETAP) --}}
                <input type="hidden" name="kegiatan" value="{{ request('kegiatan') }}">
                <input type="hidden" name="search" value="{{ request('search') }}">
                <input type="hidden" name="tahun" value="{{ request('tahun', date('Y')) }}">
                <input type="hidden" name="page" value="{{ request('page', 1) }}">
                <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">

                <div class="modal-content modern-modal">
                    <div class="modal-header">
                        <div class="modal-header-content">
                            <h5 class="modal-title">Ekspor Data NWA</h5>
                            <p class="modal-subtitle">Pilih opsi ekspor Anda</p>
                        </div>
                        <button type="button" class="modal-close" data-bs-dismiss="modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{-- 19. Menggunakan .form-group dan .form-select --}}
                        <div class="form-group">
                            <label for="dataRange" class="form-label">Jangkauan Data</label>
                            <select class="form-select" id="dataRange" name="dataRange" required>
                                <option value="all">Semua Catatan</option>
                                <option value="current_page">Hanya Halaman Terkini</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="dataFormat" class="form-label">Format Data</label>
                            <select class="form-select" id="dataFormat" name="dataFormat" required>
                                <option value="formatted_values">Formatted Values</option>
                                <option value="raw_values">Raw Values</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="exportFormat" class="form-label">Format Keluaran</label>
                            <select class="form-select" id="exportFormat" name="exportFormat" required>
                                <option value="excel">Excel 2007</option>
                                <option value="csv">CSV (Nilai Terpisah Koma)</option>
                                <option value="word">Word (.docx)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Ekspor
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="{{ route('nwa.triwulanan.store') }}" method="POST" id="tambahForm">
                @csrf
                <div class="modal-content modern-modal">
                    {{-- 20. Menggunakan style modal header modern --}}
                    <div class="modal-header">
                        <div class="modal-header-content">
                            <h5 class="modal-title">Tambah Data NWA Triwulanan</h5>
                            <p class="modal-subtitle">Isi form di bawah untuk menambah data baru</p>
                        </div>
                        <button type="button" class="modal-close" data-bs-dismiss="modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{-- Alert Error (jika ada dari fallback) - Dipindahkan ke JS --}}
                        <input type="hidden" name="_form" value="tambahForm">

                        {{-- 21. Menggunakan .form-group, .form-label, .form-input --}}
                        <div class="form-group autocomplete-container">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span
                                    class="required">*</span></label>
                            <input type="text" class="form-input @error('nama_kegiatan') is-invalid @enderror"
                                id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}"
                                placeholder="Ketik untuk mencari..." required autocomplete="off">
                            <div class="autocomplete-suggestions" id="kegiatan-suggestions"></div>
                            <div class="invalid-feedback" data-field="nama_kegiatan">
                                @error('nama_kegiatan') {{ $message }} @enderror
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="BS_Responden" class="form-label">Blok Sensus/Responden <span
                                            class="required">*</span></label>
                                    <input type="text" class="form-input @error('BS_Responden') is-invalid @enderror"
                                        id="BS_Responden" name="BS_Responden" value="{{ old('BS_Responden') }}" required>
                                    <div class="invalid-feedback" data-field="BS_Responden">
                                        @error('BS_Responden'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group autocomplete-container">
                                    <label for="pencacah" class="form-label">Pencacah <span
                                            class="required">*</span></label>
                                    <input type="text" class="form-input @error('pencacah') is-invalid @enderror"
                                        id="pencacah" name="pencacah" value="{{ old('pencacah') }}" required
                                        autocomplete="off">
                                    <div class="autocomplete-suggestions" id="pencacah-suggestions"></div>
                                    <div class="invalid-feedback" data-field="pencacah">
                                        @error('pencacah'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group autocomplete-container">
                                    <label for="pengawas" class="form-label">Pengawas <span
                                            class="required">*</span></label>
                                    <input type="text" class="form-input @error('pengawas') is-invalid @enderror"
                                        id="pengawas" name="pengawas" value="{{ old('pengawas') }}" required
                                        autocomplete="off">
                                    <div class="autocomplete-suggestions" id="pengawas-suggestions"></div>
                                    <div class="invalid-feedback" data-field="pengawas">
                                        @error('pengawas'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="target_penyelesaian" class="form-label">Target Penyelesaian <span
                                            class="required">*</span></label>
                                    <input type="date" class="form-input @error('target_penyelesaian') is-invalid @enderror"
                                        id="target_penyelesaian" name="target_penyelesaian"
                                        value="{{ old('target_penyelesaian') }}" required>
                                    <div class="invalid-feedback" data-field="target_penyelesaian">
                                        @error('target_penyelesaian'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="flag_progress" class="form-label">Flag Progress <span
                                            class="required">*</span></label>
                                    {{-- 22. .form-select akan otomatis ter-style --}}
                                    <select class="form-select @error('flag_progress') is-invalid @enderror"
                                        id="flag_progress" name="flag_progress" required>
                                        @php $oldFlag = old('flag_progress', 'Belum Mulai'); @endphp
                                        @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                            <option value="{{ $opt }}" @selected($oldFlag === $opt)>
                                                {{ $opt }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" data-field="flag_progress">
                                        @error('flag_progress'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                                    <input type="date" class="form-input @error('tanggal_pengumpulan') is-invalid @enderror"
                                        id="tanggal_pengumpulan" name="tanggal_pengumpulan"
                                        value="{{ old('tanggal_pengumpulan') }}">
                                    <div class="invalid-feedback" data-field="tanggal_pengumpulan">
                                        @error('tanggal_pengumpulan'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- 23. Menggunakan style modal footer modern --}}
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary">
                            <span class="spinner-border spinner-border-sm d-none"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="editForm">
                <input type="hidden" name="edit_id_fallback" id="edit_id_fallback" value="{{ session('edit_id') ?? '' }}">

                <div class="modal-content modern-modal">
                    <div class="modal-header">
                        <div class="modal-header-content">
                            <h5 class="modal-title">Edit Data NWA Triwulanan</h5>
                            <p class="modal-subtitle">Perbarui informasi data yang diperlukan</p>
                        </div>
                        <button type="button" class="modal-close" data-bs-dismiss="modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        {{-- Alert Error (jika ada dari fallback) - Dipindahkan ke JS --}}

                        <div class="form-group autocomplete-container">
                            <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan <span
                                    class="required">*</span></label>
                            <input type="text" class="form-input @error('nama_kegiatan') is-invalid @enderror"
                                id="edit_nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" required
                                autocomplete="off">
                            <div class="autocomplete-suggestions" id="edit-kegiatan-suggestions"></div>
                            <div class="invalid-feedback" data-field="nama_kegiatan">
                                @error('nama_kegiatan') {{ $message }} @enderror
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_BS_Responden" class="form-label">Blok Sensus/Responden <span
                                            class="required">*</span></label>
                                    <input type="text" class="form-input @error('BS_Responden') is-invalid @enderror"
                                        id="edit_BS_Responden" name="BS_Responden" value="{{ old('BS_Responden') }}"
                                        required>
                                    <div class="invalid-feedback" data-field="BS_Responden">
                                        @error('BS_Responden'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group autocomplete-container">
                                    <label for="edit_pencacah" class="form-label">Pencacah <span
                                            class="required">*</span></label>
                                    <input type="text" class="form-input @error('pencacah') is-invalid @enderror"
                                        id="edit_pencacah" name="pencacah" value="{{ old('pencacah') }}" required
                                        autocomplete="off">
                                    <div class="autocomplete-suggestions" id="edit-pencacah-suggestions"></div>
                                    <div class="invalid-feedback" data-field="pencacah">
                                        @error('pencacah'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group autocomplete-container">
                                    <label for="edit_pengawas" class="form-label">Pengawas <span
                                            class="required">*</span></label>
                                    <input type="text" class="form-input @error('pengawas') is-invalid @enderror"
                                        id="edit_pengawas" name="pengawas" value="{{ old('pengawas') }}" required
                                        autocomplete="off">
                                    <div class="autocomplete-suggestions" id="edit-pengawas-suggestions"></div>
                                    <div class="invalid-feedback" data-field="pengawas">
                                        @error('pengawas'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_target_penyelesaian" class="form-label">Target Penyelesaian <span
                                            class="required">*</span></label>
                                    <input type="date" class="form-input @error('target_penyelesaian') is-invalid @enderror"
                                        id="edit_target_penyelesaian" name="target_penyelesaian"
                                        value="{{ old('target_penyelesaian') }}" required>
                                    <div class="invalid-feedback" data-field="target_penyelesaian">
                                        @error('target_penyelesaian'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_flag_progress" class="form-label">Flag Progress <span
                                            class="required">*</span></label>
                                    <select class="form-select @error('flag_progress') is-invalid @enderror"
                                        id="edit_flag_progress" name="flag_progress" required>
                                        {{-- Opsi disamakan --}}
                                        <option value="Belum Mulai">Belum Mulai</option>
                                        <option value="Proses">Proses</option>
                                        <option value="Selesai">Selesai</option>
                                    </select>
                                    <div class="invalid-feedback" data-field="flag_progress">
                                        @error('flag_progress'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                                    <input type="date" class="form-input @error('tanggal_pengumpulan') is-invalid @enderror"
                                        id="edit_tanggal_pengumpulan" name="tanggal_pengumpulan"
                                        value="{{ old('tanggal_pengumpulan') }}">
                                    <div class="invalid-feedback" data-field="tanggal_pengumpulan">
                                        @error('tanggal_pengumpulan'){{ $message }}@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary">
                            <span class="spinner-border spinner-border-sm d-none"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                {{-- 24. Menggunakan style modal delete modern --}}
                <div class="modal-content modern-modal">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="modal-close modal-close-white" data-bs-dismiss="modal">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="delete-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                        <p class="delete-text" id="deleteModalBody">Apakah Anda yakin ingin menghapus data ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-danger" id="confirmDeleteButton">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                </path>
                            </svg>
                            Ya, Hapus
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // URL global untuk rute (diasumsikan ada)
        const autocompleteUrls = {
            kegiatan: '{{ Route::has('master.kegiatan.search') ? route('master.kegiatan.search') : '' }}',
            petugas: '{{ Route::has('master.petugas.search') ? route('master.petugas.search') : '' }}'
        };

        // URL basis untuk NWA Triwulanan (sesuai web.php baru)
        const nwaTriwulananBaseUrl = '/nwa/triwulanan';

        /** Autocomplete */
        function initAutocomplete(inputId, suggestionsId, searchUrl) {
            /* ... (fungsi ini tidak perlu diubah) ... */
            const input = document.getElementById(inputId);
            if (!input || !searchUrl) return;
            const suggestionsContainer = document.getElementById(suggestionsId);
            let debounceTimer;
            let activeSuggestionIndex = -1;
            input.addEventListener('input', function () {
                const query = this.value;
                clearTimeout(debounceTimer);
                if (query.length < 1) {
                    if (suggestionsContainer) suggestionsContainer.innerHTML = '';
                    activeSuggestionIndex = -1;
                    return;
                }
                debounceTimer = setTimeout(() => {
                    const finalSearchUrl = `${searchUrl}?query=${encodeURIComponent(query)}`;
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
            input.addEventListener('keydown', function (e) {
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

        /** Edit Data */
        function editData(id) {
            const editModalEl = document.getElementById('editDataModal');
            if (!editModalEl) return;
            const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
            const editForm = document.getElementById('editForm');

            // PERUBAHAN: URL disesuaikan dengan web.php baru (PUT /nwa/triwulanan/{id})
            editForm.action = `${nwaTriwulananBaseUrl}/${id}`;

            clearFormErrors(editForm);
            document.getElementById('edit_id_fallback').value = id;

            // PERUBAHAN: URL disesuaikan dengan web.php baru (GET /nwa/triwulanan/{id}/edit)
            fetch(`${nwaTriwulananBaseUrl}/${id}/edit`)
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
                        ''; // Disederhanakan
                    document.getElementById('edit_flag_progress').value = data.flag_progress || 'Belum Mulai';
                    document.getElementById('edit_tanggal_pengumpulan').value = data.tanggal_pengumpulan ||
                        ''; // Disederhanakan
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

            // PERUBAHAN: URL disesuaikan dengan web.php baru (DELETE /nwa/triwulanan/{id})
            deleteForm.action = `${nwaTriwulananBaseUrl}/${id}`;

            let methodInput = deleteForm.querySelector('input[name="_method"]');
            if (!methodInput) {
                methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                deleteForm.appendChild(methodInput);
            }
            methodInput.value = 'DELETE';
            deleteForm.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
            document.getElementById('deleteModalBody').innerText = 'Hapus data ini?';
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
        document.addEventListener('DOMContentLoaded', function () {
            // Init Autocomplete
            if (autocompleteUrls.kegiatan) {
                initAutocomplete('nama_kegiatan', 'kegiatan-suggestions', autocompleteUrls.kegiatan);
                initAutocomplete('edit_nama_kegiatan', 'edit-kegiatan-suggestions', autocompleteUrls.kegiatan);
            } else {
                console.warn('Rute master.kegiatan.search tidak ada.');
            }
            @if (Route::has('nwa.triwulanan.searchPetugas'))
                // Gunakan rute searchPetugas yang benar
                const petugasSearchUrl = '{{ route('nwa.triwulanan.searchPetugas') }}';
                initAutocomplete('pencacah', 'pencacah-suggestions', petugasSearchUrl);
                initAutocomplete('pengawas', 'pengawas-suggestions', petugasSearchUrl);
                initAutocomplete('edit_pencacah', 'edit-pencacah-suggestions', petugasSearchUrl);
                initAutocomplete('edit_pengawas', 'edit-pengawas-suggestions', petugasSearchUrl);
            @else
                console.warn('Rute nwa.triwulanan.searchPetugas tidak ada.');
            @endif

                // Init AJAX Form Handlers
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

            // Select All & Bulk Delete
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
                df.action = '{{ route('nwa.triwulanan.bulkDelete') }}';
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

            // Filters Per Page & Tahun
            const pps = document.getElementById('perPageSelect');
            const ts = document.getElementById('tahunSelect');
            const ks = document.getElementById('kegiatanSelect');

            function hfc() {
                const cu = new URL(window.location.href);
                const p = cu.searchParams;
                if (pps) p.set('per_page', pps.value);
                if (ts) p.set('tahun', ts.value);
                if (ks) p.set('kegiatan', ks.value);
                p.set('page', 1);
                window.location.href = cu.pathname + '?' + p.toString();
            }
            if (pps) pps.addEventListener('change', hfc);
            if (ts) ts.addEventListener('change', hfc);
            if (ks) ks.addEventListener('change', hfc);

            // Fallback Error Modals
            @if (session('error_modal') == 'tambahDataModal' && $errors->any())
                const tmef = document.getElementById('tambahDataModal');
                if (tmef) bootstrap.Modal.getOrCreateInstance(tmef).show();
            @endif
                @if (session('error_modal') == 'editDataModal' && $errors->any())
                    const eid_fb = document.getElementById('edit_id_fallback')?.value;
                    if (eid_fb) {
                        const emef = document.getElementById('editDataModal');
                        if (emef) {
                            const edf = document.getElementById('editForm');
                            edf.action = `${nwaTriwulananBaseUrl}/${eid_fb}`;
                            bootstrap.Modal.getOrCreateInstance(emef).show();
                            setTimeout(() => {
                                @foreach ($errors->keys() as $f)
                                    const fel = edf.querySelector('[name="{{ $f }}"]');
                                    if (fel) fel.classList.add('is-invalid');
                                    const erel = edf.querySelector(
                                        `.invalid-feedback[data-field="{{ $f }}"]`);
                                    if (erel) erel.textContent =
                                        '{{ $errors->first($f) }}';
                                @endforeach
                                    }, 500);
                        }
                    }
                @endif

                // Auto-hide Alerts
                const aha = document.querySelectorAll('.alert-dismissible[role="alert"]');
            aha.forEach(a => {
                if (!a.closest('.modal')) {
                    setTimeout(() => {
                        const bsa = bootstrap.Alert.getOrCreateInstance(a);
                        if (bsa) bsa.close();
                    }, 5000);
                }
            });
        });
    </script>
@endpush