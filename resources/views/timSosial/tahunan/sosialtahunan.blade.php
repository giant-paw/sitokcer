@extends('layouts.app')

@section('title', 'Sosial Tahunan')
@section('header-title', 'List Target Kegiatan Tahunan Tim Sosial')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">LIST TARGET KEGIATAN TAHUNAN TIM SOSIAL</h4>
            </div>
            <div class="card-body">
                {{-- Toolbar --}}
                <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#modalCreate" onclick="prefillKategori('{{ $kategori }}')">
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
                            @php $per_page = request('per_page', $items->perPage()); @endphp
                            @foreach ($options as $option)
                                <option value="{{ $option }}" {{ $per_page == $option ? 'selected' : '' }}>
                                    {{ $option == 'all' ? 'All' : $option }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Alert --}}
                @if (session('ok'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('ok') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Tabs kategori (diubah jadi nav-pills) --}}
                <ul class="nav nav-pills mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ !$kategori ? 'active' : '' }}"
                            href="{{ route('sosial.tahunan.index') }}">Semua</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $kategori === 'Polkam' ? 'active' : '' }}"
                            href="{{ route('sosial.tahunan.index', ['kategori' => 'Polkam']) }}">
                            Polkam <span class="badge bg-secondary">{{ $countPolkam }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $kategori === 'PODES' ? 'active' : '' }}"
                            href="{{ route('sosial.tahunan.index', ['kategori' => 'PODES']) }}">
                            Podes <span class="badge bg-secondary">{{ $countPodes }}</span>
                        </a>
                    </li>
                </ul>

                {{-- Search --}}
                <form method="get" class="mb-4">
                    <input type="hidden" name="kategori" value="{{ $kategori }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-9 col-12">
                            <input type="text" class="form-control" name="q" value="{{ $q }}"
                                placeholder="Cari berdasarkan Responden, Pencacah, Pengawas...">
                        </div>
                        <div class="col-md-3 col-12">
                            <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i>
                                Cari</button>
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
                                <th class="text-center" style="width: 56px">#</th>
                                <th>Nama Kegiatan</th>
                                <th>Blok Sensus/Responden</th>
                                <th>Pencacah</th>
                                <th>Pengawas</th>
                                <th>Target Selesai</th>
                                <th class="text-center">Progress</th>
                                <th>Tgl Kumpul</th>
                                <th class="text-center" style="width: 140px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $i => $it)
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input row-checkbox"
                                            value="{{ $it->id_sosial_tahunan }}"> {{-- Pastikan primary key benar --}}
                                    </td>
                                    <td class="text-center">{{ $items->firstItem() + $i }}</td>
                                    <td>{{ $it->nama_kegiatan }}</td>
                                    <td>{{ $it->BS_Responden }}</td>
                                    <td>{{ $it->pencacah }}</td>
                                    <td>{{ $it->pengawas }}</td>
                                    <td class="text-nowrap">{{ $it->target_penyelesaian_formatted }}</td>
                                    <td class="text-center">
                                        <span
                                            class="badge
                                            {{ $it->flag_progress === 'Selesai' ? 'bg-success' : ($it->flag_progress === 'Proses' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                            {{ $it->flag_progress }}
                                        </span>
                                    </td>
                                    <td class="text-nowrap">{{ $it->tanggal_pengumpulan_formatted }}</td>
                                    <td class="text-center">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="{{ route('sosial.tahunan.show', $it) }}"
                                                class="btn btn-sm btn-secondary" title="Lihat"><i
                                                    class="bi bi-eye"></i></a>
                                            <a href="{{ route('sosial.tahunan.edit', $it) }}"
                                                class="btn btn-sm btn-warning" title="Edit"><i
                                                    class="bi bi-pencil-square"></i></a>
                                            <button class="btn btn-sm btn-danger" title="Hapus"
                                                data-bs-toggle="modal" data-bs-target="#deleteDataModal"
                                                onclick="deleteData({{ $it->id_sosial_tahunan }})">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">Belum ada data</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between align-items-center">
                <div class="text-muted">
                    Displaying {{ $items->firstItem() ?? 0 }} - {{ $items->lastItem() ?? 0 }} of {{ $items->total() }}
                </div>
                <div>
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- ================== MODAL TAMBAH (Tidak Berubah) ================== --}}
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
                                <input type="text" name="pencacah" class="form-control"
                                    value="{{ old('pencacah') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pengawas <span class="text-danger">*</span></label>
                                <input type="text" name="pengawas" class="form-control"
                                    value="{{ old('pengawas') }}" required>
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
                                    <option value="" disabled {{ old('flag_progress') ? '' : 'selected' }}>
                                        Silahkan pilih</option>
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
        // Definisikan URL template
        const deleteUrlTemplate = "{{ route('sosial.tahunan.destroy', ['tahunan' => '__ID__']) }}";
        const bulkDeleteUrl = "{{ route('sosial.tahunan.bulkDelete') }}";

        // (EXISTING) Prefill nama_kegiatan
        function prefillKategori(kat) {
            if (!kat) return;
            const inp = document.querySelector('#modalCreate input[name=nama_kegiatan]');
            if (inp && !inp.value) inp.value = kat;
        }

        // [BARU] Function untuk single delete
        function deleteData(id) {
            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = deleteUrlTemplate.replace('__ID__', id);
            deleteForm.querySelector('input[name="_method"]').value = 'DELETE';
            deleteForm.querySelectorAll('input[name="ids[]"]').forEach(input => input.remove());
            document.getElementById('deleteMessage').textContent = 'Apakah Anda yakin ingin menghapus data ini?';
        }

        document.addEventListener('DOMContentLoaded', function() {
            // (EXISTING) Auto-open modal on validation failure
            @if ($errors->any() || !empty($openModal))
                const m = new bootstrap.Modal(document.getElementById('modalCreate'));
                m.show();
            @endif

            const deleteModal = new bootstrap.Modal(document.getElementById('deleteDataModal'));

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

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkDeleteState);
            });
            updateBulkDeleteState(); // Initial state

            // [BARU] Bulk Delete Button Click
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

                    document.getElementById('deleteMessage').textContent =
                        `Apakah Anda yakin ingin menghapus ${selectedIds.length} data terpilih?`;
                    deleteModal.show();
                });
            }
        });
    </script>
@endpush
