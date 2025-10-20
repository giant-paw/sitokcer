@extends('layouts.app')

@section('title', 'Seruti')
@section('header-title', 'List Target Seruti (Kegiatan Triwulanan)')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">LIST TARGET SURVEI EKONOMI RUMAH TANGGA TRIWULANAN (SERUTI) - {{ $tw }}</h4>
            </div>
            <div class="card-body">
                {{-- Toolbar --}}
                <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" onclick="openSerutiCreate()">
                            <i class="bi bi-plus-circle"></i> Tambah Baru
                        </button>
                        <button type="button" class="btn btn-secondary">
                            <i class="bi bi-upload"></i> Import
                        </button>
                        <button type="button" class="btn btn-success">
                            <i class="bi bi-download"></i> Ekspor Hasil
                        </button>
                        <button type="button" class="btn btn-danger" data-bs-target="#deleteDataModal" id="bulkDeleteBtn"
                            disabled>
                            <i class="bi bi-trash"></i> Hapus Data Terpilih
                        </button>
                    </div>
                    <div class="d-flex align-items-center">
                        <label for="perPageSelect" class="form-label me-2 mb-0">Display:</label>
                        <select class="form-select form-select-sm" id="perPageSelect" style="width: auto;">
                            @php $options = [10, 20, 30, 50, 100, 'all']; @endphp
                            @php $per_page = request('per_page', $rows->perPage()); @endphp
                            @foreach ($options as $option)
                                <option value="{{ $option }}" {{ $per_page == $option ? 'selected' : '' }}>
                                    {{ $option == 'all' ? 'All' : $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Alert Sukses (jika ada) --}}
                @if (session('ok'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('ok') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Tabs TW1..TW4 (diubah jadi nav-pills) --}}
                @php $tabs = ['TW1', 'TW2', 'TW3', 'TW4']; @endphp
                <ul class="nav nav-pills mb-3">
                    @foreach ($tabs as $t)
                        <li class="nav-item">
                            <a class="nav-link {{ $tw === $t ? 'active' : '' }}"
                                href="{{ route('sosial.seruti.index', ['tw' => $t, 'q' => request('q')]) }}">
                                Seruti-{{ $t }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Search Form --}}
                <form action="{{ route('sosial.seruti.index') }}" method="GET" class="mb-4">
                    <input type="hidden" name="tw" value="{{ $tw }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-9 col-12">
                            <input type="text" name="q" value="{{ $q }}"
                                placeholder="Cari berdasarkan Responden, Pencacah, Pengawas..." class="form-control">
                        </div>
                        <div class="col-md-3 col-12">
                            <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i> Cari</button>
                        </div>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 50px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
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
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input row-checkbox"
                                            value="{{ $row->id_sosial_triwulanan }}">
                                    </td>
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
                                            $badgeClass = $flag === 'Selesai' ? 'bg-success' : ($flag === 'Proses' ? 'bg-warning text-dark' : 'bg-secondary');
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $flag ?? '-' }}</span>
                                    </td>
                                    <td class="text-nowrap">
                                        {{ $row->tanggal_pengumpulan ? \Carbon\Carbon::parse($row->tanggal_pengumpulan)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('sosial.seruti.show', $row) }}" class="btn btn-sm btn-secondary" title="Lihat"><i class="bi bi-eye"></i></a>
                                            <button type="button" class="btn btn-sm btn-warning" title="Edit"
                                                onclick="openSerutiEdit(this)"
                                                data-id="{{ $row->id_sosial_triwulanan }}"
                                                data-nama="{{ $row->nama_kegiatan }}"
                                                data-bs="{{ $row->BS_Responden }}"
                                                data-pencacah="{{ $row->pencacah }}"
                                                data-pengawas="{{ $row->pengawas }}"
                                                data-target="{{ $row->target_penyelesaian ? \Carbon\Carbon::parse($row->target_penyelesaian)->format('Y-m-d') : '' }}"
                                                data-flag="{{ $row->flag_progress }}"
                                                data-kumpul="{{ $row->tanggal_pengumpulan ? \Carbon\Carbon::parse($row->tanggal_pengumpulan)->format('Y-m-d') : '' }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" title="Hapus" data-bs-toggle="modal" data-bs-target="#deleteDataModal" onclick="deleteData({{ $row->id_sosial_triwulanan }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">Data tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center">
                <div class="text-muted">
                    Displaying {{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }}
                </div>
                <div>
                    {{ $rows->links() }}
                </div>
            </div>
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

    {{-- [BARU] Modal Konfirmasi Hapus --}}
    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="deleteForm" method="POST" action="#">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteDataModalLabel">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <span id="deleteMessage">Apakah Anda yakin ingin menghapus data ini?</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // URL template
        const updateUrlTemplate = "{{ route('sosial.seruti.update', ['seruti' => '__ID__']) }}";
        const deleteUrlTemplate = "{{ route('sosial.seruti.destroy', ['seruti' => '__ID__']) }}";
        const bulkDeleteUrl = "{{ route('sosial.seruti.bulkDelete') }}";

        let modalCreate, modalEdit, deleteModal;

        // [BARU] Function untuk single delete
        function deleteData(id) {
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = deleteUrlTemplate.replace('__ID__', id);
            deleteForm.querySelector('input[name="_method"]').value = 'DELETE';
            deleteForm.querySelectorAll('input[name="ids[]"]').forEach(input => input.remove());
            document.getElementById('deleteMessage').textContent = 'Apakah Anda yakin ingin menghapus data ini?';
        }
        
        function openSerutiCreate() {
            // Set nama kegiatan default berdasarkan prefix dan TW
            const inputNama = document.querySelector('#modalCreate input[name="nama_kegiatan"]');
            if (inputNama) {
                inputNama.value = 'Seruti-{{ $tw }}';
            }
            modalCreate.show();
        }
        
        function openSerutiEdit(btn) {
             const id = btn.dataset.id;
             const form = document.getElementById('formSerutiEdit');
             form.action = updateUrlTemplate.replace('__ID__', id);
             document.getElementById('edit_id').value = id;
             document.getElementById('edit_nama_kegiatan').value = btn.dataset.nama || '';
             document.getElementById('edit_bs').value = btn.dataset.bs || '';
             document.getElementById('edit_pencacah').value = btn.dataset.pencacah || '';
             document.getElementById('edit_pengawas').value = btn.dataset.pengawas || '';
             document.getElementById('edit_target').value = btn.dataset.target || '';
             document.getElementById('edit_flag').value = btn.dataset.flag || 'Belum Mulai';
             document.getElementById('edit_kumpul').value = btn.dataset.kumpul || '';
             modalEdit.show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            modalCreate = new bootstrap.Modal(document.getElementById('modalCreate'));
            modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
            deleteModal = new bootstrap.Modal(document.getElementById('deleteDataModal'));

            // Auto-open modal on validation failure
            @if ($errors->any())
                @if (old('_mode') === 'create')
                    modalCreate.show();
                @elseif (old('_mode') === 'edit')
                    (function() {
                        const id = "{{ old('_id') }}";
                        if (id) {
                            const form = document.getElementById('formSerutiEdit');
                            form.action = updateUrlTemplate.replace('__ID__', id);
                        }
                        modalEdit.show();
                    })();
                @endif
            @endif

            // [BARU] Logic for perPageSelect
            const perPageSelect = document.getElementById('perPageSelect');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    const selectedValue = this.value;
                    const currentUrl = new URL(window.location.href);
                    const params = currentUrl.searchParams;
                    params.set('per_page', selectedValue);
                    params.set('page', 1);
                    window.location.href = currentUrl.pathname + '?' + params.toString();
                });
            }

            // [BARU] Logic for Checkboxes and Bulk Delete
            const selectAll = document.getElementById('selectAll');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');

            function updateBulkDeleteState() {
                if (!bulkDeleteBtn) return;
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                bulkDeleteBtn.disabled = checkedCount === 0;
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    rowCheckboxes.forEach(cb => cb.checked = this.checked);
                    updateBulkDeleteState();
                });
            }
            rowCheckboxes.forEach(cb => { cb.addEventListener('change', updateBulkDeleteState); });
            updateBulkDeleteState();

            if (bulkDeleteBtn) {
                bulkDeleteBtn.addEventListener('click', function() {
                    const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(
                        cb => cb.value);
                    if (selectedIds.length === 0) return;

                    const deleteForm = document.getElementById('deleteForm');
                    deleteForm.action = bulkDeleteUrl;
                    deleteForm.querySelector('input[name="_method"]').value = 'POST';
                    deleteForm.querySelectorAll('input[name="ids[]"]').forEach(input => input.remove());
                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        deleteForm.appendChild(input);
                    });
                    document.getElementById('deleteMessage').textContent = `Apakah Anda yakin ingin menghapus ${selectedIds.length} data terpilih?`;
                    deleteModal.show();
                });
            }
        });
    </script>
@endpush

