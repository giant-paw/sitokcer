@extends('layouts.app')

@section('title', 'Distribusi Tahunan - Sitokcer')

@section('header-title', 'List Target Kegiatan Tahunan Tim Distribusi')

@section('content')
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

                <ul class="nav nav-pills mb-3 d-flex flex-wrap gap-8" >
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

                <form action="{{ route('tim-distribusi.tahunan.index') }}" method="GET" class="mb-4">
                    <div class="row g-2 align-items-center">
                        @if(request('kegiatan'))
                            <input type="hidden" name="kegiatan" value="{{ request('kegiatan') }}">
                        @endif
                        <div class="col-md-9 col-12">
                            <input type="text" class="form-control" placeholder="Cari berdasarkan Responden, Pencacah, atau Pengawas..." name="search" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 col-12">
                            <button class="btn btn-primary w-100" type="submit">
                                <i class="bi bi-search"></i> Cari
                            </button>
                        </div>
                    </div>
                </form>


                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
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
                                    <td>
                                        <input type="checkbox" class="form-check-input row-checkbox">
                                    </td>
                                    <td>{{ $item->nama_kegiatan }}</td>
                                    <td>{{ $item->blok_sensus_responden }}</td>
                                    <td>{{ $item->pencacah }}</td>
                                    <td>{{ $item->pengawas }}</td>
                                    <td>{{ $item->target_penyelesaian }}</td>
                                    <td><span class="badge {{ $item->flag_progress == 'Selesai' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $item->flag_progress }}</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
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

    <!-- Modal Tambah Data -->
    <div class="modal fade" id="tambahDataModal" tabindex="-1" aria-labelledby="tambahDataModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form action="{{ route('tim-distribusi.tahunan.store') }}" method="POST">
          @csrf
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="tambahDataModalLabel">Tambah Data Baru</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
                <input type="text" class="form-control" id="nama_kegiatan" name="nama_kegiatan" required>
              </div>
              <div class="mb-3">
                <label for="blok_sensus_responden" class="form-label">Blok Sensus/Responden</label>
                <input type="text" class="form-control" id="blok_sensus_responden" name="blok_sensus_responden" required>
              </div>
              <div class="mb-3">
                <label for="pencacah" class="form-label">Pencacah</label>
                <input type="text" class="form-control" id="pencacah" name="pencacah" required>
              </div>
              <div class="mb-3">
                <label for="pengawas" class="form-label">Pengawas</label>
                <input type="text" class="form-control" id="pengawas" name="pengawas" required>
              </div>
              <div class="mb-3">
                <label for="target_penyelesaian" class="form-label">Tanggal Target Penyelesaian</label>
                <input type="text" class="form-control" id="target_penyelesaian" name="target_penyelesaian" placeholder="dd/mm/yyyy" required>
              </div>
              <div class="mb-3">
                <label for="flag_progress" class="form-label">Flag Progress</label>
                <select class="form-select" id="flag_progress" name="flag_progress" required>
                  <option value="Belum Selesai">Belum Selesai</option>
                  <option value="Selesai">Selesai</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </div>
        </form>
      </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('selectAll');
            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    // Ambil ulang semua row-checkbox setiap kali selectAll berubah
                    document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = selectAll.checked);
                });
            }
        });
    </script>
@endpush