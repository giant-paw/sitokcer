{{-- resources/views/timSosial/tahunan/show.blade.php --}}
@extends('layouts.app')

@section('content')
    <h4 class="mb-3">Detail Target Kegiatan</h4>
    <div class="card">
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Nama Kegiatan</dt>
                <dd class="col-sm-9">{{ $item->nama_kegiatan }}</dd>
                <dt class="col-sm-3">Blok Sensus</dt>
                <dd class="col-sm-9">{{ $item->blok_sensus }}</dd>
                <dt class="col-sm-3">Pencacah</dt>
                <dd class="col-sm-9">{{ $item->pencacah }}</dd>
                <dt class="col-sm-3">Pengawas</dt>
                <dd class="col-sm-9">{{ $item->pengawas }}</dd>
                <dt class="col-sm-3">Target</dt>
                <dd class="col-sm-9">{{ optional($item->tgl_target)->format('d/m/Y') }}</dd>
                <dt class="col-sm-3">Progress</dt>
                <dd class="col-sm-9">{{ $item->flag_progress }}</dd>
                <dt class="col-sm-3">Pengumpulan</dt>
                <dd class="col-sm-9">{{ optional($item->tgl_pengumpulan)->format('d/m/Y') }}</dd>
            </dl>
            <a href="{{ route('sosial.tahunan.edit', $item) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('sosial.tahunan.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
@endsection
