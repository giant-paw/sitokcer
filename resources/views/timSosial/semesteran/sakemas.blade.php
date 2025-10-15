@extends('layouts.app')

@section('title', 'Sakernas')
@section('header-title', 'List Target Sakernas (Kegiatan Semesteran)')

@section('content')
    <div class="p-4 md:p-6">

        {{-- Tabs Sakernas --}}
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ !$kategori ? 'active' : '' }}" href="{{ route('sosial.semesteran.index') }}">Semua</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kategori === 'Sakernas' ? 'active' : '' }}"
                    href="{{ route('sosial.semesteran.index', ['kategori' => 'Sakernas']) }}">
                    Sakernas
                </a>
            </li>
        </ul>

        {{-- Search --}}
        <form method="get" class="mb-3">
            <input type="hidden" name="kategori" value="{{ $kategori }}">
            <div class="input-group" style="max-width: 360px">
                <input type="text" class="form-control" name="q" value="{{ $q }}" placeholder="search">
                <button class="btn btn-outline-secondary">Cari</button>
            </div>
        </form>

        {{-- Table --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover table-bordered mb-0 align-middle">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th style="width: 56px">#</th>
                                <th>Nama Kegiatan</th>
                                <th>Blok Sensus/Responden</th>
                                <th>Pencacah</th>
                                <th>Pengawas</th>
                                <th>Tanggal Target Penyelesaian</th>
                                <th>Flag Progress</th>
                                <th>Tanggal Pengumpulan</th>
                                <th style="width: 180px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $i => $it)
                                <tr>
                                    <td class="text-center">{{ $items->firstItem() + $i }}</td>
                                    <td>{{ $it->nama_kegiatan }}</td>
                                    <td>{{ $it->BS_Responden }}</td>
                                    <td>{{ $it->pencacah }}</td>
                                    <td>{{ $it->pengawas }}</td>
                                    <td class="text-nowrap">{{ $it->target_penyelesaian_formatted }}</td>
                                    <td class="text-center">
                                        <span
                                            class="badge
                                    {{ $it->flag_progress === 'Selesai' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $it->flag_progress }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">{{ $it->tanggal_pengumpulan_formatted }}</td>
                                    <td class="text-nowrap">
                                        {{-- <a href="{{ route('sosial.semesteran.show', $it) }}"
                                            class="btn btn-sm btn-outline-secondary">Lihat</a>
                                        <a href="{{ route('sosial.semesteran.edit', $it) }}"
                                            class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('sosial.semesteran.destroy', $it) }}" method="post"
                                            class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button> --}}
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $items->links() }}
            </div>
        </div>
    </div>
@endsection
