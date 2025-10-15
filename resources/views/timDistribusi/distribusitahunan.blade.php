<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Target Kegiatan Tahunan Tim Distribusi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>

    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">LIST TARGET KEGIATAN TAHUNAN TIM DISTRIBUSI</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahDataModal">
                        <i class="bi bi-plus-circle"></i> Tambah baru
                    </button>
                    <button type="button" class="btn btn-secondary"><i class="bi bi-upload"></i> Import</button>
                    <button type="button" class="btn btn-success"><i class="bi bi-download"></i> Ekspor hasil</button>
                    <button type="button" class="btn btn-danger"><i class="bi bi-trash"></i> Hapus</button>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Terjadi Kesalahan!</strong> Mohon periksa kembali isian form Anda.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <ul class="nav nav-pills mb-3">
                    <li class="nav-item">
                        <a class="nav-link {{ request('kegiatan') == '' ? 'active' : '' }}" href="{{ route('tim-distribusi.tahunan.index') }}">All data</a>
                    </li>
                    @foreach($kegiatanCounts as $kegiatan)
                        <li class="nav-item">
                            <a class="nav-link {{ request('kegiatan') == $kegiatan->nama_kegiatan ? 'active' : '' }}" href="{{ route('tim-distribusi.tahunan.index', ['kegiatan' => $kegiatan->nama_kegiatan]) }}">
                                {{ $kegiatan->nama_kegiatan }} <span class="badge bg-secondary">{{ $kegiatan->total }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <form action="{{ route('tim-distribusi.tahunan.index') }}" method="GET">
                    <div class="input-group mb-3">
                        @if(request('kegiatan'))
                            <input type="hidden" name="kegiatan" value="{{ request('kegiatan') }}">
                        @endif
                        <input type="text" class="form-control" placeholder="Cari berdasarkan Responden, Pencacah, atau Pengawas..." name="search" value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><input type="checkbox" class="form-check-input"></th>
                                <th scope="col">Nama Kegiatan</th>
                                <th scope="col">Blok Sensus/Responden</th>
                                <th scope="col">Pencacah</th>
                                <th scope="col">Pengawas</th>
                                <th scope="col">Tanggal Target Penyelesaian</th>
                                <th scope="col">Flag Progress</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listData as $item)
                                <tr>
                                    <td><input type="checkbox" class="form-check-input"></td>
                                    <td>{{ $item->nama_kegiatan }}</td>
                                    <td>{{ $item->blok_sensus_responden }}</td>
                                    <td>{{ $item->pencacah }}</td>
                                    <td>{{ $item->pengawas }}</td>
                                    <td>{{ $item->target_penyelesaian }}</td>
                                    <td><span class="badge {{ $item->flag_progress == 'Selesai' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $item->flag_progress }}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning"><i class="bi bi-pencil-square"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="text-muted">
                    Displaying {{ $listData->firstItem() ?? 0 }} - {{ $listData->lastItem() ?? 0 }} of {{ $listData->total() }}
                </div>
                <div>
                    {{ $listData->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tambahDataModalLabel">Distribusi Tahunan, Tambah baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('tim-distribusi.tahunan.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama_kegiatan') is-invalid @enderror" id="nama_kegiatan" name="nama_kegiatan" value="{{ old('nama_kegiatan') }}" required>
                            @error('nama_kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="blok_sensus_responden" class="form-label">Blok Sensus/Responden <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('blok_sensus_responden') is-invalid @enderror" id="blok_sensus_responden" name="blok_sensus_responden" value="{{ old('blok_sensus_responden') }}" required>
                            @error('blok_sensus_responden')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="pencacah" class="form-label">Pencacah <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pencacah') is-invalid @enderror" id="pencacah" name="pencacah" value="{{ old('pencacah') }}" required>
                            @error('pencacah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="pengawas" class="form-label">Pengawas <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pengawas') is-invalid @enderror" id="pengawas" name="pengawas" value="{{ old('pengawas') }}" required>
                            @error('pengawas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="target_penyelesaian" class="form-label">Tanggal Target Penyelesaian (dd/mm/yyyy) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('target_penyelesaian') is-invalid @enderror" id="target_penyelesaian" name="target_penyelesaian" placeholder="Contoh: 31/12/2025" value="{{ old('target_penyelesaian') }}" required>
                             @error('target_penyelesaian')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="flag_progress" class="form-label">Flag Progress <span class="text-danger">*</span></label>
                            <select class="form-select @error('flag_progress') is-invalid @enderror" id="flag_progress" name="flag_progress" required>
                                <option value="">Silahkan pilih</option>
                                <option value="Belum Mulai" {{ old('flag_progress') == 'Belum Mulai' ? 'selected' : '' }}>Belum Mulai</option>
                                <option value="Proses" {{ old('flag_progress') == 'Proses' ? 'selected' : '' }}>Proses</option>
                                <option value="Selesai" {{ old('flag_progress') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                             @error('flag_progress')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Script untuk menampilkan modal lagi jika ada error validasi dari server
        @if ($errors->any())
            const errorModal = new bootstrap.Modal(document.getElementById('tambahDataModal'));
            errorModal.show();
        @endif
    </script>
</body>
</html>