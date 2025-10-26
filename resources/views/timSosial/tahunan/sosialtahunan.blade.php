@extends('layouts.app')

{{-- [DIUBAH] Title & Header --V --}}
@section('title', 'Sosial Tahunan - Sitokcer')
@section('header-title', 'List Target Kegiatan Tahunan Tim Sosial')

{{-- STYLES UNTUK AUTOCOMPLETE --}}
@push('styles')
    <style>
        /* Style Autocomplete */
        .autocomplete-container { position: relative; }
        .autocomplete-suggestions {
            position: absolute; border: 1px solid var(--border-color, #d1d3e2); border-top: none;
            top: 100%; left: 0; right: 0; z-index: 1056; width: 100%;
            background-color: var(--card-bg, #fff); max-height: 200px; overflow-y: auto;
            border-radius: 0 0 var(--border-radius-sm, 0.375rem) var(--border-radius-sm, 0.375rem);
            box-shadow: var(--box-shadow-md, 0 4px 6px rgba(0, 0, 0, 0.07));
        }
        .autocomplete-suggestion-item { padding: 8px 12px; cursor: pointer; font-size: var(--font-size-sm, 0.875rem); }
        .autocomplete-suggestion-item:hover,
        .autocomplete-suggestion-item.active { background-color: var(--primary-color, #0d6efd); color: var(--card-bg, #fff); }

        /* Grid 2 kolom di modal */
        .modal-grid-2col { display: grid; grid-template-columns: repeat(2, 1fr); gap: var(--spacing-lg, 1.5rem); }
        @media (max-width: 768px) { .modal-grid-2col { grid-template-columns: 1fr; gap: var(--spacing-md, 1rem); } }

        .modern-modal .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4 py-4">

        {{-- Alert Success --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        {{-- Alert Error --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorAlert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
         {{-- Alert Warning (dari import sebagian) --}}
        @if (session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert" id="warningAlert">
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        {{-- Alert Import Errors--}}
        @if (session('import_errors'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert" id="importErrorAlert">
                <strong>Beberapa baris gagal diimport:</strong>
                <ul class="mb-0">
                    @foreach (session('import_errors') as $error)
                        <li>Baris {{ $error['row'] ?? '?' }}: {{ $error['error'] }} (Nilai: {{ $error['values'] ?? 'N/A' }})</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Page Header --}}
        <div class="page-header mb-4">
            <div class="header-content">
                 <h2 class="page-title">List Target Kegiatan Tahunan Sosial</h2>
                 <p class="page-subtitle">Kelola data target kegiatan tahunan untuk tim Sosial</p>
            </div>
        </div>

        {{-- Data Card --}}
        <div class="data-card">
            {{-- Toolbar --}}
            <div class="toolbar">
                <div class="toolbar-left">
                    <button type="button" class="btn-action btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        Tambah Baru
                    </button>
                    <button type="button" class="btn-action btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        Import
                    </button>
                    <button type="button" class="btn-action btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                         <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Ekspor Hasil
                    </button>
                    <button type="button" class="btn-action btn-danger" data-bs-toggle="modal" data-bs-target="#deleteDataModal" id="bulkDeleteBtn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        Hapus Terpilih
                    </button>
                </div>
                <div class="toolbar-right">
                    <div class="filter-group">
                        <label class="filter-label">Display:</label>
                        <select class="filter-select" id="perPageSelect">
                            @php $options = [10, 20, 30, 50, 100, 500, 'all']; @endphp
                            @foreach ($options as $option) <option value="{{ $option }}" {{ request('per_page', 20) == $option ? 'selected' : '' }}>{{ $option == 'all' ? 'All' : $option }}</option> @endforeach
                        </select>
                    </div>

                     <div class="filter-group">
                        <label class="filter-label">Kegiatan:</label>
                        <select class="filter-select" id="kegiatanSelect" name="kegiatan">
                            <option value="">Semua Kegiatan</option>
                            @foreach ($kegiatanCounts ?? [] as $kegiatan)
                                <option value="{{ $kegiatan->nama_kegiatan }}"
                                    {{ $selectedKegiatan == $kegiatan->nama_kegiatan ? 'selected' : '' }}>
                                    {{ $kegiatan->nama_kegiatan }} ({{ $kegiatan->total }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Tahun:</label>
                        <select class="filter-select" id="tahunSelect">
                            @foreach ($availableTahun ?? [date('Y')] as $tahun) <option value="{{ $tahun }}" {{ ($selectedTahun ?? date('Y')) == $tahun ? 'selected' : '' }}>{{ $tahun }}</option> @endforeach
                        </select>
                    </div>
                    <form action="{{ route('sosial.tahunan.index') }}" method="GET" class="search-form">
                        {{-- Input hidden akan di-handle oleh JS filter --}}
                        <input type="text" class="search-input" placeholder="Cari..." name="search" value="{{ $search ?? '' }}">
                        <button class="search-btn" type="submit"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg> </button>
                    </form>
                </div>
            </div>

             {{-- Alert error (jika validasi gagal di controller tanpa AJAX) --}}
            @if ($errors->any() && !session('error_modal'))
                <div class="alert alert-danger alert-dismissible fade show mx-4" role="alert">
                    <strong>Error!</strong> Periksa form.<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Table --}}
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="th-checkbox"><input type="checkbox" class="table-checkbox" id="selectAll"></th>
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
                                <td class="td-checkbox"><input type="checkbox" class="table-checkbox row-checkbox" value="{{ $item->id_sosial }}"></td>
                                <td>{{ $item->nama_kegiatan }}</td>
                                <td class="text-secondary">{{ $item->BS_Responden }}</td>
                                <td class="text-secondary">{{ $item->pencacah }}</td>
                                <td class="text-secondary">{{ $item->pengawas }}</td>
                                <td class="text-secondary">{{ $item->target_penyelesaian ? \Carbon\Carbon::parse($item->target_penyelesaian)->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @php
                                        $flag = $item->flag_progress;
                                        $badgeClass = ($flag === 'Selesai') ? 'badge-success' : 'badge-warning';
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $flag }}</span>
                                </td>
                                <td class="text-secondary">{{ $item->tanggal_pengumpulan ? \Carbon\Carbon::parse($item->tanggal_pengumpulan)->format('d/m/Y') : '-' }}</td>
                                <td class="td-action">
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-icon-edit" title="Edit" data-bs-toggle="modal" data-bs-target="#editDataModal" onclick="editData({{ $item->id_sosial }})"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg> </button>
                                        <button class="btn-icon btn-icon-delete" title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteDataModal" onclick="deleteData({{ $item->id_sosial }})"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-state">
                                    <div class="empty-icon"> <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg> </div>
                                    <p class="empty-text">Tidak ada data ditemukan untuk tahun {{ $selectedTahun }}.</p>
                                    @if($search || !empty($selectedKegiatan)) <a href="{{ route('sosial.tahunan.index', ['tahun' => $selectedTahun ?? date('Y')]) }}" class="empty-link">Reset filter</a> @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($listData->hasPages())
            <div class="table-footer">
                <div class="footer-info"> Displaying {{ $listData->firstItem() ?? 0 }} - {{ $listData->lastItem() ?? 0 }} of {{ $listData->total() ?? 0 }} </div>
                <div class="footer-pagination"> {{ $listData->links() ?? '' }} </div>
            </div>
            @endif
        </div>
    </div>

     <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
         <div class="modal-dialog">
             <div class="modal-content">
                 <form action="{{ route('sosial.tahunan.import') }}" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="modal-header">
                         <h5 class="modal-title" id="importModalLabel">Import Data dari Excel</h5>
                         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                     </div>
                     <div class="modal-body">
                          <div class="alert alert-info" role="alert">
                              <small>
                                  <strong>Format yang didukung:</strong> Excel (.xlsx, .xls)<br>
                                  <strong>Ukuran maksimal:</strong> 2 MB<br>
                                  <strong>Catatan:</strong> Pastikan header (snake_case) dan nilai 'flag_progress' (Belum Selesai/Selesai) sesuai template.
                              </small>
                          </div>
                          <div class="mb-3">
                              <a href="{{ route('sosial.tahunan.downloadTemplate') }}" class="btn btn-sm btn-secondary">
                                  <i class="bi bi-download"></i> Download Template Excel
                              </a>
                          </div>
                          <div class="mb-3">
                              <label for="importFile" class="form-label">Pilih File</label>
                              <input type="file" class="form-control" id="importFile" name="file" required accept=".xlsx,.xls">
                              <div class="form-text"> Pastikan format kolom sesuai dengan template </div>
                          </div>
                     </div>
                     <div class="modal-footer">
                         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                         <button type="submit" class="btn btn-primary"> <i class="bi bi-upload"></i> Import </button>
                     </div>
                 </form>
             </div>
         </div>
     </div>


    {{-- ================================================= --}}
    {{-- ==              MODAL SECTIONS                 == --}}
    {{-- ================================================= --}}

    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('sosial.tahunan.export') }}" method="GET" id="exportForm">
                <div class="modal-content modern-modal">
                    <div class="modal-header">
                        <div class="modal-header-content"> <h5 class="modal-title">Ekspor Data Sosial Tahu</h5> <p class="modal-subtitle">Pilih opsi ekspor Anda</p> </div>
                        <button type="button" class="modal-close" data-bs-dismiss="modal"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="tahun" id="export_tahun" value="{{ $selectedTahun ?? date('Y') }}">
                        <input type="hidden" name="kegiatan" id="export_kegiatan" value="{{ $selectedKegiatan ?? '' }}">
                        <input type="hidden" name="search" id="export_search" value="{{ $search ?? '' }}">
                        <input type="hidden" name="page" id="export_page" value="{{ $listData->currentPage() }}">
                        <input type="hidden" name="per_page" id="export_per_page" value="{{ request('per_page', $listData->perPage()) }}">
                        <div class="form-group"> <label for="exportDataRangeDist" class="form-label">Jangkauan Data</label> <select class="form-select" id="exportDataRangeDist" name="dataRange" required> <option value="all">Semua Data ({{ $listData->total() }} record)</option> <option value="current_page">Halaman Ini ({{ $listData->count() }} record)</option> </select> </div>
                        <div class="form-group"> <label for="exportDataFormatDist" class="form-label">Format Nilai Tanggal/Lainnya</label> <select class="form-select" id="exportDataFormatDist" name="dataFormat"> <option value="formatted_values" selected>Format Tampilan</option> <option value="raw_values">Nilai Asli Database</option> </select> <small class="form-text text-muted">Pilih "Raw Values" untuk olah data lanjut.</small> </div>
                        <div class="form-group"> <label for="exportExportFormatDist" class="form-label">Format File Export</label> <select class="form-select" id="exportExportFormatDist" name="exportFormat" required> <option value="excel">Excel (.xlsx)</option> <option value="csv">CSV (.csv)</option> </select> </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> Ekspor </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true">
        {{-- [FIX] Tambah modal-lg --}}
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="{{ route('sosial.tahunan.store') }}" method="POST" id="tambahForm">
                @csrf
                <div class="modal-content modern-modal">
                    <div class="modal-header">
                        <div class="modal-header-content"> <h5 class="modal-title">Tambah Data Sosial Tahunan</h5> <p class="modal-subtitle">Isi form di bawah</p> </div>
                        <button type="button" class="modal-close" data-bs-dismiss="modal"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="_form" value="tambahForm">
                         <div class="modal-grid-2col">
                             <div class="modal-column">
                                <div class="form-group autocomplete-container"> <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="required">*</span></label> <input type="text" class="form-input @error('nama_kegiatan', 'tambahForm') is-invalid @enderror" id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" placeholder="Ketik..." required autocomplete="off"> <div class="autocomplete-suggestions" id="kegiatan-suggestions"></div> <div class="invalid-feedback" data-field="nama_kegiatan">@error('nama_kegiatan', 'tambahForm') {{ $message }} @enderror</div> </div>
                                <div class="form-group"> <label for="BS_Responden" class="form-label">BS/Responden <span class="required">*</span></label> <input type="text" class="form-input @error('BS_Responden', 'tambahForm') is-invalid @enderror" id="BS_Responden" name="BS_Responden" value="{{ old('BS_Responden') }}" required> <div class="invalid-feedback" data-field="BS_Responden">@error('BS_Responden', 'tambahForm') {{ $message }} @enderror</div> </div>
                                <div class="form-group autocomplete-container"> <label for="pencacah" class="form-label">Pencacah <span class="required">*</span></label> <input type="text" class="form-input @error('pencacah', 'tambahForm') is-invalid @enderror" id="pencacah" name="pencacah" value="{{ old('pencacah') }}" required autocomplete="off"> <div class="autocomplete-suggestions" id="pencacah-suggestions"></div> <div class="invalid-feedback" data-field="pencacah">@error('pencacah', 'tambahForm') {{ $message }} @enderror</div> </div>
                                <div class="form-group autocomplete-container"> <label for="pengawas" class="form-label">Pengawas <span class="required">*</span></label> <input type="text" class="form-input @error('pengawas', 'tambahForm') is-invalid @enderror" id="pengawas" name="pengawas" value="{{ old('pengawas') }}" required autocomplete="off"> <div class="autocomplete-suggestions" id="pengawas-suggestions"></div> <div class="invalid-feedback" data-field="pengawas">@error('pengawas', 'tambahForm') {{ $message }} @enderror</div> </div>
                             </div>
                             <div class="modal-column">
                                <div class="form-group"> <label for="target_penyelesaian" class="form-label">Target Penyelesaian <span class="required">*</span></label> <input type="date" class="form-input @error('target_penyelesaian', 'tambahForm') is-invalid @enderror" id="target_penyelesaian" name="target_penyelesaian" value="{{ old('target_penyelesaian') }}" required> <div class="invalid-feedback" data-field="target_penyelesaian">@error('target_penyelesaian', 'tambahForm') {{ $message }} @enderror</div> </div>
                                <div class="form-group"> <label for="flag_progress" class="form-label">Flag Progress <span class="required">*</span></label>
                                    {{-- [FIX] Opsi Select disesuaikan --}}
                                    <select class="form-select @error('flag_progress', 'tambahForm') is-invalid @enderror" id="flag_progress" name="flag_progress" required>
                                        @php $oldFlag = old('flag_progress', 'Belum Selesai'); @endphp
                                        <option value="Belum Selesai" @selected($oldFlag === 'Belum Selesai')>Belum Selesai</option>
                                        <option value="Selesai" @selected($oldFlag === 'Selesai')>Selesai</option>
                                    </select>
                                    <div class="invalid-feedback" data-field="flag_progress">@error('flag_progress', 'tambahForm') {{ $message }} @enderror</div>
                                </div>
                                <div class="form-group"> <label for="tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label> <input type="date" class="form-input @error('tanggal_pengumpulan', 'tambahForm') is-invalid @enderror" id="tanggal_pengumpulan" name="tanggal_pengumpulan" value="{{ old('tanggal_pengumpulan') }}"> <div class="invalid-feedback" data-field="tanggal_pengumpulan">@error('tanggal_pengumpulan', 'tambahForm') {{ $message }} @enderror</div> </div>
                             </div>
                         </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary"> <span class="spinner-border spinner-border-sm d-none"></span> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Simpan </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
         {{-- [FIX] Tambah modal-lg --}}
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="_form" value="editForm"> <input type="hidden" name="edit_id_fallback" id="edit_id_fallback" value="{{ session('edit_id') ?? '' }}">
                <div class="modal-content modern-modal">
                    <div class="modal-header">
                        <div class="modal-header-content"> <h5 class="modal-title">Edit Data Sosial Tahunan</h5> <p class="modal-subtitle">Perbarui informasi data</p> </div>
                        <button type="button" class="modal-close" data-bs-dismiss="modal"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                    </div>
                    <div class="modal-body">
                          <div class="modal-grid-2col">
                              <div class="modal-column">
                                <div class="form-group autocomplete-container"> <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan <span class="required">*</span></label> <input type="text" class="form-input @error('nama_kegiatan', 'editForm') is-invalid @enderror" id="edit_nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" required autocomplete="off"> <div class="autocomplete-suggestions" id="edit-kegiatan-suggestions"></div> <div class="invalid-feedback" data-field="nama_kegiatan">@error('nama_kegiatan', 'editForm') {{ $message }} @enderror</div> </div>
                                <div class="form-group"> <label for="edit_BS_Responden" class="form-label">BS/Responden <span class="required">*</span></label> <input type="text" class="form-input @error('BS_Responden', 'editForm') is-invalid @enderror" id="edit_BS_Responden" name="BS_Responden" value="{{ old('BS_Responden') }}" required> <div class="invalid-feedback" data-field="BS_Responden">@error('BS_Responden', 'editForm') {{ $message }} @enderror</div> </div>
                                <div class="form-group autocomplete-container"> <label for="edit_pencacah" class="form-label">Pencacah <span class="required">*</span></label> <input type="text" class="form-input @error('pencacah', 'editForm') is-invalid @enderror" id="edit_pencacah" name="pencacah" value="{{ old('pencacah') }}" required autocomplete="off"> <div class="autocomplete-suggestions" id="edit-pencacah-suggestions"></div> <div class="invalid-feedback" data-field="pencacah">@error('pencacah', 'editForm') {{ $message }} @enderror</div> </div>
                                <div class="form-group autocomplete-container"> <label for="edit_pengawas" class="form-label">Pengawas <span class="required">*</span></label> <input type="text" class="form-input @error('pengawas', 'editForm') is-invalid @enderror" id="edit_pengawas" name="pengawas" value="{{ old('pengawas') }}" required autocomplete="off"> <div class="autocomplete-suggestions" id="edit-pengawas-suggestions"></div> <div class="invalid-feedback" data-field="pengawas">@error('pengawas', 'editForm') {{ $message }} @enderror</div> </div>
                              </div>
                              <div class="modal-column">
                                <div class="form-group"> <label for="edit_target_penyelesaian" class="form-label">Target Penyelesaian <span class="required">*</span></label> <input type="date" class="form-input @error('target_penyelesaian', 'editForm') is-invalid @enderror" id="edit_target_penyelesaian" name="target_penyelesaian" value="{{ old('target_penyelesaian') }}" required> <div class="invalid-feedback" data-field="target_penyelesaian">@error('target_penyelesaian', 'editForm') {{ $message }} @enderror</div> </div>
                                <div class="form-group"> <label for="edit_flag_progress" class="form-label">Flag Progress <span class="required">*</span></label>
                                    <select class="form-select @error('flag_progress', 'editForm') is-invalid @enderror" id="edit_flag_progress" name="flag_progress" required>
                                         <option value="Belum Selesai">Belum Selesai</option>
                                         <option value="Selesai">Selesai</option>
                                     </select>
                                    <div class="invalid-feedback" data-field="flag_progress">@error('flag_progress', 'editForm') {{ $message }} @enderror</div>
                                </div>
                                <div class="form-group"> <label for="edit_tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label> <input type="date" class="form-input @error('tanggal_pengumpulan', 'editForm') is-invalid @enderror" id="edit_tanggal_pengumpulan" name="tanggal_pengumpulan" value="{{ old('tanggal_pengumpulan') }}"> <div class="invalid-feedback" data-field="tanggal_pengumpulan">@error('tanggal_pengumpulan', 'editForm') {{ $message }} @enderror</div> </div>
                              </div>
                          </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary"> <span class="spinner-border spinner-border-sm d-none"></span> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Simpan Perubahan </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="deleteForm" method="POST">
                @csrf @method('DELETE')
                <div class="modal-content modern-modal">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="modal-close modal-close-white" data-bs-dismiss="modal"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                    </div>
                    <div class="modal-body">
                        <div class="delete-icon"> <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> </div>
                        <p class="delete-text" id="deleteModalBody">Hapus data ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-danger" id="confirmDeleteButton"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Ya, Hapus </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        /** Autocomplete */
        function initAutocomplete(inputId, suggestionsId, searchUrl) {
             const input = document.getElementById(inputId);
             if (!input || !searchUrl) { console.warn(`Autocomplete init failed: Input or URL missing for ${inputId}`); return; }
             const suggestionsContainer = document.getElementById(suggestionsId);
              if (!suggestionsContainer) { console.error(`Suggestion container #${suggestionsId} not found.`); return; }
             let debounceTimer; let activeSuggestionIndex = -1;

             input.addEventListener('input', function() {
                 const query = this.value; clearTimeout(debounceTimer);
                 if (query.length < 1) { if (suggestionsContainer) suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1; return; }
                 debounceTimer = setTimeout(() => {
                     const finalSearchUrl = `${searchUrl}?query=${encodeURIComponent(query)}`; // URL Encoding
                     fetch(finalSearchUrl)
                        .then(response => { if (!response.ok) { throw new Error(`HTTP error! status: ${response.status}`); } return response.json(); })
                        .then(data => {
                             suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1;
                             if (Array.isArray(data)) {
                                 data.forEach((item, index) => {
                                     const div = document.createElement('div'); div.textContent = item; div.classList.add('autocomplete-suggestion-item');
                                     div.onclick = () => { input.value = item; suggestionsContainer.innerHTML = ''; };
                                     div.onmouseover = () => { document.querySelectorAll(`#${suggestionsId} .autocomplete-suggestion-item`).forEach(el => el.classList.remove('active')); div.classList.add('active'); activeSuggestionIndex = index; };
                                     suggestionsContainer.appendChild(div);
                                 });
                             } else { console.error('Autocomplete data is not an array:', data); }
                        }).catch(error => console.error('Autocomplete error:', error));
                 }, 300);
             });
              input.addEventListener('keydown', function(e) {
                 const suggestions = suggestionsContainer.querySelectorAll('.autocomplete-suggestion-item');
                 if (suggestions.length === 0) return;
                 if (e.key === 'ArrowDown') { e.preventDefault(); activeSuggestionIndex = (activeSuggestionIndex + 1) % suggestions.length; updateActiveSuggestion(suggestions, activeSuggestionIndex); }
                 else if (e.key === 'ArrowUp') { e.preventDefault(); activeSuggestionIndex = (activeSuggestionIndex - 1 + suggestions.length) % suggestions.length; updateActiveSuggestion(suggestions, activeSuggestionIndex); }
                 else if (e.key === 'Enter') { if (activeSuggestionIndex > -1) { e.preventDefault(); input.value = suggestions[activeSuggestionIndex].textContent; suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1; } }
                 else if (e.key === 'Escape') { suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1; }
             });
             function updateActiveSuggestion(suggestions, index) { suggestions.forEach(el => el.classList.remove('active')); if (suggestions[index]) suggestions[index].classList.add('active'); }
             document.addEventListener('click', (e) => { if (e.target.id !== inputId && suggestionsContainer) { suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1; } });
        }

        const sosialTahunanBaseUrl = '/sosial/tahunan';

        /** Edit Data */
        function editData(id) {
            const editModalEl = document.getElementById('editDataModal'); if (!editModalEl) return;
            const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
            const editForm = document.getElementById('editForm'); if (!editForm) { console.error("Edit form not found!"); return; }
            editForm.action = `${sosialTahunanBaseUrl}/${id}`;
            clearFormErrors(editForm); document.getElementById('edit_id_fallback').value = id;
            fetch(`${sosialTahunanBaseUrl}/${id}/edit`)
                .then(response => { if (!response.ok) { return response.text().then(text => { throw new Error(text || 'Data not found'); }); } return response.json(); })
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
                .catch(error => { console.error("Error loading edit data:", error); alert('Failed to load data: ' + error.message); });
        }

        /** Delete Data */
        function deleteData(id) {
             const deleteModalEl = document.getElementById('deleteDataModal'); if (!deleteModalEl) return;
             const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
             const deleteForm = document.getElementById('deleteForm'); if (!deleteForm) { console.error("Delete form not found!"); return; }
             deleteForm.action = `${sosialTahunanBaseUrl}/${id}`;
             let methodInput = deleteForm.querySelector('input[name="_method"]');
             if (!methodInput) { methodInput = document.createElement('input'); methodInput.type = 'hidden'; methodInput.name = '_method'; deleteForm.appendChild(methodInput); }
             methodInput.value = 'DELETE';
             deleteForm.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
             document.getElementById('deleteModalBody').innerText = 'Apakah Anda yakin ingin menghapus data ini?';
             const confirmBtn = document.getElementById('confirmDeleteButton'); if (!confirmBtn) { console.error("Confirm delete button not found!"); return; }
             const newConfirmButton = confirmBtn.cloneNode(true);
             confirmBtn.parentNode.replaceChild(newConfirmButton, confirmBtn);
             newConfirmButton.addEventListener('click', (e) => { e.preventDefault(); deleteForm.submit(); });
             deleteModal.show();
        }

        /** AJAX Helpers */
        function clearFormErrors(form) { if (!form) return; form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid')); form.querySelectorAll('.invalid-feedback[data-field]').forEach(el => el.textContent = ''); }
        function showFormErrors(form, errors) { if (!form) return; for (const [field, messages] of Object.entries(errors)) { const input = form.querySelector(`[name="${field}"]`); const errorDiv = form.querySelector(`.invalid-feedback[data-field="${field}"]`); if (input) input.classList.add('is-invalid'); if (errorDiv) errorDiv.textContent = messages[0]; } }
        async function handleFormSubmitAjax(event, form, modalInstance) {
             event.preventDefault(); if (!form || !modalInstance) return;
             const sb = form.querySelector('button[type="submit"]'); const sp = sb?.querySelector('.spinner-border');
             if (sb) sb.disabled = true; if (sp) sp.classList.remove('d-none');
             clearFormErrors(form);
             try {
                 const fd = new FormData(form);
                 const response = await fetch(form.action, { method: form.method, body: fd, headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': fd.get('_token')} });
                 const data = await response.json();
                 if (!response.ok) {
                     if (response.status === 422 && data.errors) { showFormErrors(form, data.errors); }
                     else { alert(data.message || 'Error occurred.'); }
                 } else {
                    modalInstance.hide();
                    location.reload(); // [FIX] Reload untuk memuat session flash
                 }
             } catch (error) { console.error('Fetch error:', error); alert('Cannot connect to server.'); }
             finally { if (sb) sb.disabled = false; if (sp) sp.classList.add('d-none'); }
        }


               /** DOM Ready */
        document.addEventListener('DOMContentLoaded', function() {

            

             // --- [FIX] Auto-hide alerts ---
             ['successAlert', 'errorAlert', 'warningAlert', 'importErrorAlert'].forEach(alertId => {
                 const alertEl = document.getElementById(alertId);
                 if (alertEl && !alertEl.closest('.modal')) {
                    let timeoutDuration = (alertId === 'importErrorAlert' || alertId === 'warningAlert') ? 10000 : 5000;
                     setTimeout(() => {
                         if (window.jQuery) { // Cek jQuery
                             $(alertEl).fadeOut('slow', () => $(alertEl).remove());
                         } else { // Fallback
                             alertEl.style.transition = 'opacity 1s'; alertEl.style.opacity = '0';
                             setTimeout(() => alertEl.remove(), 1000);
                         }
                     }, timeoutDuration);
                 }
             });

            // --- Init Autocomplete ---
             @if (Route::has('sosial.tahunan.searchKegiatan'))
                 const kegiatanSearchUrl = '{{ route('sosial.tahunan.searchKegiatan') }}'; // [FIX] Hapus '?'
                 initAutocomplete('nama_kegiatan', 'kegiatan-suggestions', kegiatanSearchUrl);
                 initAutocomplete('edit_nama_kegiatan', 'edit-kegiatan-suggestions', kegiatanSearchUrl);
             @elseif (Route::has('master.kegiatan.search'))
                 const kegiatanSearchUrl = '{{ route('master.kegiatan.search') }}';
                 initAutocomplete('nama_kegiatan', 'kegiatan-suggestions', kegiatanSearchUrl);
                 initAutocomplete('edit_nama_kegiatan', 'edit-kegiatan-suggestions', kegiatanSearchUrl);
             @else console.warn('Rute searchKegiatan tidak ditemukan.'); @endif

             @if (Route::has('sosial.tahunan.searchPetugas'))
                 const petugasSearchUrl = '{{ route('sosial.tahunan.searchPetugas') }}'; // [FIX] Hapus '?'
                 initAutocomplete('pencacah', 'pencacah-suggestions', petugasSearchUrl);
                 initAutocomplete('pengawas', 'pengawas-suggestions', petugasSearchUrl);
                 initAutocomplete('edit_pencacah', 'edit-pencacah-suggestions', petugasSearchUrl);
                 initAutocomplete('edit_pengawas', 'edit-pengawas-suggestions', petugasSearchUrl);
             @elseif (Route::has('master.petugas.search'))
                 const petugasSearchUrl = '{{ route('master.petugas.search') }}';
                 initAutocomplete('pencacah', 'pencacah-suggestions', petugasSearchUrl);
                 initAutocomplete('pengawas', 'pengawas-suggestions', petugasSearchUrl);
                 initAutocomplete('edit_pencacah', 'edit-pencacah-suggestions', petugasSearchUrl);
                 initAutocomplete('edit_pengawas', 'edit-pengawas-suggestions', petugasSearchUrl);
             @else console.warn('Rute searchPetugas tidak ditemukan.'); @endif

            // --- Init AJAX Form Handlers ---
             const tme = document.getElementById('tambahDataModal');
             const tf = document.getElementById('tambahForm');
             if (tme && tf) { const tm = bootstrap.Modal.getOrCreateInstance(tme); tf.addEventListener('submit', (e) => handleFormSubmitAjax(e, tf, tm)); tme.addEventListener('hidden.bs.modal', () => { clearFormErrors(tf); tf.reset(); }); }
             const eme = document.getElementById('editDataModal');
             const ef = document.getElementById('editForm');
             if (eme && ef) { const em = bootstrap.Modal.getOrCreateInstance(eme); ef.addEventListener('submit', (e) => handleFormSubmitAjax(e, ef, em)); eme.addEventListener('hidden.bs.modal', () => clearFormErrors(ef)); }

            // --- Select All & Bulk Delete ---
             const sa = document.getElementById('selectAll');
             const rcb = document.querySelectorAll('.row-checkbox');
             const bdb = document.getElementById('bulkDeleteBtn');
             const df = document.getElementById('deleteForm');
             function ubdbs() { const cc = document.querySelectorAll('.row-checkbox:checked').length; if (bdb) bdb.disabled = cc === 0; }
             sa?.addEventListener('change', () => { rcb.forEach(cb => cb.checked = sa.checked); ubdbs(); });
             rcb.forEach(cb => cb.addEventListener('change', ubdbs));
             ubdbs();
             bdb?.addEventListener('click', () => {
                 const count = document.querySelectorAll('.row-checkbox:checked').length; if (count === 0) return;
                 const dme = document.getElementById('deleteDataModal'); if (!dme || !df) return;
                 const dm = bootstrap.Modal.getOrCreateInstance(dme);
                 df.action = '{{ route('sosial.tahunan.bulkDelete') }}';
                 let mi = df.querySelector('input[name="_method"]');
                 if (mi) { mi.value = 'POST'; } else { const i = document.createElement('input'); i.type = 'hidden'; i.name = '_method'; i.value = 'POST'; df.appendChild(i); }
                 df.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
                 document.querySelectorAll('.row-checkbox:checked').forEach(cb => { const i = document.createElement('input'); i.type = 'hidden'; i.name = 'ids[]'; i.value = cb.value; df.appendChild(i); });
                 document.getElementById('deleteModalBody').innerText = `Hapus ${count} data?`;
                 const ncb = document.getElementById('confirmDeleteButton').cloneNode(true);
                 document.getElementById('confirmDeleteButton').parentNode.replaceChild(ncb, document.getElementById('confirmDeleteButton'));
                 ncb.addEventListener('click', (e) => { e.preventDefault(); df.submit(); });
                 dm.show();
             });

            // --- [FIX] Filters (Ganti ke metode Produksi) ---
            const pps = document.getElementById('perPageSelect');
            const ts = document.getElementById('tahunSelect');
            const ks = document.getElementById('kegiatanSelect');
            function hfc() {
                const cu = new URL(window.location.href);
                const p = cu.searchParams;
                if (pps) p.set('per_page', pps.value);
                if (ts) p.set('tahun', ts.value);
                if (ks) {
                    if (ks.value) { p.set('kegiatan', ks.value); }
                    else { p.delete('kegiatan'); } // Hapus jika 'Semua'
                }
                
                // Ambil search term dari URL agar tidak hilang
                const currentSearch = new URLSearchParams(window.location.search).get('search');
                if (currentSearch) {
                    p.set('search', currentSearch);
                }

                p.set('page', 1); // Selalu reset ke halaman 1
                window.location.href = cu.pathname + '?' + p.toString();
            }
            if (pps) pps.addEventListener('change', hfc);
            if (ts) ts.addEventListener('change', hfc);
            if (ks) ks.addEventListener('change', hfc);


            // --- [FIX] Fallback Error Modals (Menggunakan Error Bags) ---
            @if (session('error_modal') == 'tambahDataModal' && $errors->tambahForm->any())
                 const tmef = document.getElementById('tambahDataModal');
                 if (tmef) {
                     const tm = bootstrap.Modal.getOrCreateInstance(tmef);
                     tm.show();
                     setTimeout(() => { showFormErrors(document.getElementById('tambahForm'), @json($errors->tambahForm->toArray())); }, 300);
                 }
            @endif
             @if (session('error_modal') == 'editDataModal' && $errors->editForm->any())
                 const eid_fb = '{{ session('edit_id') }}' || document.getElementById('edit_id_fallback')?.value;
                 if (eid_fb) {
                     const emef = document.getElementById('editDataModal');
                     if (emef) {
                         const edf = document.getElementById('editForm');
                         edf.action = `${sosialTahunanBaseUrl}/${eid_fb}`;
                         const em = bootstrap.Modal.getOrCreateInstance(emef);
                         em.show();
                         setTimeout(() => { showFormErrors(edf, @json($errors->editForm->toArray())); }, 300);
                     }
                 }
             @endif

              // --- JAVASCRIPT EXPORT ---
             const exportForm = document.getElementById('exportForm');
             if (exportForm) {
                 exportForm.addEventListener('submit', function(e) {
                      const dataRange = document.getElementById('exportDataRangeSos')?.value;
                      const exportFormat = document.getElementById('exportExportFormatSos')?.value;
                      if (!dataRange || !exportFormat) { e.preventDefault(); alert('Lengkapi pilihan export!'); return false; }
                     this.querySelectorAll('input[type="hidden"]').forEach(el => el.remove());
                     function addHiddenInput(form, name, value) { const i=document.createElement('input'); i.type='hidden'; i.name=name; i.value=value||''; form.appendChild(i); }
                     addHiddenInput(this, 'tahun', document.getElementById('tahunSelect')?.value || '{{ date('Y') }}');
                     addHiddenInput(this, 'kegiatan', document.getElementById('kegiatanSelect')?.value || '');
                     addHiddenInput(this, 'search', document.querySelector('.search-form input[name="search"]')?.value || '');
                     addHiddenInput(this, 'page', '{{ $listData->currentPage() }}');
                     addHiddenInput(this, 'per_page', document.getElementById('perPageSelect')?.value || '{{ $listData->perPage() }}');
                     return true;
                 });
             }
             const exportModalEl = document.getElementById('exportModal');
             if (exportModalEl) {
                 exportModalEl.addEventListener('show.bs.modal', function() {
                    const currentPageOption = document.querySelector('#exportDataRangeSos option[value="current_page"]');
                    const allDataOption = document.querySelector('#exportDataRangeSos option[value="all"]');
                    const totalData = {{ $listData->total() }};
                    const currentPageData = {{ $listData->count() }};
                    if (currentPageOption) currentPageOption.textContent = `Halaman Ini (${currentPageData} data)`;
                    if (allDataOption) allDataOption.textContent = `Semua Data (${totalData} data)`;
                 });
             }
             // --- AKHIR JAVASCRIPT EXPORT ---
        });
    </script>
@endpush