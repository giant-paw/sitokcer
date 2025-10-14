{{-- resources/views/timSosial/tahunan/create_edit.blade.php --}}
@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <h4 class="mb-3">{{ $item->exists ? 'Edit' : 'Tambah' }} Target Kegiatan Tahunan</h4>

        {{-- Error Validation Display --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> Terdapat kesalahan input:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="post"
                    action="{{ $item->exists ? route('sosial.tahunan.update', $item) : route('sosial.tahunan.store') }}">
                    @csrf
                    @if ($item->exists)
                        @method('PUT')
                    @endif

                    <div class="row g-3">
                        {{-- Nama Kegiatan --}}
                        <div class="col-md-4">
                            <label class="form-label">
                                Nama Kegiatan <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="nama_kegiatan"
                                class="form-control @error('nama_kegiatan') is-invalid @enderror"
                                value="{{ old('nama_kegiatan', $item->nama_kegiatan) }}" 
                                placeholder="Contoh: PODES, SPAK, Polkam"
                                required>
                            @error('nama_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Blok Sensus/Responden --}}
                        <div class="col-md-4">
                            <label class="form-label">Blok Sensus/Responden</label>
                            <input type="text" name="blok_sensus"
                                class="form-control @error('blok_sensus') is-invalid @enderror"
                                value="{{ old('blok_sensus', $item->blok_sensus) }}"
                                placeholder="Masukkan blok sensus">
                            @error('blok_sensus')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Pencacah --}}
                        <div class="col-md-4">
                            <label class="form-label">
                                Pencacah <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="pencacah"
                                class="form-control @error('pencacah') is-invalid @enderror"
                                value="{{ old('pencacah', $item->pencacah) }}" 
                                placeholder="Nama pencacah"
                                required>
                            @error('pencacah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Pengawas --}}
                        <div class="col-md-4">
                            <label class="form-label">
                                Pengawas <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="pengawas"
                                class="form-control @error('pengawas') is-invalid @enderror"
                                value="{{ old('pengawas', $item->pengawas) }}" 
                                placeholder="Nama pengawas"
                                required>
                            @error('pengawas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tanggal Target --}}
                        <div class="col-md-4">
                            <label class="form-label">
                                Tanggal Target Penyelesaian <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tgl_target"
                                class="form-control @error('tgl_target') is-invalid @enderror"
                                value="{{ old('tgl_target', optional($item->tgl_target)->toDateString()) }}"
                                required>
                            @error('tgl_target')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Flag Progress --}}
                        <div class="col-md-4">
                            <label class="form-label">
                                Flag Progress <span class="text-danger">*</span>
                            </label>
                            <select name="flag_progress" 
                                class="form-select @error('flag_progress') is-invalid @enderror" 
                                required>
                                <option value="" disabled {{ old('flag_progress', $item->flag_progress) ? '' : 'selected' }}>
                                    -- Pilih Status --
                                </option>
                                @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                    <option value="{{ $opt }}" 
                                        @selected(old('flag_progress', $item->flag_progress) === $opt)>
                                        {{ $opt }}
                                    </option>
                                @endforeach
                            </select>
                            @error('flag_progress')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tanggal Pengumpulan --}}
                        <div class="col-md-4">
                            <label class="form-label">Tanggal Pengumpulan</label>
                            <input type="date" name="tgl_pengumpulan"
                                class="form-control @error('tgl_pengumpulan') is-invalid @enderror"
                                value="{{ old('tgl_pengumpulan', optional($item->tgl_pengumpulan)->toDateString()) }}">
                            @error('tgl_pengumpulan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Opsional, isi jika sudah selesai</small>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> {{ $item->exists ? 'Simpan Perubahan' : 'Simpan Data' }}
                        </button>
                        <a href="{{ route('sosial.tahunan.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        @if (!$item->exists)
                            <button type="reset" class="btn btn-outline-warning">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset Form
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Info tambahan --}}
        <div class="alert alert-info mt-3">
            <strong><i class="bi bi-info-circle"></i> Catatan:</strong>
            <ul class="mb-0 mt-2">
                <li>Field dengan tanda <span class="text-danger">*</span> wajib diisi</li>
                <li>Nama Kegiatan: contoh PODES, SPAK, Polkam, dll</li>
                <li>Tanggal Pengumpulan bersifat opsional, isi jika kegiatan sudah selesai</li>
            </ul>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .is-invalid {
            border-color: #dc3545;
        }
        
        .invalid-feedback {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: #dc3545;
        }
        
        .form-label .text-danger {
            font-weight: bold;
        }
    </style>
@endpush