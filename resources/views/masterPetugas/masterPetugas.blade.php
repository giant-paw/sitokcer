@extends('layouts.app')

@section('title', 'Master Petugas')
@section('header-title', 'Master Petugas')

@section('content')
<div class="container-fluid px-4 py-4">
    
    {{-- Page Header --}}
    <div class="page-header mb-4">
        <div class="header-content">
            <h2 class="page-title">Master Petugas</h2>
            <p class="page-subtitle">Kelola data petugas dan mitra</p>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="data-card">
        
        {{-- Toolbar --}}
        <div class="toolbar">
            <div class="toolbar-left">
                <button type="button" class="btn-action btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    Tambah Baru
                </button>
               <button type="button" class="btn-action btn-success" data-bs-toggle="modal" data-bs-target="#exportModal">
                         <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Ekspor Hasil
                    </button>
                <button type="button" class="btn-action btn-danger" id="bulkDeleteBtn" data-bs-toggle="modal" data-bs-target="#deleteDataModal" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Hapus Terpilih
                </button>
            </div>

            <div class="toolbar-right">
                <div class="filter-group">
                    <label class="filter-label">Tampilkan:</label>
                    <select class="filter-select" id="perPageSelect">
                        @php $options = [10, 15, 25, 50, 100, 'all']; @endphp
                        @foreach($options as $option)
                            <option value="{{ $option }}" {{ (request('per_page', 15) == $option) ? 'selected' : '' }}>
                                {{ $option == 'all' ? 'Semua' : $option }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <form action="{{ route('master.petugas.index') }}" method="GET" class="search-form">
                    <input type="text" class="search-input" name="search" value="{{ $search ?? '' }}" placeholder="Cari nama, NIK, atau kategori...">
                    <button class="search-btn" type="submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Alert --}}
        @if(session('success'))
            <div class="alert-success">
                <div class="alert-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close" data-bs-dismiss="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Table --}}
        <form id="bulkDeleteForm" action="{{ route('master.petugas.bulkDelete') }}" method="POST">
            @csrf
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th class="th-checkbox">
                                <input type="checkbox" class="table-checkbox" id="selectAll">
                            </th>
                            <th>Nama Petugas</th>
                            <th>Kategori</th>
                            <th>NIK</th>
                            <th>No HP</th>
                            <th>Posisi</th>
                            <th class="th-action">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($petugas as $p)
                            <tr>
                                <td class="td-checkbox">
                                    <input type="checkbox" class="table-checkbox row-checkbox" name="ids[]" value="{{ $p->id_petugas }}">
                                </td>
                                <td>
                                    <div class="user-name">{{ $p->nama_petugas }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $p->kategori == 'Mitra' ? 'blue' : 'purple' }}">
                                        {{ $p->kategori }}
                                    </span>
                                </td>
                                <td class="text-secondary">{{ $p->nik }}</td>
                                <td class="text-secondary">{{ $p->no_hp }}</td>
                                <td class="text-secondary">{{ $p->posisi }}</td>
                                <td class="td-action">
                                    <div class="action-buttons">
                                        <a href="{{ route('master.petugas.show', $p) }}" class="btn-icon btn-icon-view" title="Lihat">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                <circle cx="12" cy="12" r="3"></circle>
                                            </svg>
                                        </a>
                                        <button type="button" class="btn-icon btn-icon-edit" title="Edit" data-bs-toggle="modal" data-bs-target="#editDataModal" onclick="editData({{ $p->id_petugas }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                                            </svg>
                                        </button>
                                        <button type="button" class="btn-icon btn-icon-delete" title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteDataModal" onclick="deleteData({{ $p->id_petugas }})">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <div class="empty-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <path d="m21 21-4.35-4.35"></path>
                                        </svg>
                                    </div>
                                    <p class="empty-text">Tidak ada data ditemukan</p>
                                    @if($search ?? false)
                                        <a href="{{ route('master.petugas.index') }}" class="empty-link">Reset pencarian</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        {{-- Footer --}}
        <div class="table-footer">
            <div class="footer-info">
                Menampilkan {{ $petugas->firstItem() ?? 0 }} - {{ $petugas->lastItem() ?? 0 }} dari {{ $petugas->total() }} data
            </div>
            <div class="footer-pagination">
                {{ $petugas->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('master.petugas.export') }}" method="GET">
        <div class="modal-header">
          <h5 class="modal-title" id="exportModalLabel">
            Export Data Petugas
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Rentang Data</label>
            <select class="form-select" name="dataRange" required>
              <option value="all">Semua Data</option>
              <option value="current_page">Halaman Ini Saja</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Format File</label>
            <select class="form-select" name="exportFormat" required>
              <option value="excel">Excel (.xlsx)</option>
              <option value="csv">CSV (.csv)</option>
            </select>
          </div>
          <!-- Param tersembunyi buat filter aktif dan page -->
          <input type="hidden" name="search" value="{{ request('search')?? '' }}">
          <input type="hidden" name="page" value="{{ request('page', 1) }}">
          <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="bi bi-download"></i> Export Data
          </button>
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <form action="{{ route('master.petugas.store') }}" method="POST">
            @csrf
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <div class="modal-header-content">
                        <h5 class="modal-title">Tambah Petugas Baru</h5>
                        <p class="modal-subtitle">Isi form di bawah untuk menambahkan data petugas</p>
                    </div>
                    <button type="button" class="modal-close" data-bs-dismiss="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="modal-grid-layout">
                        <!-- Column 1 -->
                        <div class="modal-column">
                            <div class="form-section">
                                <h6 class="section-title">Informasi Pribadi</h6>
                                <div class="form-group">
                                    <label class="form-label">Nama Petugas <span class="required">*</span></label>
                                    <input type="text" class="form-input @error('nama_petugas') is-invalid @enderror" name="nama_petugas" value="{{ old('nama_petugas') }}" required placeholder="Masukkan nama lengkap">
                                    @error('nama_petugas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">NIK</label>
                                    <input type="text" class="form-input @error('nik') is-invalid @enderror" name="nik" value="{{ old('nik') }}" placeholder="16 digit NIK">
                                    @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-input @error('tgl_lahir') is-invalid @enderror" name="tgl_lahir" value="{{ old('tgl_lahir') }}">
                                    @error('tgl_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Pendidikan Terakhir</label>
                                    <input type="text" class="form-input @error('pendidikan') is-invalid @enderror" name="pendidikan" value="{{ old('pendidikan') }}" placeholder="Contoh: S1">
                                    @error('pendidikan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="modal-column">
                            <div class="form-section">
                                <h6 class="section-title">Informasi Kontak</h6>
                                <div class="form-group">
                                    <label class="form-label">No HP</label>
                                    <input type="text" class="form-input @error('no_hp') is-invalid @enderror" name="no_hp" value="{{ old('no_hp') }}" placeholder="+62 812 xxxx xxxx">
                                    @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="email@example.com">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Kecamatan</label>
                                    <input type="text" class="form-input @error('kecamatan') is-invalid @enderror" name="kecamatan" value="{{ old('kecamatan') }}" placeholder="Nama kecamatan">
                                    @error('kecamatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Alamat</label>
                                    <textarea class="form-input @error('alamat') is-invalid @enderror" name="alamat" rows="3" placeholder="Alamat lengkap">{{ old('alamat') }}</textarea>
                                    @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Column 3 -->
                        <div class="modal-column">
                            <div class="form-section">
                                <h6 class="section-title">Informasi Pekerjaan</h6>
                                <div class="form-group">
                                    <label class="form-label">Kategori</label>
                                    <select class="form-select @error('kategori') is-invalid @enderror" name="kategori">
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="Mitra" {{ old('kategori') == 'Mitra' ? 'selected' : '' }}>Mitra</option>
                                        <option value="Organik BPS" {{ old('kategori') == 'Organik BPS' ? 'selected' : '' }}>Organik BPS</option>
                                    </select>
                                    @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Posisi</label>
                                    <input type="text" class="form-input @error('posisi') is-invalid @enderror" name="posisi" value="{{ old('posisi') }}" placeholder="Jabatan/posisi">
                                    @error('posisi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Pekerjaan</label>
                                    <input type="text" class="form-input @error('pekerjaan') is-invalid @enderror" name="pekerjaan" value="{{ old('pekerjaan') }}" placeholder="Bidang pekerjaan">
                                    @error('pekerjaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        Batal
                    </button>
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Simpan Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <div class="modal-header-content">
                        <h5 class="modal-title">Edit Data Petugas</h5>
                        <p class="modal-subtitle">Perbarui informasi petugas yang diperlukan</p>
                    </div>
                    <button type="button" class="modal-close" data-bs-dismiss="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="modal-grid-layout">
                        <!-- Column 1 -->
                        <div class="modal-column">
                            <div class="form-section">
                                <h6 class="section-title">Informasi Pribadi</h6>
                                <div class="form-group">
                                    <label class="form-label">Nama Petugas <span class="required">*</span></label>
                                    <input type="text" class="form-input @error('nama_petugas', 'edit_error') is-invalid @enderror" id="edit_nama_petugas" name="nama_petugas" required placeholder="Masukkan nama lengkap">
                                    @error('nama_petugas', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">NIK</label>
                                    <input type="text" class="form-input @error('nik', 'edit_error') is-invalid @enderror" id="edit_nik" name="nik" placeholder="16 digit NIK">
                                    @error('nik', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-input @error('tgl_lahir', 'edit_error') is-invalid @enderror" id="edit_tgl_lahir" name="tgl_lahir">
                                    @error('tgl_lahir', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Pendidikan Terakhir</label>
                                    <input type="text" class="form-input @error('pendidikan', 'edit_error') is-invalid @enderror" id="edit_pendidikan" name="pendidikan" placeholder="Contoh: S1">
                                    @error('pendidikan', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="modal-column">
                            <div class="form-section">
                                <h6 class="section-title">Informasi Kontak</h6>
                                <div class="form-group">
                                    <label class="form-label">No HP</label>
                                    <input type="text" class="form-input @error('no_hp', 'edit_error') is-invalid @enderror" id="edit_no_hp" name="no_hp" placeholder="+62 812 xxxx xxxx">
                                    @error('no_hp', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-input @error('email', 'edit_error') is-invalid @enderror" id="edit_email" name="email" placeholder="email@example.com">
                                    @error('email', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Kecamatan</label>
                                    <input type="text" class="form-input @error('kecamatan', 'edit_error') is-invalid @enderror" id="edit_kecamatan" name="kecamatan" placeholder="Nama kecamatan">
                                    @error('kecamatan', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Alamat</label>
                                    <textarea class="form-input @error('alamat', 'edit_error') is-invalid @enderror" id="edit_alamat" name="alamat" rows="3" placeholder="Alamat lengkap"></textarea>
                                    @error('alamat', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Column 3 -->
                        <div class="modal-column">
                            <div class="form-section">
                                <h6 class="section-title">Informasi Pekerjaan</h6>
                                <div class="form-group">
                                    <label class="form-label">Kategori</label>
                                    <select class="form-select @error('kategori', 'edit_error') is-invalid @enderror" id="edit_kategori" name="kategori">
                                        <option value="">-- Pilih Kategori --</option>
                                        <option value="Mitra">Mitra</option>
                                        <option value="Organik BPS">Organik BPS</option>
                                    </select>
                                    @error('kategori', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Posisi</label>
                                    <input type="text" class="form-input @error('posisi', 'edit_error') is-invalid @enderror" id="edit_posisi" name="posisi" placeholder="Jabatan/posisi">
                                    @error('posisi', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Pekerjaan</label>
                                    <input type="text" class="form-input @error('pekerjaan', 'edit_error') is-invalid @enderror" id="edit_pekerjaan" name="pekerjaan" placeholder="Bidang pekerjaan">
                                    @error('pekerjaan', 'edit_error') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        Batal
                    </button>
                    <button type="submit" class="btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Delete --}}
<div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content modern-modal">
                <div class="modal-header modal-header-danger">
                    <h5 class="modal-title">Konfirmasi Hapus</h5>
                    <button type="button" class="modal-close modal-close-white" data-bs-dismiss="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="delete-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <p class="delete-text" id="deleteModalBody">Apakah Anda yakin ingin menghapus data ini?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn-danger" id="confirmDeleteButton">Ya, Hapus</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function editData(id) {
        const editForm = document.getElementById('editForm');
        editForm.action = `/master-petugas/${id}`;

        fetch(`/master-petugas/${id}/edit`)
            .then(response => {
                if (!response.ok) throw new Error('Data petugas tidak ditemukan.');
                return response.json();
            })
            .then(data => {
                document.getElementById('edit_nama_petugas').value = data.nama_petugas || '';
                document.getElementById('edit_kategori').value = data.kategori || '';
                document.getElementById('edit_nik').value = data.nik || '';
                document.getElementById('edit_alamat').value = data.alamat || '';
                document.getElementById('edit_no_hp').value = data.no_hp || '';
                document.getElementById('edit_posisi').value = data.posisi || '';
                document.getElementById('edit_email').value = data.email || '';
                document.getElementById('edit_pendidikan').value = data.pendidikan || '';
                document.getElementById('edit_tgl_lahir').value = data.tgl_lahir_formatted || '';
                document.getElementById('edit_kecamatan').value = data.kecamatan || '';
                document.getElementById('edit_pekerjaan').value = data.pekerjaan || '';

                editForm.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                editForm.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            })
            .catch(error => {
                console.error('Error fetching data for edit:', error);
                alert('Tidak dapat memuat data untuk diedit. Silakan coba lagi.');
                const editModal = bootstrap.Modal.getInstance(document.getElementById('editDataModal'));
                if (editModal) editModal.hide();
            });
    }

    function deleteData(id) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/master-petugas/${id}`;
        document.getElementById('deleteModalBody').innerText = 'Apakah Anda yakin ingin menghapus data petugas ini? Tindakan ini tidak dapat dibatalkan.';
        document.getElementById('confirmDeleteButton').onclick = function() {
            deleteForm.submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');
        const confirmDeleteButton = document.getElementById('confirmDeleteButton');
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

        function updateBulkDeleteButtonState() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            bulkDeleteBtn.disabled = checkedCount === 0;
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                rowCheckboxes.forEach(cb => cb.checked = this.checked);
                updateBulkDeleteButtonState();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkDeleteButtonState);
        });
        updateBulkDeleteButtonState();

        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', function() {
                const count = document.querySelectorAll('.row-checkbox:checked').length;
                document.getElementById('deleteModalBody').innerText = `Apakah Anda yakin ingin menghapus ${count} data petugas yang dipilih? Tindakan ini tidak dapat dibatalkan.`;
                confirmDeleteButton.onclick = function() {
                    bulkDeleteForm.submit();
                }
            });
        }

        @if (session('error_modal') == 'tambahDataModal' && $errors->any())
            const tambahModalEl = document.getElementById('tambahDataModal');
            if (tambahModalEl) {
                const tambahModal = new bootstrap.Modal(tambahModalEl);
                tambahModal.show();
            }
        @endif

        @if (session('error_modal') == 'editDataModal' && $errors->any() && session('edit_id'))
            const editModalEl = document.getElementById('editDataModal');
            const editId = {{ session('edit_id') }};
            if (editModalEl && editId) {
                const editModal = new bootstrap.Modal(editModalEl);
                editData(editId); 
                setTimeout(() => {
                    @foreach ($errors->getBag('edit_error')->keys() as $field)
                        const fieldElement = document.getElementById('edit_{{ $field }}');
                        if (fieldElement) {
                            fieldElement.classList.add('is-invalid');
                            const errorElement = fieldElement.closest('.form-group').querySelector('.invalid-feedback');
                            if (errorElement) {
                                errorElement.textContent = '{{ $errors->getBag("edit_error")->first($field) }}';
                            }
                        }
                    @endforeach
                }, 500);
                editModal.show();
            }
        @endif

        const autoHideAlerts = document.querySelectorAll('.alert-success');
        autoHideAlerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>
@endpush

