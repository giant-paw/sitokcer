@extends('layouts.app')

@section('title', 'Detail Petugas')
@section('header-title', 'Detail Petugas')

@push('styles')
    {{-- CSS Kustom untuk Halaman Detail --}}
    <style>
        /* Header Halaman Detail */
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--spacing-md, 1rem);
            /* Menggunakan variabel spacing dari global */
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: var(--spacing-md, 1rem);
        }

        /* Tombol Kembali (Komponen Baru) */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-sm, 0.5rem);
            padding: 0.5rem 0.75rem;
            background-color: var(--color-gray-100, #f3f4f6);
            color: var(--color-gray-700, #374151);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: var(--border-radius-sm, 0.375rem);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition-base, all 0.2s ease-in-out);
        }

        .btn-back:hover {
            background-color: var(--color-gray-200, #e5e7eb);
            color: var(--color-gray-900, #111827);
        }

        .header-info {
            margin: 0;
        }

        /* Judul Halaman (menggunakan style .page-title & .page-subtitle dari global) */
        .detail-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--color-gray-800, #1f2937);
            margin-bottom: 4px;
        }

        .detail-subtitle {
            font-size: 0.9375rem;
            color: var(--color-gray-500, #6b7280);
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: var(--spacing-sm, 0.5rem);
            flex-wrap: wrap;
        }

        /* Kartu Detail (Komponen Baru) */
        .detail-card {
            background: var(--card-bg, #ffffff);
            border-radius: var(--border-radius-xl, 1rem);
            border: 1px solid var(--border-color, #e5e7eb);
            overflow: hidden;
            margin-bottom: var(--spacing-lg, 1.5rem); /* Gunakan margin global */
            height: 100%; /* Untuk menyamakan tinggi di grid */
        }

        .detail-card-header {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm, 0.5rem);
            padding: var(--spacing-md, 1rem) var(--spacing-lg, 1.5rem);
            border-bottom: 1px solid var(--border-color, #e5e7eb);
            background: var(--color-gray-50, #f9fafb);
        }

        .card-header-icon {
            flex-shrink: 0;
            color: var(--primary-color, #4A90E2);
            /* Sesuaikan warnanya jika perlu */
        }

        .card-header-title {
            font-size: 1.125rem;
            /* 18px */
            font-weight: 600;
            color: var(--color-gray-900, #111827);
            margin: 0;
        }

        .detail-card-body {
            padding: var(--spacing-lg, 1.5rem);
        }

        /* Daftar Info (Komponen Baru) */
        .info-item {
            display: grid;
            grid-template-columns: 140px 1fr;
            /* Lebar label tetap */
            gap: var(--spacing-md, 1rem);
            padding: var(--spacing-sm, 0.5rem) 0;
        }

        .info-item+.info-item {
            border-top: 1px solid var(--color-gray-100, #f3f4f6);
        }

        .info-label {
            font-weight: 500;
            color: var(--color-gray-500, #6b7280);
            font-size: var(--font-size-sm, 0.875rem);
        }

        .info-value {
            font-weight: 500;
            color: var(--color-gray-800, #1f2937);
            font-size: var(--font-size-sm, 0.875rem);
            word-break: break-word; /* Agar teks panjang tidak overflow */
        }

        .contact-link {
            color: var(--primary-color, #4A90E2);
            text-decoration: none;
            font-weight: 600;
        }

        .contact-link:hover {
            text-decoration: underline;
        }

         /* Badge dari global.css sudah ada */
         .badge.badge-blue {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }
        .badge.badge-purple {
            background: rgba(139, 92, 246, 0.1);
            color: #7c3aed;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-4 py-4">

        {{-- Header with Back Button --}}
        <div class="detail-header mb-4">
            <div class="header-left">
                <a href="{{ route('master.petugas.index') }}" class="btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali
                </a>
                <div class="header-info">
                    {{-- [DIUBAH] Menggunakan kelas .page-title dan .page-subtitle --}}
                    <h2 class="page-title">{{ $petugas->nama_petugas }}</h2>
                    <p class="page-subtitle">Informasi lengkap petugas</p>
                </div>
            </div>
        </div>

        {{-- Detail Cards (Struktur kustom dipertahankan) --}}
        <div class="row g-4">
            {{-- Informasi Utama --}}
            <div class="col-lg-6">
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="card-header-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <h3 class="card-header-title">Informasi Utama</h3>
                    </div>
                    <div class="detail-card-body">
                        <div class="info-item">
                            <label class="info-label">Nama Petugas</label>
                            <div class="info-value">{{ $petugas->nama_petugas ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Kategori</label>
                            <div class="info-value">
                                @if ($petugas->kategori)
                                    {{-- [DIUBAH] Badge dari global.css --}}
                                    <span class="badge {{ $petugas->kategori == 'Mitra' ? 'badge-blue' : 'badge-purple' }}">
                                        {{ $petugas->kategori }}
                                    </span>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">NIK</label>
                            <div class="info-value text-secondary">{{ $petugas->nik ?? '-' }}</div> {{-- [DIUBAH] text-secondary --}}
                        </div>
                        <div class="info-item">
                            <label class="info-label">Posisi</label>
                            <div class="info-value text-secondary">{{ $petugas->posisi ?? '-' }}</div> {{-- [DIUBAH] text-secondary --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kontak --}}
            <div class="col-lg-6">
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="card-header-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="card-header-title">Kontak</h3>
                    </div>
                    <div class="detail-card-body">
                        <div class="info-item">
                            <label class="info-label">No. HP</label>
                            <div class="info-value">
                                @if ($petugas->no_hp)
                                    <a href="tel:{{ $petugas->no_hp }}" class="contact-link">
                                        {{ $petugas->no_hp }}
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Email</label>
                            <div class="info-value">
                                @if ($petugas->email)
                                    <a href="mailto:{{ $petugas->email }}" class="contact-link">
                                        {{ $petugas->email }}
                                    </a>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Alamat</label>
                            <div class="info-value text-secondary">{{ $petugas->alamat ?? '-' }}</div> {{-- [DIUBAH] text-secondary --}}
                        </div>
                        <div class="info-item">
                            <label class="info-label">Kecamatan</label>
                            <div class="info-value text-secondary">{{ $petugas->kecamatan ?? '-' }}</div> {{-- [DIUBAH] text-secondary --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Data Pribadi --}}
            <div class="col-lg-6">
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="card-header-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                        </div>
                        <h3 class="card-header-title">Data Pribadi</h3>
                    </div>
                    <div class="detail-card-body">
                        <div class="info-item">
                            <label class="info-label">Tanggal Lahir</label>
                            <div class="info-value text-secondary"> {{-- [DIUBAH] text-secondary --}}
                                {{ $petugas->tgl_lahir ? $petugas->tgl_lahir->isoFormat('D MMMM YYYY') : '-' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Pendidikan Terakhir</label>
                            <div class="info-value text-secondary">{{ $petugas->pendidikan ?? '-' }}</div> {{-- [DIUBAH] text-secondary --}}
                        </div>
                        <div class="info-item">
                            <label class="info-label">Pekerjaan</label>
                            <div class="info-value text-secondary">{{ $petugas->pekerjaan ?? '-' }}</div> {{-- [DIUBAH] text-secondary --}}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Timeline / Metadata --}}
            <div class="col-lg-6">
                <div class="detail-card">
                    <div class="detail-card-header">
                        <div class="card-header-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                        <h3 class="card-header-title">Informasi Sistem</h3>
                    </div>
                    <div class="detail-card-body">
                        <div class="info-item">
                            <label class="info-label">ID Petugas</label>
                            <div class="info-value text-monospace text-secondary">{{ $petugas->id_petugas }}</div> {{-- [DIUBAH] text-secondary --}}
                        </div>
                        @if ($petugas->created_at)
                            <div class="info-item">
                                <label class="info-label">Tanggal Dibuat</label>
                                <div class="info-value text-secondary">{{ $petugas->created_at->isoFormat('D MMMM YYYY, HH:mm') }}</div>
                            </div>
                        @endif
                        @if ($petugas->updated_at)
                            <div class="info-item">
                                <label class="info-label">Terakhir Diperbarui</label>
                                <div class="info-value text-secondary">{{ $petugas->updated_at->isoFormat('D MMMM YYYY, HH:mm') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Delete (Sudah sesuai global.css) --}}
    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
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
                        <p class="delete-text" id="deleteModalBody">Apakah Anda yakin ingin menghapus data petugas ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn-danger" id="confirmDeleteButton">
                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
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
    /** Delete Data */
    function deleteData(id) {
        const deleteForm = document.getElementById('deleteForm');
        // Set action URL
        deleteForm.action = `{{ url('master-petugas') }}/${id}`; // Menggunakan url() helper
        
        // Pastikan modal body text benar
        document.getElementById('deleteModalBody').innerText = 'Apakah Anda yakin ingin menghapus data petugas ini? Tindakan ini tidak dapat dibatalkan.';
        
        // Re-attach listener untuk konfirmasi
        const confirmBtn = document.getElementById('confirmDeleteButton');
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        newConfirmBtn.addEventListener('click', () => {
            deleteForm.submit();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
         // Auto-hide alerts
        const aha = document.querySelectorAll('.alert-success');
        aha.forEach(a => {
            if (!a.closest('.modal')) {
                setTimeout(() => {
                    a.style.transition = 'opacity 0.5s ease';
                    a.style.opacity = '0';
                    setTimeout(() => a.remove(), 500);
                }, 5000);
            }
        });
    });
</script>
@endpush