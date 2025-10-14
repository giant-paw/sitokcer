{{-- resources/views/timSosial/tahunan/create_edit.blade.php --}}
@extends('layouts.app')

@section('content')
    <h4 class="mb-3">{{ $item->exists ? 'Edit' : 'Tambah' }} Target Kegiatan Tahunan</h4>

    <x-errors /> {{-- kalau punya komponen error; kalau tidak, tampilkan manual --}}

    <form method="post" action="{{ $item->exists ? route('sosial.tahunan.update', $item) : route('sosial.tahunan.store') }}">
        @csrf
        @if ($item->exists)
            @method('PUT')
        @endif

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Nama Kegiatan</label>
                <input type="text" name="nama_kegiatan" class="form-control"
                    value="{{ old('nama_kegiatan', $item->nama_kegiatan) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Blok Sensus/Responden</label>
                <input type="text" name="blok_sensus" class="form-control"
                    value="{{ old('blok_sensus', $item->blok_sensus) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Pencacah</label>
                <input type="text" name="pencacah" class="form-control" value="{{ old('pencacah', $item->pencacah) }}"
                    required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Pengawas</label>
                <input type="text" name="pengawas" class="form-control" value="{{ old('pengawas', $item->pengawas) }}"
                    required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Target</label>
                <input type="date" name="tgl_target" class="form-control"
                    value="{{ old('tgl_target', optional($item->tgl_target)->toDateString()) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Flag Progress</label>
                <select name="flag_progress" class="form-select" required>
                    @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                        <option value="{{ $opt }}" @selected(old('flag_progress', $item->flag_progress) === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Pengumpulan</label>
                <input type="date" name="tgl_pengumpulan" class="form-control"
                    value="{{ old('tgl_pengumpulan', optional($item->tgl_pengumpulan)->toDateString()) }}">
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('sosial.tahunan.index') }}" class="btn btn-light">Batal</a>
            <button class="btn btn-primary">{{ $item->exists ? 'Simpan Perubahan' : 'Simpan' }}</button>
        </div>
    </form>
@endsection
