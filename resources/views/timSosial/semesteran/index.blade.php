@extends('layouts.app')

@section('title', 'Kegiatan Semesteran')
@section('header-title', 'List Target Kegiatan Semesteran')

@section('content')
    <div class="container-fluid px-0">

        {{-- Tabs kategori --}}
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ !$kategori ? 'active' : '' }}"
                    href="{{ route('sosial.semesteran.index', ['kategori' => '']) }}">Semua</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kategori === 'Sakernas' ? 'active' : '' }}"
                    href="{{ route('sosial.semesteran.index', ['kategori' => 'Sakernas']) }}">Sakernas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $kategori === 'Susenas' ? 'active' : '' }}"
                    href="{{ route('sosial.semesteran.index', ['kategori' => 'Susenas']) }}">Susenas</a>
            </li>
        </ul>

        {{-- Toolbar + Search --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreate">Tambah Baru</button>
            <form method="get" action="{{ route('sosial.semesteran.index') }}">
                <div class="input-group" style="max-width: 360px;">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search"
                        class="form-control">
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
                                <th>#</th>
                                <th>Nama Kegiatan</th>
                                <th>Blok Sensus/Responden</th>
                                <th>Pencacah</th>
                                <th>Pengawas</th>
                                <th>Target Penyelesaian</th>
                                <th>Flag Progress</th>
                                <th>Tanggal Pengumpulan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->nama_kegiatan }}</td>
                                    <td>{{ $item->BS_Responden }}</td>
                                    <td>{{ $item->pencacah }}</td>
                                    <td>{{ $item->pengawas }}</td>
                                    <td>{{ $item->target_penyelesaian_formatted }}</td>
                                    <td>{{ $item->flag_progress }}</td>
                                    <td>{{ $item->tanggal_pengumpulan_formatted }}</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="openEdit('{{ $item->id_sosial_semesteran }}')">Edit</button>
                                        <form action="{{ route('sosial.semesteran.destroy', $item) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $items->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="modalCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="post" action="{{ route('sosial.semesteran.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Data Kegiatan Semesteran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}"
                                class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Blok Sensus/Responden</label>
                            <input type="text" name="BS_Responden" value="{{ old('BS_Responden') }}"
                                class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pencacah</label>
                            <input type="text" name="pencacah" value="{{ old('pencacah') }}" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pengawas</label>
                            <input type="text" name="pengawas" value="{{ old('pengawas') }}" class="form-control"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Target Penyelesaian</label>
                            <input type="date" name="target_penyelesaian" value="{{ old('target_penyelesaian') }}"
                                class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Flag Progress</label>
                            <select name="flag_progress" class="form-select" required>
                                <option value="Belum Mulai" {{ old('flag_progress') == 'Belum Mulai' ? 'selected' : '' }}>
                                    Belum Mulai</option>
                                <option value="Proses" {{ old('flag_progress') == 'Proses' ? 'selected' : '' }}>Proses
                                </option>
                                <option value="Selesai" {{ old('flag_progress') == 'Selesai' ? 'selected' : '' }}>Selesai
                                </option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Pengumpulan</label>
                            <input type="date" name="tanggal_pengumpulan" value="{{ old('tanggal_pengumpulan') }}"
                                class="form-control">
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

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formEdit" method="post" action="#">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Data Kegiatan Semesteran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" id="edit_nama" name="nama_kegiatan"
                                value="{{ old('nama_kegiatan') }}" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Blok Sensus/Responden</label>
                            <input type="text" id="edit_bsres" name="BS_Responden" value="{{ old('BS_Responden') }}"
                                class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pencacah</label>
                            <input type="text" id="edit_pencacah" name="pencacah" value="{{ old('pencacah') }}"
                                class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Pengawas</label>
                            <input type="text" id="edit_pengawas" name="pengawas" value="{{ old('pengawas') }}"
                                class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Target Penyelesaian</label>
                            <input type="date" id="edit_target" name="target_penyelesaian"
                                value="{{ old('target_penyelesaian') }}" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Flag Progress</label>
                            <select id="edit_flag" name="flag_progress" class="form-select" required>
                                <option value="Belum Mulai" {{ old('flag_progress') == 'Belum Mulai' ? 'selected' : '' }}>
                                    Belum Mulai</option>
                                <option value="Proses" {{ old('flag_progress') == 'Proses' ? 'selected' : '' }}>Proses
                                </option>
                                <option value="Selesai" {{ old('flag_progress') == 'Selesai' ? 'selected' : '' }}>Selesai
                                </option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Pengumpulan</label>
                            <input type="date" id="edit_tanggal" name="tanggal_pengumpulan"
                                value="{{ old('tanggal_pengumpulan') }}" class="form-control">
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
        function openEdit(id) {
            const modal = new bootstrap.Modal(document.getElementById('modalEdit'));
            modal.show();

            // Set form action
            document.getElementById('formEdit').action = '/sosial/semesteran/' + id;

            // Set the input values dynamically
            // Replace with actual values from your database row
            // These values should be passed to the script on page load
            document.getElementById('edit_nama').value = "Sample Nama Kegiatan";
            document.getElementById('edit_bsres').value = "Sample Blok";
            document.getElementById('edit_pencacah').value = "Sample Pencacah";
            document.getElementById('edit_pengawas').value = "Sample Pengawas";
            document.getElementById('edit_target').value = "2025-02-28"; // Example date format
            document.getElementById('edit_flag').value = "Selesai"; // Example flag
            document.getElementById('edit_tanggal').value = "2025-02-28"; // Example date format
        }
    </script>
@endpush
