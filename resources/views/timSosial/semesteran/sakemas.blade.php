@extends('layouts.app')

@section('title', 'Sakernas')
@section('header-title', 'List Target Sakernas (Kegiatan Semesteran)')

@section('content')
    <div class="container-fluid px-0">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">LIST TARGET SAKERNAS (Semesteran)</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">Tambah baru</button>
        </div>

        @if (session('ok'))
            <div class="alert alert-success">{{ session('ok') }}</div>
        @endif

        {{-- Tabs kategori --}}
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
                                <th style="width:56px">#</th>
                                <th>Nama Kegiatan</th>
                                <th>Blok Sensus/Responden</th>
                                <th>Pencacah</th>
                                <th>Pengawas</th>
                                <th>Tanggal Target Penyelesaian</th>
                                <th>Flag Progress</th>
                                <th>Tanggal Pengumpulan</th>
                                <th style="width:220px">Aksi</th>
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
                                            {{ $it->flag_progress ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">{{ $it->tanggal_pengumpulan_formatted }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('sosial.semesteran.show', $it) }}"
                                            class="btn btn-sm btn-outline-secondary">Lihat</a>

                                        {{-- Edit: panggil JS agar action form ditetapkan benar --}}
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="openEditSakernas(this)" data-id="{{ $it->id_sosial_semesteran }}"
                                            data-nama="{{ $it->nama_kegiatan }}" data-bsres="{{ $it->BS_Responden }}"
                                            data-pencacah="{{ $it->pencacah }}" data-pengawas="{{ $it->pengawas }}"
                                            data-target="{{ $it->target_penyelesaian ? \Carbon\Carbon::createFromFormat('d/m/Y', $it->target_penyelesaian)->format('Y-m-d') : '' }}"
                                            data-flag="{{ $it->flag_progress }}"
                                            data-kumpul="{{ $it->tanggal_pengumpulan ? \Carbon\Carbon::parse($it->tanggal_pengumpulan)->format('Y-m-d') : '' }}">
                                            Edit
                                        </button>

                                        <form action="{{ route('sosial.semesteran.destroy', $it) }}" method="post"
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

    {{-- ================== MODAL CREATE ================== --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" action="{{ route('sosial.semesteran.store') }}">
                    @csrf
                    <input type="hidden" name="_mode" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title">Sakernas — Tambah Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p class="text-muted mb-3">Contoh nama: <code>Sakernas Februari</code>, <code>Sakernas
                                Agustus</code></p>

                        @if ($errors->any() && old('_mode') === 'create')
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
                            <input type="text" name="nama_kegiatan"
                                value="{{ old('nama_kegiatan', 'Sakernas Februari') }}" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Blok Sensus/Responden</label>
                            <input type="text" name="BS_Responden" value="{{ old('BS_Responden') }}"
                                class="form-control">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Pencacah <span class="text-danger">*</span></label>
                                <input type="text" name="pencacah" value="{{ old('pencacah') }}" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pengawas <span class="text-danger">*</span></label>
                                <input type="text" name="pengawas" value="{{ old('pengawas') }}"
                                    class="form-control" required>
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Target Penyelesaian</label>
                                <input type="date" name="target_penyelesaian"
                                    value="{{ old('target_penyelesaian') }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Flag Progress <span class="text-danger">*</span></label>
                                @php $oldFlag = old('flag_progress','Belum Mulai'); @endphp
                                <select name="flag_progress" class="form-select" required>
                                    @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                        <option value="{{ $opt }}" @selected($oldFlag === $opt)>
                                            {{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Tanggal Pengumpulan</label>
                            <input type="date name="tanggal_pengumpulan" value="{{ old('tanggal_pengumpulan') }}"
                                class="form-control">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">Simpan</button>
                        <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================== MODAL EDIT ================== --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formEdit" method="post" action="#">
                    @csrf @method('PUT')
                    <input type="hidden" name="_mode" value="edit">

                    <div class="modal-header">
                        <h5 class="modal-title">Sakernas — Edit Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        @if ($errors->any() && old('_mode') === 'edit')
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
                            <input type="text" id="e_nama" name="nama_kegiatan" class="form-control" required
                                value="{{ old('_mode') === 'edit' ? old('nama_kegiatan') : '' }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Blok Sensus/Responden</label>
                            <input type="text" id="e_bsres" name="BS_Responden" class="form-control"
                                value="{{ old('_mode') === 'edit' ? old('BS_Responden') : '' }}">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Pencacah <span class="text-danger">*</span></label>
                                <input type="text" id="e_pencacah" name="pencacah" class="form-control" required
                                    value="{{ old('_mode') === 'edit' ? old('pencacah') : '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pengawas <span class="text-danger">*</span></label>
                                <input type="text" id="e_pengawas" name="pengawas" class="form-control" required
                                    value="{{ old('_mode') === 'edit' ? old('pengawas') : '' }}">
                            </div>
                        </div>

                        <div class="row g-3 mt-0">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Target Penyelesaian</label>
                                <input type="date" id="e_target" name="target_penyelesaian" class="form-control"
                                    value="{{ old('_mode') === 'edit' ? old('target_penyelesaian') : '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Flag Progress <span class="text-danger">*</span></label>
                                @php $oldFlagE = old('_mode')==='edit' ? old('flag_progress') : null; @endphp
                                <select id="e_flag" name="flag_progress" class="form-select" required>
                                    @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                        <option value="{{ $opt }}" {{ $oldFlagE === $opt ? 'selected' : '' }}>
                                            {{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">Tanggal Pengumpulan</label>
                            <input type="date" id="e_kumpul" name="tanggal_pengumpulan" class="form-control"
                                value="{{ old('_mode') === 'edit' ? old('tanggal_pengumpulan') : '' }}">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script khusus halaman (inline supaya pasti jalan walau layout tak punya @stack) --}}
    <script>
        function openEditSakernas(btn) {
            const id = btn.getAttribute('data-id');
            const nama = btn.getAttribute('data-nama') || '';
            const bsres = btn.getAttribute('data-bsres') || '';
            const penc = btn.getAttribute('data-pencacah') || '';
            const peng = btn.getAttribute('data-pengawas') || '';
            const target = btn.getAttribute('data-target') || '';
            const flag = btn.getAttribute('data-flag') || 'Belum Mulai';
            const kumpul = btn.getAttribute('data-kumpul') || '';

            const form = document.getElementById('formEdit');
            const urlTemplate = "{{ route('sosial.semesteran.update', ['semesteran' => '__ID__']) }}";
            form.action = urlTemplate.replace('__ID__', id);

            document.getElementById('e_nama').value = nama;
            document.getElementById('e_bsres').value = bsres;
            document.getElementById('e_pencacah').value = penc;
            document.getElementById('e_pengawas').value = peng;
            document.getElementById('e_target').value = target;
            document.getElementById('e_flag').value = flag;
            document.getElementById('e_kumpul').value = kumpul;

            const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
            modal.show();
        }
    </script>
@endsection
