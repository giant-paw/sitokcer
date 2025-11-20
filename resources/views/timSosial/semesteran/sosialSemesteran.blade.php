@extends('layouts.app')

{{-- Judul Disesuaikan --}}
@section('title', 'Sosial Semesteran - Sitokcer')
@section('header-title', 'List Target Kegiatan Semesteran Tim Sosial')

@push('styles')
    <style>
        /* Style Autocomplete (Sama seperti Tahunan) */
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
        .data-table { table-layout: fixed; width: 100%; } .th-checkbox { width: 5%; }
        .data-table th:nth-child(2) { width: 20%; } .data-table th:nth-child(3) { width: 15%; }
        .data-table th:nth-child(4) { width: 12%; } .data-table th:nth-child(5) { width: 12%; }
        .data-table th:nth-child(6) { width: 10%; } .data-table th:nth-child(7) { width: 8%; }
        .data-table th:nth-child(8) { width: 10%; } .th-action { width: 8%; }
        .modern-modal .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 16px 12px;
            -webkit-appearance: none; -moz-appearance: none; appearance: none;
        }
        .modern-modal .invalid-feedback { display: block; }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4 py-4">
    {{-- Alert Import Errors --}}
        @if (session('import_errors'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert" id="importErrorAlert">
                <strong>Beberapa baris gagal diimport:</strong>
                <ul class="mb-0">
                    @foreach (session('import_errors') as $error)
                        <li>Baris {{ $error['row'] ?? '?' }}: {{ $error['error'] }} (Nilai: {{ $error['values'] ?? 'N/A' }})</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- 1. Page Header --}}
        <div class="page-header mb-4">
            <div class="header-content">
                 <h2 class="page-title">List Target Kegiatan Semesteran</h2>
                 <p class="page-subtitle">Kelola data target kegiatan semesteran tim Sosial</p>
            </div>
        </div>

        {{-- 2. Data Card --}}
        <div class="data-card">
            {{-- 3. Toolbar --}}
            <div class="toolbar">
                <div class="toolbar-left">
                    <button type="button" class="btn-action btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        Tambah Baru
                    </button>
                    <button type="button" class="btn-action btn-secondary" data-bs-toggle="modal" data-bs-target="#importModal">
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
                                <option value="{{ $kegiatan->filter_value }}" 
                                    {{ $selectedKegiatan == $kegiatan->filter_value ? 'selected' : '' }}>
                                    {{ $kegiatan->display_name }} ({{ $kegiatan->total }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label">Tahun:</label>
                        <select class="filter-select" id="tahunSelect">
                            @foreach ($availableTahun as $tahun) <option value="{{ $tahun }}" {{ $selectedTahun == $tahun ? 'selected' : '' }}>{{ $tahun }}</option> @endforeach
                        </select>
                    </div>
                    
                    {{-- Ganti rute ke sosial.semesteran.index --}}
                    <form action="{{ route('sosial.semesteran.index') }}" method="GET" class="search-form">
                        <input type="hidden" name="tahun" value="{{ $selectedTahun }}">
                        <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">
                        @if ($selectedKegiatan) <input type="hidden" name="kegiatan" value="{{ $selectedKegiatan }}"> @endif
                        
                        <input type="text" class="search-input" placeholder="Cari..." name="search" id="searchInput" value="{{ $search ?? '' }}">
                        <button class="search-btn" type="submit"> 
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg> 
                        </button>
                    </form>
                </div>
            </div>

            {{-- Alert Sukses & Error --}}
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-4" role="alert">
                <div class="alert-icon"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> </div>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
            </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show mx-4" role="alert">
                     <div class="alert-icon"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> </div>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="alert-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                </div>
            @endif

            {{-- 7. Tabel Data --}}
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
                        @forelse ($listData as $item)
                            <tr>
                                <td class="td-checkbox"><input type="checkbox" class="table-checkbox row-checkbox" value="{{ $item->id_sosial_semesteran }}"></td>
                                <td>{{ $item->masterKegiatan->nama_kegiatan ?? $item->nama_kegiatan }}</td>
                                <td class="text-secondary">{{ $item->BS_Responden }}</td>
                                <td class="text-secondary">{{ $item->pencacah }}</td>
                                <td class="text-secondary">{{ $item->pengawas }}</td>
                                <td class="text-secondary">{{ $item->target_penyelesaian ? $item->target_penyelesaian->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @php
                                        $badgeClass = 'badge-warning'; // Belum Selesai
                                        if ($item->flag_progress == 'Selesai') $badgeClass = 'badge-success';
                                        elseif ($item->flag_progress == 'Proses') $badgeClass = 'badge-info'; // 'Proses'
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ $item->flag_progress }}</span>
                                </td>
                                <td class="text-secondary">{{ $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format('d/m/Y') : '-' }}</td>
                                <td class="td-action">
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-icon-edit" title="Edit" data-bs-toggle="modal" data-bs-target="#editDataModal" onclick="editData({{ $item->id_sosial_semesteran }})"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg> </button>
                                        <button class="btn-icon btn-icon-delete" title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteDataModal" onclick="deleteData({{ $item->id_sosial_semesteran }})"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-state">
                                    <div class="empty-icon"> <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg> </div>
                                    <p class="empty-text">Tidak ada data semesteran ditemukan untuk tahun {{ $selectedTahun }}.</p>
                                     @if($search || $selectedKegiatan) <a href="{{ route('sosial.semesteran.index', ['tahun' => $selectedTahun]) }}" class="empty-link">Reset filter</a> @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($listData->hasPages())
                <div class="table-footer">
                    <div class="footer-info"> Displaying {{ $listData->firstItem() ?? 0 }} - {{ $listData->lastItem() ?? 0 }} of {{ $listData->total() }} </div>
                    <div class="footer-pagination"> {{ $listData->links() }} </div>
                </div>
            @endif
        </div>
    </div>


    {{-- ================================================= --}}
    {{-- == MODAL SECTIONS == --}}
    {{-- ================================================= --}}

    {{-- Modal Import --}}
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modern-modal">
                <form action="{{ route('sosial.semesteran.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                         <div class="modal-header-content">
                             <h5 class="modal-title" id="importModalLabel">Import Data dari Excel</h5>
                             <p class="modal-subtitle">Upload file sesuai template</p>
                         </div>
                        <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info d-flex align-items-start p-3" role="alert">
                             <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3 flex-shrink-0" style="margin-top: 3px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                            <div>
                                <small>
                                    <strong>PENTING:</strong> Pastikan data `nama_kegiatan` di file Excel Anda **sudah valid** dan terdaftar di Master Kegiatan untuk modul **Sosial Semesteran**.
                                </small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <a href="{{ route('sosial.semesteran.downloadTemplate') }}" class="btn-action btn-secondary w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                Download Template Excel
                            </a>
                        </div>
                        <div class="form-group">
                            <label for="importFile" class="form-label">Pilih File (.xlsx, .xls, .csv)</label>
                            <input type="file" class="form-input" id="importFile" name="file" required accept=".xlsx,.xls,.csv">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    {{-- Modal Export --}}
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
             <form action="{{ route('sosial.semesteran.export') }}" method="GET" id="exportForm">
                <input type="hidden" name="kegiatan" id="export_kegiatan"> 
                <input type="hidden" name="search" id="export_search"> 
                <input type="hidden" name="tahun" id="export_tahun"> 
                <input type="hidden" name="page" id="export_page"> 
                <input type="hidden" name="per_page" id="export_per_page">
                
                <div class="modal-content modern-modal">
                    <div class="modal-header">
                        <div class="modal-header-content"> <h5 class="modal-title">Ekspor Data Semesteran</h5> <p class="modal-subtitle">Pilih opsi ekspor</p> </div>
                        <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"> <label for="exportDataRangeBul" class="form-label">Jangkauan Data</label> <select class="form-select" id="exportDataRangeBul" name="dataRange" required> <option value="all">Semua Data</option> <option value="current_page">Halaman Ini</option> </select> </div>
                        <div class="form-group"> <label for="exportDataFormatBul" class="form-label">Format Nilai Tanggal</label> <select class="form-select" id="exportDataFormatBul" name="dataFormat"> <option value="formatted_values" selected>Format Tampilan (dd/mm/yyyy)</option> <option value="raw_values">Nilai Asli Database (yyyy-mm-dd)</option> </select> <small class="form-text text-muted">Pilih "Raw Values" untuk olah data.</small> </div>
                        <div class="form-group"> <label for="exportExportFormatBul" class="form-label">Format File</label> <select class="form-select" id="exportExportFormatBul" name="exportFormat" required> <option value="excel">Excel (.xlsx)</option> <option value="csv">CSV (.csv)</option> </select> </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-primary"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg> Ekspor </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Tambah Data --}}
    <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered"> 
            <form action="{{ route('sosial.semesteran.store') }}" method="POST" id="tambahForm">
                @csrf
                <div class="modal-content modern-modal">
                    <div class="modal-header">
                        <div class="modal-header-content"> <h5 class="modal-title">Tambah Data Semesteran Baru</h5> <p class="modal-subtitle">Isi form di bawah</p> </div>
                        <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="_form" value="tambahForm">

                        <div class="form-group autocomplete-container">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="required">*</span></label>
                            <input type="text" class="form-input @error('nama_kegiatan') is-invalid @enderror"
                                id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}"
                                placeholder="Ketik nama kegiatan (contoh: Susenas Maret)..." required autocomplete="off">
                            <input type="hidden" id="master_kegiatan_id" name="master_kegiatan_id" value="{{ old('master_kegiatan_id') }}">
                            <div class="autocomplete-suggestions" id="kegiatan-suggestions"></div>
                            <div class="invalid-feedback" data-field="nama_kegiatan">@error('nama_kegiatan') {{ $message }} @enderror</div>
                            <div class="invalid-feedback" data-field="master_kegiatan_id">@error('master_kegiatan_id') {{ $message }} @enderror</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="BS_Responden" class="form-label">BS/Responden <span class="required">*</span></label>
                                    <input type="text" class="form-input @error('BS_Responden') is-invalid @enderror" id="BS_Responden" name="BS_Responden" value="{{ old('BS_Responden') }}" required>
                                    <div class="invalid-feedback" data-field="BS_Responden">@error('BS_Responden') {{ $message }} @enderror</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group autocomplete-container">
                                    <label for="pencacah" class="form-label">Pencacah <span class="required">*</span></label>
                                    <input type="text" class="form-input @error('pencacah') is-invalid @enderror" id="pencacah" name="pencacah" value="{{ old('pencacah') }}" required autocomplete="off">
                                    <div class="autocomplete-suggestions" id="pencacah-suggestions"></div>
                                    <div class="invalid-feedback" data-field="pencacah">@error('pencacah') {{ $message }} @enderror</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group autocomplete-container">
                                    <label for="pengawas" class="form-label">Pengawas <span class="required">*</span></label>
                                    <input type="text" class="form-input @error('pengawas') is-invalid @enderror" id="pengawas" name="pengawas" value="{{ old('pengawas') }}" required autocomplete="off">
                                    <div class="autocomplete-suggestions" id="pengawas-suggestions"></div>
                                    <div class="invalid-feedback" data-field="pengawas">@error('pengawas') {{ $message }} @enderror</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="target_penyelesaian" class="form-label">Target Penyelesaian <span class="required">*</span></label>
                                    <input type="date" class="form-input @error('target_penyelesaian') is-invalid @enderror" id="target_penyelesaian" name="target_penyelesaian" value="{{ old('target_penyelesaian') }}" required>
                                    <div class="invalid-feedback" data-field="target_penyelesaian">@error('target_penyelesaian') {{ $message }} @enderror</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="flag_progress" class="form-label">Flag Progress <span class="required">*</span></label>
                                    <select class="form-select @error('flag_progress') is-invalid @enderror" id="flag_progress" name="flag_progress" required>
                                        @php $oldFlag = old('flag_progress', 'Belum Selesai'); @endphp
                                        <option value="Belum Selesai" @selected($oldFlag === 'Belum Selesai')>Belum Selesai</option>
                                        <option value="Proses" @selected($oldFlag === 'Proses')>Proses</option>
                                        <option value="Selesai" @selected($oldFlag === 'Selesai')>Selesai</option>
                                    </select>
                                    <div class="invalid-feedback" data-field="flag_progress">@error('flag_progress') {{ $message }} @enderror</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                                    <input type="date" class="form-input @error('tanggal_pengumpulan') is-invalid @enderror" id="tanggal_pengumpulan" name="tanggal_pengumpulan" value="{{ old('tanggal_pengumpulan') }}">
                                    <div class="invalid-feedback" data-field="tanggal_pengumpulan">@error('tanggal_pengumpulan') {{ $message }} @enderror</div>
                                </div>
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

    {{-- Modal Edit Data --}}
    <div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered"> 
            <form id="editForm" method="POST"> 
                @csrf 
                @method('PUT')
                <input type="hidden" name="_form" value="editForm"> 
                <input type="hidden" name="edit_id_fallback" id="edit_id_fallback" value="{{ session('edit_id') ?? '' }}">
                <div class="modal-content modern-modal">
                    <div class="modal-header">
                        <div class="modal-header-content"> <h5 class="modal-title">Edit Data Semesteran</h5> <p class="modal-subtitle">Perbarui informasi data</p> </div>
                        <button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                    </div>
                    <div class="modal-body">
                        
                        <div class="form-group autocomplete-container">
                            <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan <span class="required">*</span></label>
                            <input type="text" class="form-input @error('nama_kegiatan', 'editForm') is-invalid @enderror" id="edit_nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" required autocomplete="off">
                            <input type="hidden" id="edit_master_kegiatan_id" name="master_kegiatan_id" value="{{ old('master_kegiatan_id') }}">
                            <div class="autocomplete-suggestions" id="edit-kegiatan-suggestions"></div>
                            <div class="invalid-feedback" data-field="nama_kegiatan">@error('nama_kegiatan', 'editForm') {{ $message }} @enderror</div>
                            <div class="invalid-feedback" data-field="master_kegiatan_id">@error('master_kegiatan_id', 'editForm') {{ $message }} @enderror</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_BS_Responden" class="form-label">BS/Responden <span class="required">*</span></label>
                                    <input type="text" class="form-input @error('BS_Responden', 'editForm') is-invalid @enderror" id="edit_BS_Responden" name="BS_Responden" value="{{ old('BS_Responden') }}" required>
                                    <div class="invalid-feedback" data-field="BS_Responden">@error('BS_Responden', 'editForm') {{ $message }} @enderror</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group autocomplete-container">
                                    <label for="edit_pencacah" class="form-label">Pencacah <span class="required">*</span></label>
                                    <input type="text" class="form-input @error('pencacah', 'editForm') is-invalid @enderror" id="edit_pencacah" name="pencacah" value="{{ old('pencacah') }}" required autocomplete="off">
                                    <div class="autocomplete-suggestions" id="edit-pencacah-suggestions"></div>
                                    <div class="invalid-feedback" data-field="pencacah">@error('pencacah', 'editForm') {{ $message }} @enderror</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group autocomplete-container">
                                    <label for="edit_pengawas" class="form-label">Pengawas <span class="required">*</span></label>
                                    <input type="text" class="form-input @error('pengawas', 'editForm') is-invalid @enderror" id="edit_pengawas" name="pengawas" value="{{ old('pengawas') }}" required autocomplete="off">
                                    <div class="autocomplete-suggestions" id="edit-pengawas-suggestions"></div>
                                    <div class="invalid-feedback" data-field="pengawas">@error('pengawas', 'editForm') {{ $message }} @enderror</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_target_penyelesaian" class="form-label">Target Penyelesaian <span class="required">*</span></label>
                                    <input type="date" class="form-input @error('target_penyelesaian', 'editForm') is-invalid @enderror" id="edit_target_penyelesaian" name="target_penyelesaian" value="{{ old('target_penyelesaian') }}" required>
                                    <div class="invalid-feedback" data-field="target_penyelesaian">@error('target_penyelesaian', 'editForm') {{ $message }} @enderror</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_flag_progress" class="form-label">Flag Progress <span class="required">*</span></label>
                                    <select class="form-select @error('flag_progress', 'editForm') is-invalid @enderror" id="edit_flag_progress" name="flag_progress" required>
                                        <option value="Belum Selesai">Belum Selesai</option>
                                        <option value="Proses">Proses</option>
                                        <option value="Selesai">Selesai</option>
                                    </select>
                                    <div class="invalid-feedback" data-field="flag_progress">@error('flag_progress', 'editForm') {{ $message }} @enderror</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_tanggal_pengumpulan" class="form-label">Tanggal Pengumpulan</label>
                                    <input type="date" class="form-input @error('tanggal_pengumpulan', 'editForm') is-invalid @enderror" id="edit_tanggal_pengumpulan" name="tanggal_pengumpulan" value="{{ old('tanggal_pengumpulan') }}">
                                    <div class="invalid-feedback" data-field="tanggal_pengumpulan">@error('tanggal_pengumpulan', 'editForm') {{ $message }} @enderror</div>
                                </div>
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

    {{-- Modal Delete --}}
    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <form id="deleteForm" method="POST"> 
                @csrf
                {{-- Method (_method) di-set oleh JS --}}
                <div class="modal-content modern-modal">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="modal-close modal-close-white" data-bs-dismiss="modal" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> </button>
                    </div>
                    <div class="modal-body">
                        <div class="delete-icon"> <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg> </div>
                        <p class="delete-text" id="deleteModalBody">Hapus data Semesteran ini?</p> 
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn-danger" id="confirmDeleteButton"> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Ya, Hapus </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
{{-- Ini adalah skrip LENGKAP yang disesuaikan untuk 'semesteran' --}}
<script>
    function initAutocomplete(inputId, suggestionsId, searchUrl) {
        const input = document.getElementById(inputId);
        if (!input || !searchUrl) return;
        const suggestionsContainer = document.getElementById(suggestionsId);
        let hiddenInput = null;
        if (inputId === 'nama_kegiatan' || inputId === 'edit_nama_kegiatan') {
            const hiddenInputId = (inputId === 'edit_nama_kegiatan') ? 'edit_master_kegiatan_id' : 'master_kegiatan_id';
            hiddenInput = document.getElementById(hiddenInputId);
        }
        let debounceTimer, activeSuggestionIndex = -1;

        input.addEventListener('input', function() {
            const query = this.value;
            clearTimeout(debounceTimer);
            if (query.length < 1) {
                if (suggestionsContainer) suggestionsContainer.innerHTML = '';
                if (hiddenInput) hiddenInput.value = ''; 
                activeSuggestionIndex = -1; return;
            }
            debounceTimer = setTimeout(() => {
                const finalSearchUrl = `${searchUrl}&query=${encodeURIComponent(query)}`;
                fetch(finalSearchUrl)
                    .then(response => response.ok ? response.json() : Promise.reject(response))
                    .then(data => {
                        suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1;
                        if (!Array.isArray(data)) return;
                        data.forEach((item, index) => {
                            let textContent, idContent = null;
                            if (typeof item === 'object' && item !== null) {
                                textContent = item.nama_kegiatan; idContent = item.id_master_kegiatan;
                            } else if (typeof item === 'string') {
                                textContent = item;
                            } else { return; }
                            const div = document.createElement('div');
                            div.textContent = textContent; div.classList.add('autocomplete-suggestion-item');
                            if (idContent !== null) div.setAttribute('data-id', idContent);
                            div.onclick = () => {
                                input.value = textContent;
                                if (idContent !== null && hiddenInput) hiddenInput.value = idContent;
                                suggestionsContainer.innerHTML = '';
                                if(hiddenInput) {
                                    input.classList.remove('is-invalid');
                                    const errorDiv = hiddenInput.closest('.form-group').querySelector('.invalid-feedback[data-field="master_kegiatan_id"]');
                                    if (errorDiv) errorDiv.textContent = '';
                                }
                            };
                            div.onmouseover = () => {
                                document.querySelectorAll(`#${suggestionsId} .autocomplete-suggestion-item`).forEach(el => el.classList.remove('active'));
                                div.classList.add('active'); activeSuggestionIndex = index;
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
                e.preventDefault(); activeSuggestionIndex = (activeSuggestionIndex + 1) % suggestions.length;
            } else if (e.key === 'ArrowUp') {
                e.preventDefault(); activeSuggestionIndex = (activeSuggestionIndex - 1 + suggestions.length) % suggestions.length;
            } else if (e.key === 'Enter') {
                if (activeSuggestionIndex > -1) { e.preventDefault(); suggestions[activeSuggestionIndex].click(); activeSuggestionIndex = -1; }
            } else if (e.key === 'Escape') {
                suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1;
            }
            if(e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                 suggestions.forEach(el => el.classList.remove('active'));
                 if (suggestions[activeSuggestionIndex]) suggestions[activeSuggestionIndex].classList.add('active');
            }
        });
        document.addEventListener('click', (e) => {
            if (e.target.id !== inputId && suggestionsContainer && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.innerHTML = ''; activeSuggestionIndex = -1;
            }
        });
    }

    // --- URL Basis BARU ---
    const sosialSemesteranBaseUrl = `{{ route('sosial.semesteran.index') }}`;

    function editData(id) {
        const editModalEl = document.getElementById('editDataModal'); 
        if (!editModalEl) return;
        const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
        const editForm = document.getElementById('editForm');
        editForm.action = `${sosialSemesteranBaseUrl}/${id}`;
        if (typeof clearFormErrors === 'function') clearFormErrors(editForm);
        const fallbackInput = editForm.querySelector('#edit_id_fallback');
        if(fallbackInput) fallbackInput.value = id;
        fetch(`${sosialSemesteranBaseUrl}/${id}/edit`)
            .then(response => response.ok ? response.json() : Promise.reject(response))
            .then(data => {
                document.getElementById('edit_nama_kegiatan').value = data.nama_kegiatan || '';
                document.getElementById('edit_master_kegiatan_id').value = data.master_kegiatan_id || ''; 
                document.getElementById('edit_BS_Responden').value = data.BS_Responden || '';
                document.getElementById('edit_pencacah').value = data.pencacah || '';
                document.getElementById('edit_pengawas').value = data.pengawas || '';
                document.getElementById('edit_target_penyelesaian').value = data.target_penyelesaian || ''; 
                document.getElementById('edit_flag_progress').value = data.flag_progress || 'Belum Selesai';
                document.getElementById('edit_tanggal_pengumpulan').value = data.tanggal_pengumpulan || ''; 
                editModal.show();
            }).catch(error => { console.error("Error loading edit data:", error); alert('Tidak dapat memuat data.'); });
    }

    function deleteData(id) {
        const deleteModalEl = document.getElementById('deleteDataModal'); 
        if (!deleteModalEl) return;
        const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `${sosialSemesteranBaseUrl}/${id}`;
        let methodInput = deleteForm.querySelector('input[name="_method"]'); 
        if (!methodInput) { 
            methodInput = document.createElement('input'); 
            methodInput.type = 'hidden'; methodInput.name = '_method'; 
            deleteForm.appendChild(methodInput); 
        } 
        methodInput.value = 'DELETE';
        deleteForm.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
        document.getElementById('deleteModalBody').innerText = `Apakah Anda yakin ingin menghapus data Semesteran ini?`;
        const confirmBtn = document.getElementById('confirmDeleteButton');
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        newConfirmBtn.addEventListener('click', (e) => { e.preventDefault(); deleteForm.submit(); });
        deleteModal.show();
    }

    /** AJAX Helper Functions */
    function clearFormErrors(form) { 
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid')); 
        form.querySelectorAll('.invalid-feedback[data-field]').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
    }
    function showFormErrors(form, errors) { 
        for (const [field, messages] of Object.entries(errors)) { 
            const input = form.querySelector(`[name="${field}"]`); 
            const errorDiv = form.querySelector(`.invalid-feedback[data-field="${field}"]`); 
            if (input) input.classList.add('is-invalid'); 
            if (errorDiv) { errorDiv.textContent = messages[0]; errorDiv.style.display = 'block'; }
            if (field === 'master_kegiatan_id') {
                 const visibleInput = form.querySelector(`[name="nama_kegiatan"]`);
                 const visibleErrorDiv = form.querySelector(`.invalid-feedback[data-field="nama_kegiatan"]`);
                 if(visibleInput) visibleInput.classList.add('is-invalid');
                 if(visibleErrorDiv) { visibleErrorDiv.textContent = messages[0]; visibleErrorDiv.style.display = 'block'; }
            }
        } 
    }
    async function handleFormSubmitAjax(event, form, modalInstance) { 
        event.preventDefault(); 
        const sb = form.querySelector('button[type="submit"]'); 
        const sp = sb.querySelector('.spinner-border'); 
        sb.disabled = true; if (sp) sp.classList.remove('d-none'); 
        clearFormErrors(form); 
        try { 
            const fd = new FormData(form); 
            const response = await fetch(form.action, { 
                method: form.method, body: fd, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': fd.get('_token') } 
            }); 
            const data = await response.json(); 
            if (!response.ok) { 
                if (response.status === 422 && data.errors) showFormErrors(form, data.errors); 
                else alert(data.message || 'Terjadi error.'); 
            } else { 
                modalInstance.hide(); window.location.reload(); 
            } 
        } catch (error) { 
            console.error('Fetch error:', error); alert('Tidak dapat terhubung ke server.'); 
        } finally { 
            sb.disabled = false; if (sp) sp.classList.add('d-none'); 
        } 
    }

    /** DOM Ready */
    document.addEventListener('DOMContentLoaded', function() {

        // --- Init Autocomplete (Gunakan route baru) ---
        @if(Route::has('sosial.semesteran.searchKegiatan'))
            const kegiatanSearchUrl = `{{ route("sosial.semesteran.searchKegiatan") }}?`; 
            initAutocomplete('nama_kegiatan', 'kegiatan-suggestions', kegiatanSearchUrl);
            initAutocomplete('edit_nama_kegiatan', 'edit-kegiatan-suggestions', kegiatanSearchUrl);
        @endif
        @if(Route::has('sosial.semesteran.searchPetugas'))
            const petugasSearchUrl = '{{ route("sosial.semesteran.searchPetugas") }}?';
            initAutocomplete('pencacah', 'pencacah-suggestions', petugasSearchUrl);
            initAutocomplete('pengawas', 'pengawas-suggestions', petugasSearchUrl);
            initAutocomplete('edit_pencacah', 'edit-pencacah-suggestions', petugasSearchUrl);
            initAutocomplete('edit_pengawas', 'edit-pengawas-suggestions', petugasSearchUrl);
        @endif

        // --- Init AJAX Form Handlers ---
        const tme = document.getElementById('tambahDataModal'); 
        const tf = document.getElementById('tambahForm'); 
        if (tme && tf) { 
            const tm = bootstrap.Modal.getOrCreateInstance(tme); 
            tf.addEventListener('submit', (e) => handleFormSubmitAjax(e, tf, tm)); 
            tme.addEventListener('hidden.bs.modal', () => { clearFormErrors(tf); tf.reset(); }); 
        }
        const eme = document.getElementById('editDataModal'); 
        const ef = document.getElementById('editForm'); 
        if (eme && ef) { 
            const em = bootstrap.Modal.getOrCreateInstance(eme); 
            ef.addEventListener('submit', (e) => handleFormSubmitAjax(e, ef, em)); 
            eme.addEventListener('hidden.bs.modal', () => clearFormErrors(ef)); 
        }

        // --- Select All & Bulk Delete ---
        const sa = document.getElementById('selectAll'); 
        const rcb = document.querySelectorAll('.row-checkbox'); 
        const bdb = document.getElementById('bulkDeleteBtn'); 
        const df = document.getElementById('deleteForm'); 
        const dme = document.getElementById('deleteDataModal');
        
        function ubdbs() { 
            const cc = document.querySelectorAll('.row-checkbox:checked').length; 
            if (bdb) bdb.disabled = cc === 0; 
        }
        sa?.addEventListener('change', () => { rcb.forEach(cb => cb.checked = sa.checked); ubdbs(); });
        rcb.forEach(cb => cb.addEventListener('change', ubdbs));
        ubdbs(); 
        
        bdb?.addEventListener('click', () => {
            const count = document.querySelectorAll('.row-checkbox:checked').length; 
            if (count === 0 || !dme || !df) return; 
            df.action = '{{ route("sosial.semesteran.bulkDelete") }}'; 
            let mi = df.querySelector('input[name="_method"]'); 
            if (mi) mi.remove(); 
            df.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove()); 
            document.querySelectorAll('.row-checkbox:checked').forEach(cb => { 
                const i = document.createElement('input'); 
                i.type = 'hidden'; i.name = 'ids[]'; i.value = cb.value; 
                df.appendChild(i); 
            });
            document.getElementById('deleteModalBody').innerText = `Hapus ${count} data terpilih?`;
            const ncb = document.getElementById('confirmDeleteButton').cloneNode(true); 
            document.getElementById('confirmDeleteButton').parentNode.replaceChild(ncb, document.getElementById('confirmDeleteButton'));
            ncb.addEventListener('click', (e) => { e.preventDefault(); df.submit(); }); 
        });

        // --- Filters Per Page & Tahun & Kegiatan ---
        const pps = document.getElementById('perPageSelect');
        const ts = document.getElementById('tahunSelect');
        const ks = document.getElementById('kegiatanSelect');
        const searchForm = document.querySelector('form.search-form');
        
        function hfc() {
            const cu = new URL('{{ route("sosial.semesteran.index") }}');
            const p = new URLSearchParams(window.location.search); 
            if (pps) p.set('per_page', pps.value);
            if (ts) p.set('tahun', ts.value);
            if (ks) {
                if (ks.value) { p.set('kegiatan', ks.value); }
                else { p.delete('kegiatan'); } 
            }
            const currentSearch = document.getElementById('searchInput')?.value;
            if (currentSearch && currentSearch.trim() !== '') p.set('search', currentSearch);
            else p.delete('search');
            p.set('page', 1); 
            if (this && this.id === 'searchInput') p.set('focus_search', '1');
            window.location.href = cu.pathname + '?' + p.toString();
        }

        if (pps) pps.addEventListener('change', hfc);
        if (ts) ts.addEventListener('change', hfc);
        if (ks) ks.addEventListener('change', hfc);

        const searchInput = document.getElementById('searchInput');
        let searchDebounceTimer; 
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchDebounceTimer);
                searchDebounceTimer = setTimeout(() => { hfc.call(searchInput); }, 500); 
            });
        }
         if(searchForm) {
             searchForm.addEventListener('submit', (e) => { e.preventDefault(); hfc.call(searchInput || null); });
         }
        
         const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('focus_search') && searchInput) {
            urlParams.delete('focus_search');
            const newUrl = window.location.pathname + '?' + urlParams.toString();
            history.replaceState(null, '', newUrl);
            setTimeout(() => { 
                const len = searchInput.value.length;
                searchInput.focus(); searchInput.setSelectionRange(len, len);
            }, 100); 
        }

        // --- Fallback Error Modals ---
        @if (session('error_modal') == 'tambahDataModal' && $errors->any()) 
            const tmef = document.getElementById('tambahDataModal'); 
            if (tmef) {
                showFormErrors(tmef.querySelector('form'), @json($errors->toArray()));
                bootstrap.Modal.getOrCreateInstance(tmef).show(); 
            }
        @endif
        
        @if (session('error_modal') == 'editDataModal' && $errors->getBag('editForm')->any() && session('edit_id')) 
            const eid_fb = '{{ session('edit_id') }}'; 
            if (eid_fb) { 
                const emef = document.getElementById('editDataModal'); 
                if(emef) { 
                    const edf = document.getElementById('editForm'); 
                    edf.action = `${sosialSemesteranBaseUrl}/${eid_fb}`; 
                    editData(eid_fb); 
                    emef.addEventListener('shown.bs.modal', () => {
                         showFormErrors(edf, @json($errors->getBag('editForm')->toArray()));
                    }, { once: true });
                } 
            } 
        @endif

        // --- Auto-hide Alerts ---
        document.querySelectorAll('.alert-dismissible[role="alert"]:not(.alert-warning)').forEach(function (alert) { 
            if (!alert.closest('.modal') && {{ session('auto_hide', 'false') ? 'true' : 'false' }}) { 
                setTimeout(() => { bootstrap.Alert.getOrCreateInstance(alert).close(); }, 5000); 
            } 
        });

        // --- Update Export Modal ---
        const exportModalEl = document.getElementById('exportModal'); 
        if(exportModalEl) { 
            exportModalEl.addEventListener('show.bs.modal', function () {
                const totalData = {{ $listData->total() }}; 
                const currentPageData = {{ $listData->count() }};
                document.querySelector('#exportModal select[name="dataRange"] option[value="current_page"]').textContent = `Hanya Halaman Ini (${currentPageData} data)`;
                document.querySelector('#exportModal select[name="dataRange"] option[value="all"]').textContent = `Semua Data (${totalData} data)`; 
                document.querySelector('#exportForm input[name="tahun"]').value = '{{ $selectedTahun ?? date('Y') }}';
                document.querySelector('#exportForm input[name="kegiatan"]').value = '{{ $selectedKegiatan ?? '' }}';
                document.querySelector('#exportForm input[name="search"]').value = '{{ $search ?? '' }}';
                document.querySelector('#exportForm input[name="page"]').value = '{{ $listData->currentPage() }}';
                document.querySelector('#exportForm input[name="per_page"]').value = '{{ $listData->perPage() }}';
            });
        }
    });
</script>
@endpush