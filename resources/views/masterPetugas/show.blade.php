@extends('layouts.app')

@section('title', 'Detail Petugas')
@section('header-title', 'Detail Petugas')

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
                    <h2 class="detail-title">{{ $petugas->nama_petugas }}</h2>
                    <p class="detail-subtitle">Informasi lengkap petugas</p>
                </div>
            </div>
            <div class="header-actions">
                <a href="{{ route('master.petugas.index', ['edit_id' => $petugas->id_petugas]) }}"
                    class="btn-action-header btn-edit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
                    </svg>
                    Edit
                </a>
                <button type="button" class="btn-action-header btn-delete" data-bs-toggle="modal"
                    data-bs-target="#deleteDataModal" onclick="deleteData({{ $petugas->id_petugas }})">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                    Hapus
                </button>
            </div>
        </div>

        {{-- Detail Cards --}}
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
                                @if($petugas->kategori)
                                    <span class="badge badge-{{ $petugas->kategori == 'Mitra' ? 'blue' : 'purple' }}">
                                        {{ $petugas->kategori }}
                                    </span>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">NIK</label>
                            <div class="info-value">{{ $petugas->nik ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Posisi</label>
                            <div class="info-value">{{ $petugas->posisi ?? '-' }}</div>
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
                                @if($petugas->no_hp)
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
                                @if($petugas->email)
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
                            <div class="info-value">{{ $petugas->alamat ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Kecamatan</label>
                            <div class="info-value">{{ $petugas->kecamatan ?? '-' }}</div>
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
                            <div class="info-value">
                                {{ $petugas->tgl_lahir ? $petugas->tgl_lahir->isoFormat('D MMMM YYYY') : '-' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Pendidikan Terakhir</label>
                            <div class="info-value">{{ $petugas->pendidikan ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">Pekerjaan</label>
                            <div class="info-value">{{ $petugas->pekerjaan ?? '-' }}</div>
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
                            <div class="info-value text-monospace">{{ $petugas->id_petugas }}</div>
                        </div>
                        @if($petugas->created_at)
                            <div class="info-item">
                                <label class="info-label">Tanggal Dibuat</label>
                                <div class="info-value">{{ $petugas->created_at->isoFormat('D MMMM YYYY, HH:mm') }}</div>
                            </div>
                        @endif
                        @if($petugas->updated_at)
                            <div class="info-item">
                                <label class="info-label">Terakhir Diperbarui</label>
                                <div class="info-value">{{ $petugas->updated_at->isoFormat('D MMMM YYYY, HH:mm') }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
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
                        <button type="button" class="btn-danger" id="confirmDeleteButton">Ya, Hapus</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function deleteData(id) {
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = `/master-petugas/${id}`;
            document.getElementById('deleteModalBody').innerText = 'Apakah Anda yakin ingin menghapus data petugas ini? Tindakan ini tidak dapat dibatalkan.';
            document.getElementById('confirmDeleteButton').onclick = function () {
                deleteForm.submit();
            }
        }
    </script>
@endpush

@push('styles')
    <style>
        /* Detail Header */
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
            flex: 1;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: #f3f4f6;
            color: #6b7280;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: #e5e7eb;
            color: #374151;
            transform: translateX(-4px);
        }

        .header-info {
            flex: 1;
        }

        .detail-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }

        .detail-subtitle {
            font-size: 0.9375rem;
            color: #6b7280;
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn-action-header {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-action-header:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .btn-edit {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
        }

        .btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
        }

        /* Detail Card */
        .detail-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            height: 100%;
        }

        .detail-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .card-header-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .card-header-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .detail-card-body {
            padding: 24px;
        }

        /* Info Items */
        .info-item {
            padding: 16px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .info-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .info-item:first-child {
            padding-top: 0;
        }

        .info-label {
            font-size: 0.8125rem;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: block;
        }

        .info-value {
            font-size: 0.9375rem;
            color: #1f2937;
            font-weight: 500;
        }

        .text-monospace {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 4px 8px;
            border-radius: 6px;
            display: inline-block;
            font-size: 0.875rem;
        }

        /* Contact Link */
        .contact-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .contact-link:hover {
            color: #5568d3;
            text-decoration: underline;
        }

        /* Badge */
        .badge {
            display: inline-flex;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8125rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-blue {
            background: rgba(59, 130, 246, 0.1);
            color: #2563eb;
        }

        .badge-purple {
            background: rgba(139, 92, 246, 0.1);
            color: #7c3aed;
        }

        /* Modal Styles */
        .modern-modal .modal-header {
            padding: 24px;
            border-bottom: 1px solid #f3f4f6;
        }

        .modern-modal .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }

        .modal-close {
            background: #f3f4f6;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #6b7280;
        }

        .modal-close:hover {
            background: #e5e7eb;
            color: #374151;
        }

        .modal-header-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
        }

        .modal-header-danger .modal-title {
            color: #ffffff;
        }

        .modal-close-white {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }

        .modal-close-white:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .modern-modal .modal-body {
            padding: 24px;
        }

        .modern-modal .modal-footer {
            padding: 20px 24px;
            border-top: 1px solid #f3f4f6;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        .delete-icon {
            text-align: center;
            color: #ef4444;
            margin-bottom: 16px;
        }

        .delete-text {
            text-align: center;
            font-size: 0.9375rem;
            color: #6b7280;
            margin: 0;
        }

        /* Button Styles */
        .btn-secondary,
        .btn-danger {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #6b7280;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
            color: #4b5563;
        }

        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .detail-header {
                flex-direction: column;
                align-items: stretch;
            }

            .header-left {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-back {
                width: fit-content;
            }

            .header-actions {
                width: 100%;
            }

            .btn-action-header {
                flex: 1;
                justify-content: center;
            }

            .detail-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .header-actions {
                flex-direction: column;
            }

            .btn-action-header {
                width: 100%;
            }
        }
    </style>
@endpush