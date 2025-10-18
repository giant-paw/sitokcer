@extends('layouts.app')

@section('title', 'Master Petugas')
@section('header-title', 'Daftar Master Petugas')

@section('content')
    <div class="container-fluid px-0">

        {{-- Toolbar --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <form method="get" action="{{ route('master.petugas.index') }}">
                <div class="input-group" style="max-width: 360px">
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari nama, nik, atau jabatan"
                        class="form-control">
                    <button class="btn btn-outline-secondary">Cari</button>
                </div>
            </form>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-success" onclick="exportData()">Export Hasil</button>
                <button class="btn btn-outline-primary" onclick="importData()">Import</button>
                <button class="btn btn-outline-danger" onclick="bulkDelete()">Hapus</button>
                <button class="btn btn-primary" onclick="openCreate()">Tambah Petugas</button>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="selectAll"></th>
                                <th style="width: 50px;">#</th>
                                <th>NAMA PETUGAS</th>
                                <th>POSISI</th>
                                <th>STATUS</th>
                                <th>NO SK</th>
                                <th>TANGGAL SK</th>
                                <th>NO HP</th>
                                <th>EMAIL</th>
                                <th style="width: 150px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($petugas as $i => $p)
                                <tr>
                                    <td class="text-center"><input type="checkbox" class="rowCheck"
                                            value="{{ $p->id_petugas }}"></td>
                                    <td class="text-center">{{ $i + $petugas->firstItem() }}</td>
                                    <td>{{ $p->nama_petugas }}</td>
                                    <td>{{ $p->posisi }}</td>
                                    <td>
                                        @if($p->status == 'aktif')
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-danger">Tidak Aktif</span>
                                        @endif
                                    </td>
                                    <td>{{ $p->no_sk }}</td>
                                    <td>{{ $p->tgl_sk ? \Carbon\Carbon::parse($p->tgl_sk)->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $p->no_hp }}</td>
                                    <td>{{ $p->email }}</td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-sm btn-outline-info" onclick="openDetail(this)"
                                            data-nama_petugas="{{ $p->nama_petugas }}" data-kategori="{{ $p->kategori }}"
                                            data-nik="{{ $p->nik }}" data-alamat="{{ $p->alamat }}" data-no_hp="{{ $p->no_hp }}"
                                            data-posisi="{{ $p->posisi }}" data-email="{{ $p->email }}" data-pendidikan="{{ $p->pendidikan }}"
                                            data-tmt_pengangkatan="{{ $p->tmt_pengangkatan }}" data-no_sk="{{ $p->no_sk }}"
                                            data-tgl_sk="{{ $p->tgl_sk }}" data-pejabat_pengangkatan="{{ $p->pejabat_pengangkatan }}"
                                            data-foto="{{ $p->foto }}" data-status="{{ $p->status }}">
                                            Detail
                                        </button>

                                        <button class="btn btn-sm btn-outline-primary" onclick="openEdit(this)"
                                            data-id_petugas="{{ $p->id_petugas }}" data-nama_petugas="{{ $p->nama_petugas }}"
                                            data-kategori="{{ $p->kategori }}" data-nik="{{ $p->nik }}"
                                            data-alamat="{{ $p->alamat }}" data-no_hp="{{ $p->no_hp }}"
                                            data-posisi="{{ $p->posisi }}" data-email="{{ $p->email }}" data-pendidikan="{{ $p->pendidikan }}"
                                            data-tmt_pengangkatan="{{ $p->tmt_pengangkatan }}" data-no_sk="{{ $p->no_sk }}"
                                            data-tgl_sk="{{ $p->tgl_sk }}" data-pejabat_pengangkatan="{{ $p->pejabat_pengangkatan }}"
                                            data-foto="{{ $p->foto }}" data-status="{{ $p->status }}">
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">Tidak ada data ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Link Halaman --}}
                <div class="p-3">
                    {{ $petugas->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" action="{{ route('master.petugas.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Petugas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-6"><label>Nama Petugas</label><input type="text" name="nama_petugas" class="form-control" required></div>
                        <div class="col-md-6"><label>Kategori</label><input type="text" name="kategori" class="form-control"></div>
                        <div class="col-md-6"><label>NIK</label><input type="text" name="nik" class="form-control"></div>
                        <div class="col-md-6"><label>Alamat</label><input type="text" name="alamat" class="form-control"></div>
                        <div class="col-md-6"><label>No HP</label><input type="text" name="no_hp" class="form-control"></div>
                        <div class="col-md-6"><label>Posisi</label><input type="text" name="posisi" class="form-control"></div>
                        <div class="col-md-6"><label>Email</label><input type="email" name="email" class="form-control"></div>
                        <div class="col-md-6"><label>Pendidikan</label><input type="text" name="pendidikan" class="form-control"></div>
                        <div class="col-md-6"><label>TMT Pengangkatan</label><input type="date" name="tmt_pengangkatan" class="form-control"></div>
                        <div class="col-md-6"><label>No SK</label><input type="text" name="no_sk" class="form-control"></div>
                        <div class="col-md-6"><label>Tanggal SK</label><input type="date" name="tgl_sk" class="form-control"></div>
                        <div class="col-md-6"><label>Pejabat Pengangkatan</label><input type="text" name="pejabat_pengangkatan" class="form-control"></div>
                        <div class="col-md-6"><label>Foto</label><input type="text" name="foto" class="form-control" placeholder="URL atau path foto"></div>
                        <div class="col-md-6"><label>Status</label>
                            <select name="status" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="tidak aktif">Tidak Aktif</option>
                            </select>
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

    {{-- Modal Edit --}}
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formEdit" method="post" action="#">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Petugas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <input type="hidden" id="edit_id_petugas" name="id_petugas">
                        <div class="col-md-6"><label>Nama Petugas</label><input id="edit_nama_petugas" name="nama_petugas" class="form-control" required></div>
                        <div class="col-md-6"><label>Kategori</label><input id="edit_kategori" name="kategori" class="form-control"></div>
                        <div class="col-md-6"><label>NIK</label><input id="edit_nik" name="nik" class="form-control"></div>
                        <div class="col-md-6"><label>Alamat</label><input id="edit_alamat" name="alamat" class="form-control"></div>
                        <div class="col-md-6"><label>No HP</label><input id="edit_no_hp" name="no_hp" class="form-control"></div>
                        <div class="col-md-6"><label>Posisi</label><input id="edit_posisi" name="posisi" class="form-control"></div>
                        <div class="col-md-6"><label>Email</label><input id="edit_email" name="email" class="form-control"></div>
                        <div class="col-md-6"><label>Pendidikan</label><input id="edit_pendidikan" name="pendidikan" class="form-control"></div>
                        <div class="col-md-6"><label>TMT Pengangkatan</label><input id="edit_tmt_pengangkatan" type="date" name="tmt_pengangkatan" class="form-control"></div>
                        <div class="col-md-6"><label>No SK</label><input id="edit_no_sk" type="text" name="no_sk" class="form-control"></div>
                        <div class="col-md-6"><label>Tanggal SK</label><input id="edit_tgl_sk" type="date" name="tgl_sk" class="form-control"></div>
                        <div class="col-md-6"><label>Pejabat Pengangkatan</label><input id="edit_pejabat_pengangkatan" type="text" name="pejabat_pengangkatan" class="form-control"></div>
                        <div class="col-md-6"><label>Foto</label><input id="edit_foto" type="text" name="foto" class="form-control" placeholder="URL atau path foto"></div>
                        <div class="col-md-6"><label>Status</label>
                            <select id="edit_status" name="status" class="form-select">
                                <option value="aktif">Aktif</option>
                                <option value="tidak aktif">Tidak Aktif</option>
                            </select>
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

    {{-- Modal Detail --}}
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Petugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                             <img id="det_foto_preview" src="https://placehold.co/200x250/eef2f5/94a3b8?text=Foto" class="img-fluid rounded mb-3" alt="Foto Petugas">
                        </div>
                        <div class="col-md-8">
                             <ul class="list-group">
                                <li class="list-group-item"><strong>Nama:</strong> <span id="det_nama_petugas"></span></li>
                                <li class="list-group-item"><strong>Kategori:</strong> <span id="det_kategori"></span></li>
                                <li class="list-group-item"><strong>Status:</strong> <span id="det_status"></span></li>
                                <li class="list-group-item"><strong>NIK:</strong> <span id="det_nik"></span></li>
                                <li class="list-group-item"><strong>Alamat:</strong> <span id="det_alamat"></span></li>
                                <li class="list-group-item"><strong>No HP:</strong> <span id="det_no_hp"></span></li>
                                <li class="list-group-item"><strong>Email:</strong> <span id="det_email"></span></li>
                                <li class="list-group-item"><strong>Pendidikan:</strong> <span id="det_pendidikan"></span></li>
                                <li class="list-group-item"><strong>Posisi:</strong> <span id="det_posisi"></span></li>
                                <li class="list-group-item"><strong>TMT Pengangkatan:</strong> <span id="det_tmt_pengangkatan"></span></li>
                                <li class="list-group-item"><strong>No SK:</strong> <span id="det_no_sk"></span></li>
                                <li class="list-group-item"><strong>Tanggal SK:</strong> <span id="det_tgl_sk"></span></li>
                                <li class="list-group-item"><strong>Pejabat Pengangkatan:</strong> <span id="det_pejabat_pengangkatan"></span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let modalCreate, modalEdit, modalDetail;
        const updateUrlTemplate = "{{ route('master.petugas.update', ['petugas' => '__ID__']) }}";
        const allFields = [
            'id_petugas', 'nama_petugas', 'kategori', 'nik', 'alamat', 'no_hp', 'posisi',
            'email', 'pendidikan', 'tmt_pengangkatan', 'no_sk', 'tgl_sk',
            'pejabat_pengangkatan', 'foto', 'status'
        ];

        document.addEventListener('DOMContentLoaded', function () {
            modalCreate = new bootstrap.Modal(document.getElementById('modalCreate'));
            modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
            modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));

            document.getElementById('selectAll').addEventListener('change', function () {
                document.querySelectorAll('.rowCheck').forEach(chk => chk.checked = this.checked);
            });
        });

        function openCreate() { modalCreate.show(); }

        function openEdit(btn) {
            const id = btn.dataset.id_petugas;
            const form = document.getElementById('formEdit');
            form.action = updateUrlTemplate.replace('__ID__', id);

            allFields.forEach(k => {
                const el = document.getElementById('edit_' + k);
                if (el) {
                    el.value = btn.dataset[k] || '';
                }
            });

            modalEdit.show();
        }

        function openDetail(btn) {
            allFields.forEach(k => {
                const el = document.getElementById('det_' + k);
                if (el) {
                    let value = btn.dataset[k] || '-';
                     // Format dates
                    if ((k === 'tgl_sk' || k === 'tmt_pengangkatan') && value !== '-') {
                        const date = new Date(value);
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const year = date.getFullYear();
                        value = `${day}/${month}/${year}`;
                    }
                    el.textContent = value;
                }
            });
             const fotoPreview = document.getElementById('det_foto_preview');
            const fotoUrl = btn.dataset.foto;
            fotoPreview.src = fotoUrl ? fotoUrl : 'https://placehold.co/200x250/eef2f5/94a3b8?text=Foto';


            modalDetail.show();
        }

        function exportData() {
            window.location.href = "{{ route('master.petugas.export') }}";
        }

        function importData() {
            alert('Fitur import akan membuka form upload file Excel nanti.');
        }

        function bulkDelete() {
            const checked = [...document.querySelectorAll('.rowCheck:checked')].map(chk => chk.value);
            if (checked.length === 0) return alert('Pilih minimal satu data!');
            if (!confirm('Yakin ingin menghapus data terpilih?')) return;

            fetch("{{ route('master.petugas.bulkDelete') }}", {
                method: "POST",
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}", 'Content-Type': 'application/json' },
                body: JSON.stringify({ ids: checked })
            }).then(() => location.reload());
        }
    </script>
@endpush

