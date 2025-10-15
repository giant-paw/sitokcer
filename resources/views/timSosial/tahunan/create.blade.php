@extends('layouts.app')
@section('title', isset($mode) && $mode === 'edit' ? 'Edit Sosial Tahunan' : 'Tambah Sosial Tahunan')
@section('header-title', 'Sosial Tahunan')

@section('content')
    <div class="container-fluid px-0">
        <div class="card">
            <div class="card-header">
                <strong>{{ isset($mode) && $mode === 'edit' ? 'Edit Data' : 'Tambah Data' }}</strong>
            </div>
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="post"
                    action="{{ isset($mode) && $mode === 'edit' ? route('sosial.tahunan.update', $tahunan) : route('sosial.tahunan.store') }}">
                    @csrf
                    @if (isset($mode) && $mode === 'edit')
                        @method('PUT')
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kegiatan" class="form-control"
                            value="{{ old('nama_kegiatan', $tahunan->nama_kegiatan ?? ($prefill ?? '')) }}"
                            placeholder="cth: PODES, SPAK, Polkam" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Blok Sensus/Responden</label>
                        <input type="text" name="BS_Responden" class="form-control"
                            value="{{ old('BS_Responden', $tahunan->BS_Responden ?? '') }}">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Pencacah <span class="text-danger">*</span></label>
                            <input type="text" name="pencacah" class="form-control"
                                value="{{ old('pencacah', $tahunan->pencacah ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Pengawas <span class="text-danger">*</span></label>
                            <input type="text" name="pengawas" class="form-control"
                                value="{{ old('pengawas', $tahunan->pengawas ?? '') }}" required>
                        </div>
                    </div>

                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Target Penyelesaian <span class="text-danger">*</span></label>
                            <input type="date" name="target_penyelesaian" class="form-control"
                                value="{{ old('target_penyelesaian', $targetYmd ?? '') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Flag Progress <span class="text-danger">*</span></label>
                            <select name="flag_progress" class="form-select" required>
                                @php $sel = old('flag_progress', $tahunan->flag_progress ?? ''); @endphp
                                @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                    <option value="{{ $opt }}" {{ $sel === $opt ? 'selected' : '' }}>
                                        {{ $opt }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Tanggal Pengumpulan</label>
                        <input type="date" name="tanggal_pengumpulan" class="form-control"
                            value="{{ old('tanggal_pengumpulan', $kumpulYmd ?? '') }}">
                    </div>

                    <div class="mt-4">
                        <button
                            class="btn btn-primary">{{ isset($mode) && $mode === 'edit' ? 'Simpan Perubahan' : 'Simpan' }}</button>
                        <a href="{{ route('sosial.tahunan.index') }}" class="btn btn-light">Batal</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
