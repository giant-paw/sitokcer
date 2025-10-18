@extends('layouts.app')

@section('title', 'Detail Sakernas')
@section('header-title', 'Detail Sakernas')

@section('content')
    <div class="container py-3">
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary mb-3">← Kembali</a>

        <div class="card">
            <div class="card-header">Info Kegiatan</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Nama Kegiatan</dt>
                    <dd class="col-sm-9">{{ $semesteran->nama_kegiatan }}</dd>

                    <dt class="col-sm-3">Blok Sensus/Responden</dt>
                    <dd class="col-sm-9">{{ $semesteran->BS_Responden ?: '-' }}</dd>

                    <dt class="col-sm-3">Pencacah</dt>
                    <dd class="col-sm-9">{{ $semesteran->pencacah }}</dd>

                    <dt class="col-sm-3">Pengawas</dt>
                    <dd class="col-sm-9">{{ $semesteran->pengawas }}</dd>

                    <dt class="col-sm-3">Target Penyelesaian</dt>
                    <dd class="col-sm-9">{{ $semesteran->target_penyelesaian_formatted }}</dd>

                    <dt class="col-sm-3">Flag Progress</dt>
                    <dd class="col-sm-9">{{ $semesteran->flag_progress ?? '-' }}</dd>

                    <dt class="col-sm-3">Tanggal Pengumpulan</dt>
                    <dd class="col-sm-9">{{ $semesteran->tanggal_pengumpulan_formatted }}</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection
