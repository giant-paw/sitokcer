@extends('layouts.app')

@section('title', 'NWA Tahunan')
@section('header-title', 'List Target Kegiatan Tahunan Tim NWA')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">LIST TARGET KEGIATAN TAHUNAN TIM NWA</h4>
            </div>
            <div class="card-body">
                {{-- Toolbar --}}
                <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" onclick="openCreate()">
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
                            @php $options = [10, 20, 30, 50, 100, 500, 'all']; @endphp
                            {{-- Gunakan $rows->perPage() sebagai default jika request per_page tidak ada --}}
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
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Category Tabs (diubah ke nav-pills) --}}
                <ul class="nav nav-pills mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ $kategori === '' ? 'active' : '' }}"
                            href="{{ route('nwa.tahunan.index') }}">
                            Semua
                        </a>
                    </li>
                    @foreach ($kategoris as $k)
                        <li class="nav-item">
                            <a class="nav-link {{ $kategori === $k['label'] ? 'active' : '' }}"
                                href="{{ route('nwa.tahunan.index', ['kategori' => $k['label'], 'q' => $q]) }}">
                                {{ $k['label'] }} <span class="badge bg-secondary">{{ $k['count'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Search Form (dipindah dan distyle ulang) --}}
                <form method="get" action="{{ route('nwa.tahunan.index') }}" class="mb-4">
                    @if ($kategori)
                        <input type="hidden" name="kategori" value="{{ $kategori }}">
                    @endif
                    <div class="row g-2 align-items-center">
                        <div class="col-md-9 col-12">
                            <input type="text" name="q" value="{{ $q }}"
                                placeholder="Cari berdasarkan Responden, Pencacah, atau Pengawas..."
                                class="form-control">
                        </div>
                        <div class="col-md-3 col-12">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center" style="width: 50px;">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
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
                                            value="{{ $row->id_nwa }}">
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
                                            $badgeClass = 'bg-secondary';
                                            if ($flag === 'Selesai') {
                                                $badgeClass = 'bg-success';
                                            } elseif ($flag === 'Proses') {
                                                $badgeClass = 'bg-warning text-dark';
                                            }
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $flag ?? '-' }}</span>
                                    </td>
                                    <td class="text-nowrap">
                                        {{ $row->tanggal_pengumpulan ? \Carbon\Carbon::parse($row->tanggal_pengumpulan)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        {{-- Tombol Aksi Ikon --}}
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-warning" title="Edit"
                                                onclick="openEdit(this)" data-id="{{ $row->id_nwa }}"
                                                data-nama="{{ $row->nama_kegiatan }}"
                                                data-bs="{{ $row->BS_Responden }}"
                                                data-pencacah="{{ $row->pencacah }}"
                                                data-pengawas="{{ $row->pengawas }}"
                                                data-target="{{ $row->target_penyelesaian ? \Carbon\Carbon::parse($row->target_penyelesaian)->format('Y-m-d') : '' }}"
                                                data-flag="{{ $row->flag_progress }}"
                                                data-kumpul="{{ $row->tanggal_pengumpulan ? \Carbon\Carbon::parse($row->tanggal_pengumpulan)->format('Y-m-d') : '' }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" title="Hapus"
                                                data-bs-toggle="modal" data-bs-target="#deleteDataModal"
                                                onclick="deleteData({{ $row->id_nwa }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">Belum ada data.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div> {{-- end card-body --}}
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center">
                <div class="text-muted">
                    Displaying {{ $rows->firstItem() ?? 0 }} - {{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }}
                </div>
                <div>
                    {{ $rows->links() }}
                </div>
            </div>
        </div> {{-- end card --}}
    </div> {{-- end container-fluid --}}

    {{-- ============== MODAL CREATE ============== --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" action="{{ route('nwa.tahunan.store') }}">
                    @csrf
                    <input type="hidden" name="_mode" value="create">

                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Data Baru</h5>
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
                            <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}"
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
                        <h5 class="modal-title">Edit Data</h5>
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
                                value="{{ old('_mode') === 'edit' ? old('nama_kegiatan') : '' }}" class="form-control"
                                required>
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
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- [BARU] Modal Konfirmasi Hapus --}}
    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="deleteForm" method="POST" action="#"> {{-- Action di-set oleh JS --}}
                @csrf
                @method('DELETE') {{-- Method default, akan diubah oleh JS jika bulk --}}
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
        let modalCreate, modalEdit, deleteModal;
        const updateUrlTemplate = "{{ route('nwa.tahunan.update', ['tahunan' => '__ID__']) }}";
        // [BARU] Definisikan URL template untuk hapus
        const deleteUrlTemplate = "{{ route('nwa.tahunan.destroy', ['tahunan' => '__ID__']) }}";
        // [BARU] Definisikan URL untuk bulk delete (Anda harus membuat route ini di web.php)
        const bulkDeleteUrl = "{{ route('nwa.tahunan.bulkDelete') }}";

        document.addEventListener('DOMContentLoaded', function() {
            modalCreate = new bootstrap.Modal(document.getElementById('modalCreate'));
            modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
            deleteModal = new bootstrap.Modal(document.getElementById('deleteDataModal')); // [BARU]

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

            // [BARU] Logic for perPageSelect
            const perPageSelect = document.getElementById('perPageSelect');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function() {
                    const selectedValue = this.value;
                    const currentUrl = new URL(window.location.href);
                    const params = currentUrl.searchParams;
                    params.set('per_page', selectedValue);
                    params.set('page', 1); // Reset to page 1
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

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkDeleteState);
            });

            // Initial state
            updateBulkDeleteState();

            // [BARU] Bulk Delete Button Click
            if (bulkDeleteBtn) {
                bulkDeleteBtn.addEventListener('click', function() {
                    const selectedIds = Array.from(document.querySelectorAll('.row-checkbox:checked'))
                        .map(cb => cb.value);

                    if (selectedIds.length === 0) {
                        return; // Tombol sudah disabled, tapi sebagai pengaman
                    }

                    const deleteForm = document.getElementById('deleteForm');
                    deleteForm.action = bulkDeleteUrl;
                    deleteForm.querySelector('input[name="_method"]').value =
                        'POST'; // Bulk delete biasanya POST

                    // Clear previous inputs
                    deleteForm.querySelectorAll('input[name="ids[]"]').forEach(input => input.remove());

                    // Add new inputs
                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        deleteForm.appendChild(input);
                    });

                    document.getElementById('deleteMessage').textContent =
                        `Apakah Anda yakin ingin menghapus ${selectedIds.length} data terpilih?`;
                    deleteModal.show();
                });
            }
        });

        // (EXISTING) Function openCreate
        function openCreate() {
            modalCreate.show();
        }

        // (EXISTING) Function openEdit
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

        // [BARU] Function deleteData (for single delete)
        function deleteData(id) {
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = deleteUrlTemplate.replace('__ID__', id);
            deleteForm.querySelector('input[name="_method"]').value = 'DELETE'; // Pastikan ini DELETE

            // Clear any bulk delete inputs
            deleteForm.querySelectorAll('input[name="ids[]"]').forEach(input => input.remove());

            document.getElementById('deleteMessage').textContent = 'Apakah Anda yakin ingin menghapus data ini?';
            // Modal akan muncul karena data-bs-toggle di tombol
        }
    </script>
@endpush