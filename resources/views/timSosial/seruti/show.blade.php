@extends('layouts.app')

@section('title', 'Detail Seruti')
@section('header-title', 'Seruti - Detail')

@section('content')
    <div class="container-fluid px-0">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Detail Data</h5>
                
                {{-- Data details --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="text-muted">Nama Kegiatan</div>
                        <div>{{ $seruti->nama_kegiatan }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted">Blok Sensus/Responden</div>
                        <div>{{ $seruti->BS_Responden ?: '-' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted">Pencacah</div>
                        <div>{{ $seruti->pencacah }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted">Pengawas</div>
                        <div>{{ $seruti->pengawas }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted">Target Penyelesaian</div>
                        <div>
                            {{ $seruti->target_penyelesaian ? \Carbon\Carbon::parse($seruti->target_penyelesaian)->format('d/m/Y') : '-' }}
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="text-muted">Flag Progress</div>
                        <div>{{ $seruti->flag_progress }}</div>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="text-muted">Tanggal Pengumpulan</div>
                        <div>
                            {{ $seruti->tanggal_pengumpulan ? \Carbon\Carbon::parse($seruti->tanggal_pengumpulan)->format('Y-m-d H:i') : '-' }}
                        </div>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="mt-3 pt-3 border-top d-flex gap-2">
                    <a href="{{ route('sosial.seruti.edit', $seruti) }}" class="btn btn-sm btn-outline-primary">Edit</a>

                    <form action="{{ route('sosial.seruti.destroy', $seruti) }}" method="post"
                        onsubmit="return confirm('Hapus data ini?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                    </form>

                    <a href="{{ route('sosial.seruti.index', ['tw' => preg_match('/Seruti\-(TW[1-4])/', $seruti->nama_kegiatan, $m) ? $m[1] : 'TW1']) }}"
                        class="btn btn-sm btn-outline-secondary ms-auto">
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection