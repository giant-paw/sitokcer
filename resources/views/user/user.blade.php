@extends('layouts.app')

{{-- Judul Disesuaikan --}}
@section('title', 'Manajemen User - Sitokcer')
@section('header-title', 'Daftar User Sistem')

{{-- STYLES (jika perlu, misal untuk autocomplete 'tim') --}}
@push('styles')
<style>
    /* Style autocomplete jika diperlukan */
</style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">DAFTAR USER</h4>
            </div>
            <div class="card-body">
                <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal"><i class="bi bi-plus-circle"></i> Tambah User</button>
                        {{-- <button type="button" class="btn btn-secondary"><i class="bi bi-upload"></i> Import</button> --}}
                        {{-- <button type="button" class="btn btn-success"><i class="bi bi-download"></i> Ekspor</button> --}}
                        <button type="button" class="btn btn-danger" data-bs-target="#deleteDataModal" id="bulkDeleteBtn" disabled><i class="bi bi-trash"></i> Hapus User Terpilih</button>
                    </div>

                    {{-- Filter Per Page & Search --}}
                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <label for="perPageSelect" class="form-label me-2 mb-0">Display:</label>
                            <select class="form-select form-select-sm" id="perPageSelect" style="width: auto;">
                                @php $options = [10, 20, 30, 50, 100, 500, 'all']; @endphp
                                @foreach($options as $option)
                                    <option value="{{ $option }}" {{ (request('per_page', 20) == $option) ? 'selected' : '' }}>
                                        {{ $option == 'all' ? 'All' : $option }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Filter Tahun (jika diaktifkan di controller) --}}
                        {{-- <div class="d-flex align-items-center">
                            <label for="tahunSelect" class="form-label me-2 mb-0">Tahun Dibuat:</label>
                            <select class="form-select form-select-sm" id="tahunSelect" style="width: auto;">
                                @foreach($availableTahun ?? [date('Y')] as $tahun)
                                    <option value="{{ $tahun }}" {{ ($selectedTahun ?? date('Y')) == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div> --}}
                    </div>
                </div>

                {{-- Alert Sukses/Error --}}
                @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert"> {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button> </div>
                @elseif (session('error'))
                 <div class="alert alert-danger alert-dismissible fade show" role="alert"> {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button> </div>
                @endif
                @if ($errors->any() && !session('error_modal'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert"> <strong>Error!</strong> Periksa form. <button type="button" class="btn-close" data-bs-dismiss="alert"></button> </div>
                @endif

                {{-- Tab Filter by Tim --}}
                <ul class="nav nav-pills mb-3 d-flex flex-wrap gap-2" >
                    <li class="nav-item">
                        <a class="nav-link {{ empty($selectedTim) ? 'active' : '' }}"
                           href="{{ route('users.index', ['tahun' => $selectedTahun ?? date('Y')]) }}"> {{-- Tambah filter tahun jika dipakai --}}
                           Semua Tim
                        </a>
                    </li>
                    @foreach($timCounts ?? [] as $tim)
                         @if(!empty($tim->tim)) {{-- Tampilkan hanya jika tim tidak kosong --}}
                        <li class="nav-item">
                            <a class="nav-link {{ ($selectedTim ?? '') == $tim->tim ? 'active' : '' }}"
                               href="{{ route('users.index', ['tim' => $tim->tim, 'tahun' => $selectedTahun ?? date('Y')]) }}"> {{-- Tambah filter tahun jika dipakai --}}
                                {{ $tim->tim }} <span class="badge bg-secondary">{{ $tim->total }}</span>
                            </a>
                        </li>
                        @endif
                    @endforeach
                     {{-- Tambahkan tab untuk 'Tim Kosong' jika perlu --}}
                     @php
                        $timKosongCount = $timCounts->firstWhere('tim', null);
                     @endphp
                     @if($timKosongCount && $timKosongCount->total > 0)
                        <li class="nav-item">
                             <a class="nav-link {{ $selectedTim === '' && request()->has('tim') ? 'active' : '' }}"
                                href="{{ route('users.index', ['tim' => '', 'tahun' => $selectedTahun ?? date('Y')]) }}">
                                Tanpa Tim <span class="badge bg-secondary">{{ $timKosongCount->total }}</span>
                            </a>
                        </li>
                     @endif
                </ul>

                {{-- Form Pencarian --}}
                <form action="{{ route('users.index') }}" method="GET" class="mb-4">
                    <div class="row g-2 align-items-center">
                        {{-- <input type="hidden" name="tahun" value="{{ $selectedTahun ?? date('Y') }}"> --}} {{-- Aktifkan jika filter tahun dipakai --}}
                        @if(!empty($selectedTim))
                            <input type="hidden" name="tim" value="{{ $selectedTim }}">
                        @elseif (request()->has('tim') && $selectedTim === '') {{-- Handle tim kosong --}}
                             <input type="hidden" name="tim" value="">
                        @endif
                        <div class="col-md-9 col-12">
                            <input type="text" class="form-control" placeholder="Cari Nama, Username, Email, Tim..." name="search" value="{{ $search ?? '' }}">
                        </div>
                        <div class="col-md-3 col-12">
                            <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i> Cari</button>
                        </div>
                    </div>
                </form>

                {{-- Tabel Data --}}
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 1%;"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Tim</th>
                                <th>Dibuat Pada</th> {{-- Opsional --}}
                                <th style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listData ?? [] as $item)
                                <tr>
                                    {{-- Primary key disesuaikan --}}
                                    <td class="text-center">
                                        {{-- Jangan centang admin utama (ID 1) atau user sendiri --}}
                                        @if($item->id_user != 1 && $item->id_user != auth()->user()->id_user)
                                            <input type="checkbox" class="form-check-input row-checkbox" value="{{ $item->id_user }}">
                                        @endif
                                    </td>
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->username }}</td>
                                    <td>{{ $item->email }}</td>
                                    <td class="text-center">{{ $item->tim ?? '-' }}</td>
                                    <td class="text-center">{{ $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-'}}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            {{-- Primary key disesuaikan --}}
                                            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editDataModal" onclick="editData({{ $item->id_user }})"><i class="bi bi-pencil-square"></i></button>
                                            {{-- Jangan tampilkan tombol hapus untuk admin utama (ID 1) atau user sendiri --}}
                                            @if($item->id_user != 1 && $item->id_user != auth()->user()->id_user)
                                                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteDataModal" onclick="deleteData({{ $item->id_user }})"><i class="bi bi-trash"></i></button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data user yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
             <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted small"> Displaying {{ $listData->firstItem() ?? 0 }} - {{ $listData->lastItem() ?? 0 }} of {{ $listData->total() ?? 0 }} </div>
                <div> {{ $listData->links() ?? '' }} </div>
            </div>
        </div>
    </div>

    {{-- ================== MODAL TAMBAH USER ================== --}}
    <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('users.store') }}" method="POST" id="tambahForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahDataModalLabel">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- Nama --}}
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama') }}" required>
                        <div class="invalid-feedback" data-field="nama">@error('nama'){{ $message }}@enderror</div>
                    </div>
                    {{-- Username --}}
                    <div class="mb-3">
                        <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username') }}" required>
                        <div class="invalid-feedback" data-field="username">@error('username'){{ $message }}@enderror</div>
                    </div>
                     {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                        <div class="invalid-feedback" data-field="email">@error('email'){{ $message }}@enderror</div>
                    </div>
                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        <div class="invalid-feedback" data-field="password">@error('password'){{ $message }}@enderror</div>
                        <small class="form-text text-muted">Minimal 6 karakter, mengandung huruf dan angka.</small>
                    </div>
                    {{-- Password Confirmation --}}
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    {{-- Tim --}}
                    <div class="mb-3">
                        <label for="tim" class="form-label">Tim</label>
                        <input type="text" class="form-control @error('tim') is-invalid @enderror" id="tim" name="tim" value="{{ old('tim') }}">
                         <small class="form-text text-muted">Contoh: Produksi, Sosial, NWA, IPDS, Umum.</small>
                        <div class="invalid-feedback" data-field="tim">@error('tim'){{ $message }}@enderror</div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"> <span class="spinner-border spinner-border-sm d-none"></span> Simpan </button>
                </div>
            </div>
            </form>
        </div>
    </div>

    {{-- ================== MODAL EDIT USER ================== --}}
    <div class="modal fade" id="editDataModal" tabindex="-1" aria-labelledby="editDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST"> {{-- Action diatur JS --}}
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editDataModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                         <input type="hidden" id="edit_id_fallback" value="{{ session('edit_id') ?? '' }}">

                         <div class="mb-3">
                            <label for="edit_nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="edit_nama" name="nama" required>
                            <div class="invalid-feedback" data-field="nama">@error('nama'){{ $message }}@enderror</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('username') is-invalid @enderror" id="edit_username" name="username" required>
                            <div class="invalid-feedback" data-field="username">@error('username'){{ $message }}@enderror</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="edit_email" name="email" required>
                            <div class="invalid-feedback" data-field="email">@error('email'){{ $message }}@enderror</div>
                        </div>
                         <hr>
                         <p class="text-muted"><small>Kosongkan password jika tidak ingin mengubahnya.</small></p>
                         <div class="mb-3">
                            <label for="edit_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="edit_password" name="password">
                            <div class="invalid-feedback" data-field="password">@error('password'){{ $message }}@enderror</div>
                             <small class="form-text text-muted">Minimal 6 karakter, mengandung huruf dan angka.</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation">
                        </div>
                         <hr>
                        <div class="mb-3">
                            <label for="edit_tim" class="form-label">Tim</label>
                            <input type="text" class="form-control @error('tim') is-invalid @enderror" id="edit_tim" name="tim">
                             <small class="form-text text-muted">Contoh: Produksi, Sosial, NWA, IPDS, Umum.</small>
                            <div class="invalid-feedback" data-field="tim">@error('tim'){{ $message }}@enderror</div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none"></span> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================== MODAL HAPUS USER ================== --}}
    <div class="modal fade" id="deleteDataModal" tabindex="-1" aria-labelledby="deleteDataModalLabel" aria-hidden="true">
        <div class="modal-dialog">
             <form id="deleteForm" method="POST"> @csrf @method('DELETE') <div class="modal-content"> <div class="modal-header"> <h5 class="modal-title">Konfirmasi Hapus</h5> <button type="button" class="btn-close" data-bs-dismiss="modal"></button> </div> <div class="modal-body" id="deleteModalBody"> Hapus user ini? Tindakan ini tidak bisa dibatalkan. </div> <div class="modal-footer"> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button> <button type="submit" class="btn btn-danger" id="confirmDeleteButton">Hapus</button> </div> </div> </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // --- URL Basis BARU untuk User ---
    const usersBaseUrl = '/users'; // Sesuaikan jika prefix berbeda

    /** Edit Data */
    function editData(id) {
        const editModalEl = document.getElementById('editDataModal'); if (!editModalEl) return;
        const editModal = bootstrap.Modal.getOrCreateInstance(editModalEl);
        const editForm = document.getElementById('editForm');

        // Gunakan URL baru
        editForm.action = `${usersBaseUrl}/${id}`;
        clearFormErrors(editForm);
        document.getElementById('edit_id_fallback').value = id;
        // Kosongkan field password saat modal dibuka
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_password_confirmation').value = '';


        // Gunakan URL baru
        fetch(`${usersBaseUrl}/${id}/edit`)
            .then(response => { if (!response.ok) { return response.text().then(text => { throw new Error(text || 'Data user tidak ditemukan'); }); } return response.json(); })
            .then(data => {
                document.getElementById('edit_nama').value = data.nama || '';
                document.getElementById('edit_username').value = data.username || '';
                document.getElementById('edit_email').value = data.email || '';
                document.getElementById('edit_tim').value = data.tim || '';
                // JANGAN ISI PASSWORD
                editModal.show();
            })
            .catch(error => { console.error("Error loading edit data:", error); alert('Tidak dapat memuat data user. Error: ' + error.message); });
    }

    /** Delete Data */
    function deleteData(id) {
        const deleteModalEl = document.getElementById('deleteDataModal'); if (!deleteModalEl) return;
        const deleteModal = bootstrap.Modal.getOrCreateInstance(deleteModalEl);
        const deleteForm = document.getElementById('deleteForm');

        // Gunakan URL baru
        deleteForm.action = `${usersBaseUrl}/${id}`;

        let methodInput = deleteForm.querySelector('input[name="_method"]'); if (!methodInput) { methodInput = document.createElement('input'); methodInput.type = 'hidden'; methodInput.name = '_method'; deleteForm.appendChild(methodInput); } methodInput.value = 'DELETE';
        deleteForm.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove());
        // Ambil nama user untuk konfirmasi (opsional)
        // const userName = document.querySelector(`tr[data-user-id="${id}"] td:nth-child(2)`).textContent;
        document.getElementById('deleteModalBody').innerText = `Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak bisa dibatalkan.`;
        const newConfirmButton = document.getElementById('confirmDeleteButton').cloneNode(true); document.getElementById('confirmDeleteButton').parentNode.replaceChild(newConfirmButton, document.getElementById('confirmDeleteButton')); newConfirmButton.addEventListener('click', (e) => { e.preventDefault(); deleteForm.submit(); });
        deleteModal.show();
    }

    /** AJAX Helpers */
    function clearFormErrors(form) { form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid')); form.querySelectorAll('.invalid-feedback[data-field]').forEach(el => el.textContent = ''); }
    function showFormErrors(form, errors) { for (const [field, messages] of Object.entries(errors)) { const input = form.querySelector(`[name="${field}"]`); const errorDiv = form.querySelector(`.invalid-feedback[data-field="${field}"]`); if (input) input.classList.add('is-invalid'); if (errorDiv) errorDiv.textContent = messages[0]; } }
    async function handleFormSubmitAjax(event, form, modalInstance) { event.preventDefault(); const sb = form.querySelector('button[type="submit"]'); const sp = sb.querySelector('.spinner-border'); sb.disabled = true; if (sp) sp.classList.remove('d-none'); clearFormErrors(form); try { const fd = new FormData(form); const response = await fetch(form.action, { method: form.method, body: fd, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': fd.get('_token') } }); const data = await response.json(); if (!response.ok) { if (response.status === 422 && data.errors) { showFormErrors(form, data.errors); } else { alert(data.message || 'Terjadi kesalahan.'); } } else { modalInstance.hide(); location.reload(); } } catch (error) { console.error('Fetch error:', error); alert('Tidak dapat terhubung ke server.'); } finally { sb.disabled = false; if (sp) sp.classList.add('d-none'); } }

    /** DOM Ready */
    document.addEventListener('DOMContentLoaded', function() {

        // --- Init Autocomplete (jika 'tim' mau pakai autocomplete) ---
        // initAutocomplete('tim', 'tim-suggestions', 'URL_SEARCH_TIM');
        // initAutocomplete('edit_tim', 'edit-tim-suggestions', 'URL_SEARCH_TIM');

        // --- Init AJAX Form Handlers ---
        const tme = document.getElementById('tambahDataModal'); const tf = document.getElementById('tambahForm'); if (tme && tf) { const tm = bootstrap.Modal.getOrCreateInstance(tme); tf.addEventListener('submit', (e) => handleFormSubmitAjax(e, tf, tm)); tme.addEventListener('hidden.bs.modal', () => { clearFormErrors(tf); tf.reset(); }); }
        const eme = document.getElementById('editDataModal'); const ef = document.getElementById('editForm'); if (eme && ef) { const em = bootstrap.Modal.getOrCreateInstance(eme); ef.addEventListener('submit', (e) => handleFormSubmitAjax(e, ef, em)); eme.addEventListener('hidden.bs.modal', () => { clearFormErrors(ef); /* Reset edit form tidak diperlukan karena diisi ulang via JS */ }); }

        // --- Select All & Bulk Delete ---
        const sa = document.getElementById('selectAll'); const rcb = document.querySelectorAll('.row-checkbox'); const bdb = document.getElementById('bulkDeleteBtn'); const df = document.getElementById('deleteForm'); function ubdbs() { const cc = document.querySelectorAll('.row-checkbox:checked').length; if (bdb) bdb.disabled = cc === 0; } sa?.addEventListener('change', () => { rcb.forEach(cb => cb.checked = sa.checked); ubdbs(); }); rcb.forEach(cb => cb.addEventListener('change', ubdbs)); ubdbs(); bdb?.addEventListener('click', () => { const count = document.querySelectorAll('.row-checkbox:checked').length; if (count === 0) return; const dme = document.getElementById('deleteDataModal'); if (!dme || !df) return; const dm = bootstrap.Modal.getOrCreateInstance(dme);
        df.action = '{{ route("users.bulkDelete") }}'; // Gunakan route baru
        let mi = df.querySelector('input[name="_method"]'); if (mi) mi.value = 'POST'; // Bulk delete pakai POST
        df.querySelectorAll('input[name="ids[]"]').forEach(i => i.remove()); document.querySelectorAll('.row-checkbox:checked').forEach(cb => { const i = document.createElement('input'); i.type = 'hidden'; i.name = 'ids[]'; i.value = cb.value; df.appendChild(i); }); document.getElementById('deleteModalBody').innerText = `Hapus ${count} user terpilih? User admin utama dan akun Anda tidak akan dihapus.`; const ncb = document.getElementById('confirmDeleteButton').cloneNode(true); document.getElementById('confirmDeleteButton').parentNode.replaceChild(ncb, document.getElementById('confirmDeleteButton')); ncb.addEventListener('click', (e) => { e.preventDefault(); df.submit(); }); dm.show(); });

        // --- Filters Per Page & Tahun ---
        const pps = document.getElementById('perPageSelect');
        const ts = document.getElementById('tahunSelect'); // Ambil elemen tahun jika ada
        function hfc() {
            const cu = new URL(window.location.href);
            const p = cu.searchParams;
            if (pps) p.set('per_page', pps.value);
            // Hanya set tahun jika elemennya ada
            if (ts) p.set('tahun', ts.value);
            else p.delete('tahun'); // Hapus parameter tahun jika filter tidak dipakai
            p.set('page', 1); // Reset ke halaman 1

            // Ganti path jika perlu (misal: /users) atau biarkan pathname
            // window.location.href = cu.pathname + '?' + p.toString();
             window.location.href = '{{ route("users.index") }}' + '?' + p.toString(); // Lebih aman pakai route()
        }
        if (pps) pps.addEventListener('change', hfc);
        if (ts) ts.addEventListener('change', hfc); // Hanya tambahkan listener jika elemen ada


        // --- Fallback Error Modals ---
        @if (session('error_modal') == 'tambahDataModal' && $errors->any())
            const tmef = document.getElementById('tambahDataModal');
            if (tmef) bootstrap.Modal.getOrCreateInstance(tmef).show();
        @endif

        @if (session('error_modal') == 'editDataModal' && $errors->any())
            const eid_fb = document.getElementById('edit_id_fallback')?.value;
            if (eid_fb) {
                const emef = document.getElementById('editDataModal');
                if(emef) {
                    const edf = document.getElementById('editForm');
                    edf.action = `${usersBaseUrl}/${eid_fb}`; // Gunakan URL baru
                    bootstrap.Modal.getOrCreateInstance(emef).show();
                    // Error sudah ditangani oleh @error di HTML, tapi kita tambahkan class is-invalid
                    setTimeout(() => {
                        @foreach ($errors->keys() as $f)
                            const fel = edf.querySelector('[name="{{ $f }}"]');
                            if (fel) fel.classList.add('is-invalid');
                            // Teks error sudah ada dari @error
                        @endforeach
                    }, 100); // Sedikit delay agar modal sempat muncul
                }
            }
        @endif

        // --- Auto-hide Alerts ---
        const alertList = document.querySelectorAll('.alert-dismissible[role="alert"]');
        alertList.forEach(function (alert) {
            // Cek jika alert BUKAN di dalam modal DAN memiliki auto_hide dari session
            if (!alert.closest('.modal')) {
                const autoHide = {{ session('auto_hide', 'false') ? 'true' : 'false' }};
                if(autoHide) {
                    setTimeout(() => {
                        bootstrap.Alert.getOrCreateInstance(alert).close();
                    }, 5000); // 5 detik
                 }
            }
        })

    });
</script>
@endpush