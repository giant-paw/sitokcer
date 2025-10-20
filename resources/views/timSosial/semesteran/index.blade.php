@extends('layouts.app')

@section('title', $kategori)
@section('header-title', 'List Target ' . $kategori . ' (Kegiatan Semesteran)')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">LIST TARGET {{ strtoupper($kategori) }} -
                    {{ $semester === 'S1' ? 'Semester 1' : 'Semester 2' }}</h4>
            </div>
            <div class="card-body">
                {{-- Toolbar --}}
                <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">
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
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Tabs Semester 1 & 2 --}}
                @php $tabs = ['S1' => 'Semester 1', 'S2' => 'Semester 2']; @endphp
                <ul class="nav nav-pills mb-3">
                    @foreach ($tabs as $key => $label)
                        <li class="nav-item">
                            <a class="nav-link {{ $semester === $key ? 'active' : '' }}"
                                href="{{ route('sosial.semesteran.index', ['kategori' => $kategori, 'semester' => $key, 'q' => request('q')]) }}">
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                {{-- Search Form --}}
                <form action="{{ route('sosial.semesteran.index', $kategori) }}" method="GET" class="mb-4">
                    <input type="hidden" name="semester" value="{{ $semester }}">
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
                                            value="{{ $row->id_sosial_semesteran }}">
                                    </td>
                                    <td class="text-center">{{ $rows->firstItem() + $i }}</td>
                                    <td>{{ $row->nama_kegiatan }}</td>
                                    <td>{{ $row->BS_Responden }}</td>
                                    <td>{{ $row->pencacah }}</td>
                                    <td>{{ $row->pengawas }}</td>
                                    <td class="text-nowrap">
                                        {{ $row->target_penyelesaian ? $row->target_penyelesaian->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $flag = $row->flag_progress;
                                            $badgeClass = $flag === 'Selesai' ? 'bg-success' : ($flag === 'Proses' ? 'bg-warning text-dark' : 'bg-secondary');
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $flag ?? '-' }}</span>
                                    </td>
                                    <td class="text-nowrap">
                                        {{ $row->tanggal_pengumpulan ? $row->tanggal_pengumpulan->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-sm btn-warning" title="Edit"
                                                data-bs-toggle="modal" data-bs-target="#modalEdit" onclick="openEdit(this)"
                                                data-id="{{ $row->id_sosial_semesteran }}" data-nama="{{ $row->nama_kegiatan }}"
                                                data-bs="{{ $row->BS_Responden }}" data-pencacah="{{ $row->pencacah }}"
                                                data-pengawas="{{ $row->pengawas }}"
                                                data-target="{{ $row->target_penyelesaian ? $row->target_penyelesaian->format('Y-m-d') : '' }}"
                                                data-flag="{{ $row->flag_progress }}"
                                                data-kumpul="{{ $row->tanggal_pengumpulan ? $row->tanggal_pengumpulan->format('Y-m-d') : '' }}">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" title="Hapus" data-bs-toggle="modal"
                                                data-bs-target="#deleteDataModal"
                                                onclick="deleteData({{ $row->id_sosial_semesteran }})">
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

    {{-- ============== MODAL CREATE ============== --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" action="{{ route('sosial.semesteran.store', $kategori) }}">
                    @csrf
                    <input type="hidden" name="semester" value="{{ $semester }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Data {{ $kategori }}
                            ({{ $semester === 'S1' ? 'Semester 1' : 'Semester 2' }})</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        {{-- Error validation --}}
                        <div class="mb-3">
                            <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kegiatan"
                                value="{{ old('nama_kegiatan', $kategori . '-' . ($semester === 'S1' ? 'Semester 1' : 'Semester 2')) }}"
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
                                        <option value="{{ $opt }}" @selected($oldFlag === $opt)>{{ $opt }}</option>
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

    {{-- ============== MODAL EDIT ============== --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formEdit" method="post" action="#">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Data {{ $kategori }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" id="edit_nama_kegiatan" name="nama_kegiatan" class="form-control" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Blok Sensus/Responden</label>
                                <input type="text" id="edit_bs" name="BS_Responden" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pencacah <span class="text-danger">*</span></label>
                                <input type="text" id="edit_pencacah" name="pencacah" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pengawas <span class="text-danger">*</span></label>
                                <input type="text" id="edit_pengawas" name="pengawas" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Target Penyelesaian</label>
                                <input type="date" id="edit_target" name="target_penyelesaian" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Flag Progress <span class="text-danger">*</span></label>
                                <select id="edit_flag" name="flag_progress" class="form-select" required>
                                    @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                        <option value="{{ $opt }}">{{ $opt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Pengumpulan</label>
                                <input type="date" id="edit_kumpul" name="tanggal_pengumpulan" class="form-control">
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

    {{-- ============== MODAL DELETE ============== --}}
    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="deleteForm" method="POST" action="#">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
        const updateUrlTemplate = "{{ route('sosial.semesteran.update', [$kategori, '__ID__']) }}";
        const deleteUrlTemplate = "{{ route('sosial.semesteran.destroy', [$kategori, '__ID__']) }}";
        const bulkDeleteUrl = "{{ route('sosial.semesteran.bulkDelete', $kategori) }}";

        function deleteData(id) {
            const form = document.getElementById('deleteForm');
            form.action = deleteUrlTemplate.replace('__ID__', id);
            form.querySelector('input[name="_method"]').value = 'DELETE';
            form.querySelectorAll('input[name="ids[]"]').forEach(input => input.remove());
            document.getElementById('deleteMessage').textContent = 'Apakah Anda yakin ingin menghapus data ini?';
        }

        function openEdit(btn) {
            const form = document.getElementById('formEdit');
            const id = btn.dataset.id;
            form.action = updateUrlTemplate.replace('__ID__', id);

            document.getElementById('edit_nama_kegiatan').value = btn.dataset.nama || '';
            document.getElementById('edit_bs').value = btn.dataset.bs || '';
            document.getElementById('edit_pencacah').value = btn.dataset.pencacah || '';
            document.getElementById('edit_pengawas').value = btn.dataset.pengawas || '';
            document.getElementById('edit_target').value = btn.dataset.target || '';
            document.getElementById('edit_flag').value = btn.dataset.flag || 'Belum Mulai';
            document.getElementById('edit_kumpul').value = btn.dataset.kumpul || '';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const perPageSelect = document.getElementById('perPageSelect');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', function () {
                    const url = new URL(window.location.href);
                    url.searchParams.set('per_page', this.value);
                    url.searchParams.set('page', 1);
                    window.location.href = url.toString();
                });
            }

            const selectAll = document.getElementById('selectAll');
            const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');

            function updateBulkDeleteState() {
                if (!bulkDeleteBtn) return;
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                bulkDeleteBtn.disabled = checkedCount === 0;
            }

            if (selectAll) {
                selectAll.addEventListener('change', () => {
                    rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
                    updateBulkDeleteState();
                });
            }
            rowCheckboxes.forEach(cb => cb.addEventListener('change', updateBulkDeleteState));
            updateBulkDeleteState();

            if (bulkDeleteBtn) {
                bulkDeleteBtn.addEventListener('click', () => {
                    const ids = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
                    if (ids.length === 0) return;

                    const form = document.getElementById('deleteForm');
                    form.action = bulkDeleteUrl;
                    form.querySelector('input[name="_method"]').value = 'POST';
                    form.querySelectorAll('input[name="ids[]"]').forEach(input => input.remove());
                    ids.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                    document.getElementById('deleteMessage').textContent = `Apakah Anda yakin ingin menghapus ${ids.length} data terpilih?`;
                    new bootstrap.Modal(document.getElementById('deleteDataModal')).show();
                });
            }
        });
    </script>
@endpush