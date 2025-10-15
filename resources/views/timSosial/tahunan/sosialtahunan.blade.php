{{-- resources/views/timSosial/tahunan/sosialtahunan.blade.php --}}
@extends('layouts.app')

@section('title', 'Sosial Tahunan')

{{-- Judul yang akan tampil di header halaman --}}
@section('header-title', 'Sosial Tahunan')

@section('content')
    <div class="container-fluid px-0">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">LIST TARGET KEGIATAN TAHUNAN TIM SOSIAL</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate"
                onclick="prefillKategori('{{ $kategori }}')">
                Tambah baru
            </button>
        </div>

        @if (session('ok'))
            <div class="alert alert-success">{{ session('ok') }}</div>
        @endif

        {{-- Tabs kategori --}}
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ !$kategori ? 'active' : '' }}" href="{{ route('sosial.tahunan.index') }}">Semua</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kategori === 'Polkam' ? 'active' : '' }}"
                    href="{{ route('sosial.tahunan.index', ['kategori' => 'Polkam']) }}">
                    Polkam ({{ $countPolkam }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kategori === 'PODES' ? 'active' : '' }}"
                    href="{{ route('sosial.tahunan.index', ['kategori' => 'PODES']) }}">
                    Podes ({{ $countPodes }})
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
                  {{ $it->flag_progress === 'Selesai'
                      ? 'bg-success'
                      : ($it->flag_progress === 'Proses'
                          ? 'bg-warning text-dark'
                          : 'bg-secondary') }}">
                                            {{ $it->flag_progress }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">{{ $it->tanggal_pengumpulan_formatted }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('sosial.tahunan.show', $it) }}"
                                            class="btn btn-sm btn-outline-secondary">Lihat</a>
                                        <a href="{{ route('sosial.tahunan.edit', $it) }}"
                                            class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('sosial.tahunan.destroy', $it) }}" method="post"
                                            class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
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

    {{-- ================== MODAL TAMBAH ================== --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" action="{{ route('sosial.tahunan.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Sosial Tahunan, Tambah baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted">Nama Kegiatan pada Sosial Tahunan antara lain: Podes, SPAK, Polkam</p>

                        {{-- error list (jika validasi gagal) --}}
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $e)
                                        <li>{{ $e }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kegiatan" class="form-control"
                                value="{{ old('nama_kegiatan') }}" placeholder="cth: PODES, SPAK, Polkam" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Blok Sensus/Responden</label>
                            <input type="text" name="BS_Responden" class="form-control"
                                value="{{ old('BS_Responden') }}">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Pencacah <span class="text-danger">*</span></label>
                                <input type="text" name="pencacah" class="form-control" value="{{ old('pencacah') }}"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pengawas <span class="text-danger">*</span></label>
                                <input type="text" name="pengawas" class="form-control" value="{{ old('pengawas') }}"
                                    required>
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Target Penyelesaian <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="target_penyelesaian" class="form-control"
                                    value="{{ old('target_penyelesaian') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Flag Progress <span class="text-danger">*</span></label>
                                <select name="flag_progress" class="form-select" required>
                                    <option value="" disabled {{ old('flag_progress') ? '' : 'selected' }}>Silahkan
                                        pilih</option>
                                    @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                        <option value="{{ $opt }}" @selected(old('flag_progress') === $opt)>
                                            {{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Tanggal Pengumpulan</label>
                            <input type="date" name="tanggal_pengumpulan" class="form-control"
                                value="{{ old('tanggal_pengumpulan') }}">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="reset" class="btn btn-outline-secondary">Tata ulang</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Prefill nama_kegiatan saat tombol "Tambah baru" diklik (misal tab aktif Polkam/PODES)
        function prefillKategori(kat) {
            if (!kat) return;
            const inp = document.querySelector('#modalCreate input[name=nama_kegiatan]');
            if (inp && !inp.value) inp.value = kat;
        }

        // Auto-open modal ketika validasi gagal
        @if ($errors->any() || $openModal)
            const m = new bootstrap.Modal(document.getElementById('modalCreate'));
            m.show();
        @endif
    </script>
@endpush
