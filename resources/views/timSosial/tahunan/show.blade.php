@extends('layouts.app')
@section('title', 'Detail Sosial Tahunan')
@section('header-title', 'Sosial Tahunan - Detail')

@section('content')
    <div class="container-fluid px-0">
        <div class="card">
            <div class="card-header"><strong>Detail Kegiatan</strong></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Nama Kegiatan</dt>
                    <dd class="col-sm-9">{{ $tahunan->nama_kegiatan }}</dd>

                    <dt class="col-sm-3">Blok Sensus/Responden</dt>
                    <dd class="col-sm-9">{{ $tahunan->BS_Responden }}</dd>

                    <dt class="col-sm-3">Pencacah</dt>
                    <dd class="col-sm-9">{{ $tahunan->pencacah }}</dd>

                    <dt class="col-sm-3">Pengawas</dt>
                    <dd class="col-sm-9">{{ $tahunan->pengawas }}</dd>

                    <dt class="col-sm-3">Target Penyelesaian</dt>
                    <dd class="col-sm-9">{{ $tahunan->target_penyelesaian_formatted }}</dd>

                    <dt class="col-sm-3">Flag Progress</dt>
                    <dd class="col-sm-9">
                        <span
                            class="badge
            {{ $tahunan->flag_progress === 'Selesai'
                ? 'bg-success'
                : ($tahunan->flag_progress === 'Proses'
                    ? 'bg-warning text-dark'
                    : 'bg-secondary') }}">
                            {{ $tahunan->flag_progress }}
                        </span>
                    </dd>

                    <dt class="col-sm-3">Tanggal Pengumpulan</dt>
                    <dd class="col-sm-9">{{ $tahunan->tanggal_pengumpulan_formatted }}</dd>
                </dl>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ route('sosial.tahunan.edit', $tahunan) }}" class="btn btn-primary">Edit</a>
                <form action="{{ route('sosial.tahunan.destroy', $tahunan) }}" method="post"
                    onsubmit="return confirm('Hapus data ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger">Hapus</button>
                </form>
                <a href="{{ route('sosial.tahunan.index') }}" class="btn btn-light ms-auto">Kembali</a>
            </div>
        </div>
    </div>
@endsection
