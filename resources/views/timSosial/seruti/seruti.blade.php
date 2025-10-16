@extends('layouts.app')

@section('title', 'Seruti')
@section('header-title', 'List Target Seruti (Kegiatan Triwulanan)')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">LIST TARGET SURVEI EKONOMI RUMAH TANGGA TRIWULANAN (SERUTI)</h4>
    </div>
    <div class="container-fluid px-0">

        {{-- Tabs TW1..TW4 --}}
        @php $tabs = ['TW1', 'TW2', 'TW3', 'TW4']; @endphp
        <ul class="nav nav-tabs mb-3">
            @foreach ($tabs as $t)
                <li class="nav-item">
                    <a class="nav-link {{ $tw === $t ? 'active' : '' }}"
                        href="{{ route('sosial.seruti.index', ['tw' => $t, 'q' => request('q')]) }}">
                        Seruti-{{ $t }}
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Toolbar + Search --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                {{-- CSRF for fetch --}}
                <input type="hidden" id="csrf" value="{{ csrf_token() }}">

                <button class="btn btn-primary" onclick="openSerutiCreate()">
                    Tambah baru
                </button>

                {{-- Bulk delete --}}
                <button type="button" id="btnBulkDelete" class="btn btn-danger" onclick="bulkDelete()" disabled>
                    Hapus terpilih
                </button>
            </div>

            <form action="{{ route('sosial.seruti.index') }}" method="GET">
                <input type="hidden" name="tw" value="{{ $tw }}">
                <div class="input-group" style="max-width: 360px">
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
                            <tr class="text-center">
                                <th style="width: 50px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Nama Kegiatan</th>
                                <th>Blok Sensus/Responden</th>
                                <th>Pencacah</th>
                                <th>Pengawas</th>
                                <th>Target Penyelesaian</th>
                                <th>Flag Progress</th>
                                <th>Tanggal Pengumpulan</th>
                                <th style="width: 180px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input row-checkbox"
                                            data-id="{{ $row->id_sosial_triwulanan }}">
                                    </td>
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
                                            if ($flag === 'Selesai')
                                                $badgeClass = 'bg-success';
                                            elseif ($flag === 'Proses')
                                                $badgeClass = 'bg-warning text-dark';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $flag ?? '-' }}</span>
                                    </td>
                                    <td class="text-nowrap">
                                        {{ $row->tanggal_pengumpulan ? \Carbon\Carbon::parse($row->tanggal_pengumpulan)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-nowrap text-center">
                                        <a href="{{ route('sosial.seruti.show', $row) }}"
                                            class="btn btn-sm btn-outline-secondary">Lihat</a>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="openSerutiEdit(this)" data-id="{{ $row->id_sosial_triwulanan }}"
                                            data-nama="{{ $row->nama_kegiatan }}" data-bs="{{ $row->BS_Responden }}"
                                            data-pencacah="{{ $row->pencacah }}" data-pengawas="{{ $row->pengawas }}"
                                            data-target="{{ $row->target_penyelesaian ? \Carbon\Carbon::parse($row->target_penyelesaian)->format('Y-m-d') : '' }}"
                                            data-flag="{{ $row->flag_progress }}"
                                            data-kumpul="{{ $row->tanggal_pengumpulan ? \Carbon\Carbon::parse($row->tanggal_pengumpulan)->format('Y-m-d') : '' }}">
                                            Edit
                                        </button>
                                        <form action="{{ route('sosial.seruti.destroy', $row) }}" method="post" class="d-inline"
                                            onsubmit="return confirm('Hapus data ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        Data tidak ditemukan.
                                    </td>
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


    {{-- ================= MODAL: CREATE ================= --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" action="{{ route('sosial.seruti.store') }}">
                    @csrf
                    <input type="hidden" name="_mode" value="create">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Data Seruti ({{ $tw }})</h5>
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
                            <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', 'Seruti-' . $tw) }}"
                                class="form-control" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Blok Sensus/Responden</label>
                                <input type="text" name="BS_Responden" value="{{ old('BS_Responden') }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pencacah <span class="text-danger">*</span></label>
                                <input type="text" name="pencacah" value="{{ old('pencacah') }}" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pengawas <span class="text-danger">*</span></label>
                                <input type="text" name="pengawas" value="{{ old('pengawas') }}" class="form-control"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Target Penyelesaian</label>
                                <input type="date" name="target_penyelesaian" value="{{ old('target_penyelesaian') }}"
                                    class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Flag Progress <span class="text-danger">*</span></label>
                                @php $oldFlag = old('flag_progress', 'Belum Mulai'); @endphp
                                <select name="flag_progress" class="form-select" required>
                                    @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                        <option value="{{ $opt }}" @selected($oldFlag === $opt)>
                                            {{ $opt }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Pengumpulan</label>
                                <input type="date" name="tanggal_pengumpulan" value="{{ old('tanggal_pengumpulan') }}"
                                    class="form-control">
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
    {{-- ================= END MODAL: CREATE ================= --}}


    {{-- ================= MODAL: EDIT ================= --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formSerutiEdit" method="post" action="#">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_mode" value="edit">
                    <input type="hidden" name="_id" id="edit_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Data Seruti</h5>
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
                            <input type="text" id="edit_nama_kegiatan" name="nama_kegiatan"
                                value="{{ old('_mode') === 'edit' ? old('nama_kegiatan') : '' }}" class="form-control"
                                required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Blok Sensus/Responden</label>
                                <input type="text" id="edit_bs" name="BS_Responden"
                                    value="{{ old('_mode') === 'edit' ? old('BS_Responden') : '' }}" class="form-control">
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
                                            {{ $opt }}
                                        </option>
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
    {{-- ================= END MODAL: EDIT ================= --}}
@endsection

@push('scripts')
    <script>
        // URL template
        const updateUrlTemplate = "{{ route('sosial.seruti.update', ['seruti' => '__ID__']) }}";
        const destroyUrlTemplate = "{{ route('sosial.seruti.destroy', ['seruti' => '__ID__']) }}";

        // Bootstrap Modal instances
        let modalCreate, modalEdit;

        function openSerutiCreate() {
            modalCreate.show();
        }

        function openSerutiEdit(btn) {
            const id = btn.dataset.id;
            const nama = btn.dataset.nama || '';
            const bs = btn.dataset.bs || '';
            const penc = btn.dataset.pencacah || '';
            const peng = btn.dataset.pengawas || '';
            const target = btn.dataset.target || '';
            const flag = btn.dataset.flag || 'Belum Mulai';
            const kumpul = btn.dataset.kumpul || '';

            const form = document.getElementById('formSerutiEdit');
            form.action = updateUrlTemplate.replace('__ID__', id);

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama_kegiatan').value = nama;
            document.getElementById('edit_bs').value = bs;
            document.getElementById('edit_pencacah').value = penc;
            document.getElementById('edit_pengawas').value = peng;
            document.getElementById('edit_target').value = target;
            document.getElementById('edit_flag').value = flag;
            document.getElementById('edit_kumpul').value = kumpul;

            modalEdit.show();
        }

        // Select all + enable/disable tombol bulk delete
        document.addEventListener('DOMContentLoaded', function () {
            // Init modals
            modalCreate = new bootstrap.Modal(document.getElementById('modalCreate'));
            modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));

            const selectAll = document.getElementById('selectAll');
            const btnBulk = document.getElementById('btnBulkDelete');

            function updateBulkState() {
                const checks = document.querySelectorAll('.row-checkbox');
                const anyChecked = Array.from(checks).some(cb => cb.checked);
                if (btnBulk) btnBulk.disabled = !anyChecked;

                const allChecked = checks.length > 0 && Array.from(checks).every(cb => cb.checked);
                if (selectAll) selectAll.checked = allChecked;
            }

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    document.querySelectorAll('.row-checkbox').forEach(cb => {
                        cb.checked = selectAll.checked;
                    });
                    updateBulkState();
                });
            }

            document.addEventListener('change', function (e) {
                if (e.target && e.target.classList.contains('row-checkbox')) {
                    updateBulkState();
                }
            });

            updateBulkState();
        });

        // Bulk delete: call destroy route for each ID
        async function bulkDelete() {
            const ids = Array.from(document.querySelectorAll('.row-checkbox:checked'))
                .map(cb => cb.dataset.id);
            if (!ids.length) return;
            if (!confirm('Hapus baris yang dipilih?')) return;

            const token = document.getElementById('csrf')?.value ||
                document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            try {
                for (const id of ids) {
                    const url = destroyUrlTemplate.replace('__ID__', id);
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: new URLSearchParams({
                            '_method': 'DELETE'
                        })
                    });

                    if (!res.ok) {
                        const err = await res.json();
                        alert(`Gagal menghapus ID ${id}: ${err.message || 'Error tidak diketahui'}`);
                        return;
                    }
                }
                // Refresh to current tab
                window.location.href = "{{ route('sosial.seruti.index', ['tw' => $tw, 'q' => $q]) }}";
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan saat menghapus data.');
            }
        }

        // Auto open modal if validation fails
        @if ($errors->any())
            @if (old('_mode') === 'create')
                modalCreate.show();
            @elseif (old('_mode') === 'edit')
                (function () {
                    const id = "{{ old('_id') }}";
                    if (id) {
                        const form = document.getElementById('formSerutiEdit');
                        form.action = updateUrlTemplate.replace('__ID__', id);
                    }
                    modalEdit.show();
                })();
            @endif
        @endif
    </script>
@endpush #