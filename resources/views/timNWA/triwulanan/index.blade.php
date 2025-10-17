@extends('layouts.app')

@section('title', $prefix . ' (NWA Triwulanan)')
@section('header-title', 'List Target ' . $prefix . ' (NWA Triwulanan)')

@section('content')
    <div class="container-fluid px-0">

        {{-- Tabs TW1..TW4 --}}
        @php $tabs = ['TW1','TW2','TW3','TW4']; @endphp
        <ul class="nav nav-tabs mb-3">
            @foreach ($tabs as $t)
                <li class="nav-item">
                    <a class="nav-link {{ $tw === $t ? 'active' : '' }}"
                        href="{{ route('nwa.triwulanan.index', [$jenis, 'tw' => $t, 'q' => request('q')]) }}">
                        {{ $prefix }}-{{ $t }}
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Toolbar + Search --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-primary" onclick="openCreate()">
                Tambah baru
            </button>
            <form method="get" action="{{ route('nwa.triwulanan.index', $jenis) }}">
                <input type="hidden" name="tw" value="{{ $tw }}">
                <div class="input-group" style="max-width: 360px;">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Search" class="form-control">
                    <button class="btn btn-outline-secondary">Cari</button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover table-bordered mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">#</th>
                                <th>Nama Kegiatan</th>
                                <th>Blok Sensus/Responden</th>
                                <th>Pencacah</th>
                                <th>Pengawas</th>
                                <th>Target Penyelesaian</th>
                                <th class="text-center">Flag Progress</th>
                                <th>Tanggal Pengumpulan</th>
                                <th class="text-center" style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $i => $row)
                                <tr>
                                    <td class="text-center">{{ $rows->firstItem() + $i }}</td>
                                    <td>{{ $row->nama_kegiatan }}</td>
                                    <td>{{ $row->BS_Responden }}</td>
                                    <td>{{ $row->pencacah }}</td>
                                    <td>{{ $row->pengawas }}</td>
                                    <td class="text-nowrap">
                                        {{ $row->target_penyelesaian ? \Carbon\Carbon::parse($row->target_penyelesaian)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $flag = $row->flag_progress;
                                            $badgeClass = 'bg-secondary';
                                            if ($flag === 'Selesai') $badgeClass = 'bg-success';
                                            elseif ($flag === 'Proses') $badgeClass = 'bg-warning text-dark';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $flag ?? '-' }}</span>
                                    </td>
                                    <td class="text-nowrap">
                                        {{ $row->tanggal_pengumpulan ? \Carbon\Carbon::parse($row->tanggal_pengumpulan)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-nowrap text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="openEdit(this)" data-id="{{ $row->id_nwa_triwulanan }}"
                                            data-nama="{{ $row->nama_kegiatan }}" data-bs="{{ $row->BS_Responden }}"
                                            data-pencacah="{{ $row->pencacah }}" data-pengawas="{{ $row->pengawas }}"
                                            data-target="{{ $row->target_penyelesaian ? \Carbon\Carbon::parse($row->target_penyelesaian)->format('Y-m-d') : '' }}"
                                            data-flag="{{ $row->flag_progress }}"
                                            data-kumpul="{{ $row->tanggal_pengumpulan ? \Carbon\Carbon::parse($row->tanggal_pengumpulan)->format('Y-m-d') : '' }}">
                                            Edit
                                        </button>
                                        <form action="{{ route('nwa.triwulanan.destroy', [$jenis, $row]) }}" method="post"
                                            class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($rows->hasPages())
                <div class="card-footer">
                    {{ $rows->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ============== MODAL CREATE ============== --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" action="{{ route('nwa.triwulanan.store', $jenis) }}">
                    @csrf
                    <input type="hidden" name="_mode" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Data {{ $prefix }} ({{ $tw }})</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
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
                                value="{{ old('nama_kegiatan', $prefix . '-' . $tw) }}" class="form-control" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Blok Sensus/Responden</label>
                                <input type="text" name="BS_Responden" value="{{ old('BS_Responden') }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pencacah <span class="text-danger">*</span></label>
                                <input type="text" name="pencacah" value="{{ old('pencacah') }}"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pengawas <span class="text-danger">*</span></label>
                                <input type="text" name="pengawas" value="{{ old('pengawas') }}"
                                    class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Target Penyelesaian</label>
                                <input type="date" name="target_penyelesaian"
                                    value="{{ old('target_penyelesaian') }}" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Flag Progress <span class="text-danger">*</span></label>
                                @php $oldFlag = old('flag_progress', 'Belum Mulai'); @endphp
                                <select name="flag_progress" class="form-select" required>
                                    @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                        <option value="{{ $opt }}" @selected($oldFlag === $opt)>
                                            {{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Pengumpulan</label>
                                <input type="date" name="tanggal_pengumpulan"
                                    value="{{ old('tanggal_pengumpulan') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="reset" class="btn btn-outline-secondary">Reset</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ============== MODAL EDIT ============== --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formEdit" method="post" action="#">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_mode" value="edit">
                    <input type="hidden" name="_id" id="edit_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Data {{ $prefix }}</h5>
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
                            <input type="text" id="edit_nama" name="nama_kegiatan"
                                value="{{ old('_mode') === 'edit' ? old('nama_kegiatan') : '' }}"
                                class="form-control" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Blok Sensus/Responden</label>
                                <input type="text" id="edit_bs" name="BS_Responden"
                                    value="{{ old('_mode') === 'edit' ? old('BS_Responden') : '' }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pencacah <span class="text-danger">*</span></label>
                                <input type="text" id="edit_pencacah" name="pencacah"
                                    value="{{ old('_mode') === 'edit' ? old('pencacah') : '' }}" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pengawas <span class="text-danger">*</span></label>
                                <input type="text" id="edit_pengawas" name="pengawas"
                                    value="{{ old('_mode') === 'edit' ? old('pengawas') : '' }}" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Target Penyelesaian</label>
                                <input type="date" id="edit_target" name="target_penyelesaian"
                                    value="{{ old('_mode') === 'edit' ? old('target_penyelesaian') : '' }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Flag Progress <span class="text-danger">*</span></label>
                                @php $oldFlagE = old('_mode') === 'edit' ? old('flag_progress') : null; @endphp
                                <select id="edit_flag" name="flag_progress" class="form-select" required>
                                    @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                        <option value="{{ $opt }}" @selected($oldFlagE === $opt)>
                                            {{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Pengumpulan</label>
                                <input type="date" id="edit_kumpul" name="tanggal_pengumpulan"
                                    value="{{ old('_mode') === 'edit' ? old('tanggal_pengumpulan') : '' }}"
                                    class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let modalCreate, modalEdit;
        const updateUrlTemplate = "{{ route('nwa.triwulanan.update', [$jenis, '__ID__']) }}";

        document.addEventListener('DOMContentLoaded', function() {
            modalCreate = new bootstrap.Modal(document.getElementById('modalCreate'));
            modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));

            // Auto-open modal on validation failure
            @if ($errors->any())
                @if (old('_mode') === 'create')
                    modalCreate.show();
                @elseif (old('_mode') === 'edit')
                    (function() {
                        const id = "{{ old('_id') }}";
                        const form = document.getElementById('formEdit');
                        if (id) {
                            form.action = updateUrlTemplate.replace('__ID__', id);
                        }
                        modalEdit.show();
                    })();
                @endif
            @endif
        });

        function openCreate() {
            modalCreate.show();
        }

        function openEdit(btn) {
            const id = btn.dataset.id;
            const form = document.getElementById('formEdit');
            form.action = updateUrlTemplate.replace('__ID__', id);

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = btn.dataset.nama || '';
            document.getElementById('edit_bs').value = btn.dataset.bs || '';
            document.getElementById('edit_pencacah').value = btn.dataset.pencacah || '';
            document.getElementById('edit_pengawas').value = btn.dataset.pengawas || '';
            document.getElementById('edit_target').value = btn.dataset.target || '';
            document.getElementById('edit_flag').value = btn.dataset.flag || 'Belum Mulai';
            document.getElementById('edit_kumpul').value = btn.dataset.kumpul || '';

            modalEdit.show();
        }
    </script>
@endpush