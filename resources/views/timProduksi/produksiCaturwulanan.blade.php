@extends('layouts.app')

@section('title', strtoupper($jenisKegiatan) . ' - Sitokcer')
@section('header-title', 'List Target Survei ' . ucwords($jenisKegiatan))

@push('styles')
    <style>
        .autocomplete-container {
            position: relative;
        }

        .autocomplete-suggestions {
            position: absolute;
            border: 1px solid #d1d3e2;
            border-top: none;
            z-index: 1056;
            width: 100%;
            background-color: #fff;
            max-height: 200px;
            overflow-y: auto;
        }

        .autocomplete-suggestion-item {
            padding: 8px 12px;
            cursor: pointer;
        }

        .autocomplete-suggestion-item:hover,
        .autocomplete-suggestion-item.active {
            background-color: #0d6efd;
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">LIST TARGET SURVEI {{ strtoupper($jenisKegiatan) }}</h4>
            </div>
            <div class="card-body">
                <div class="mb-4 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#tambahDataModal"><i class="bi bi-plus-circle"></i> Tambah Baru</button>
                        <button type="button" class="btn btn-secondary"><i class="bi bi-upload"></i> Import</button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#exportModal"><i class="bi bi-download"></i> Ekspor Hasil</button>
                        <button type="button" class="btn btn-danger" data-bs-target="#deleteDataModal" id="bulkDeleteBtn"
                            disabled><i class="bi bi-trash"></i> Hapus Data Terpilih</button>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <div>
                            <label for="perPageSelect" class="form-label me-2 mb-0">Display:</label>
                            <select class="form-select form-select-sm" id="perPageSelect" style="width: auto;">
                                @php $options = [10, 20, 30, 50, 100, 500, 'all']; @endphp
                                @foreach ($options as $option)
                                    <option value="{{ $option }}"
                                        {{ request('per_page', 20) == $option ? 'selected' : '' }}>
                                        {{ $option == 'all' ? 'All' : $option }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex align-items-center">
                            <label for="tahunSelect" class="form-label me-2 mb-0">Tahun:</label>
                            <select class="form-select form-select-sm" id="tahunSelect" style="width: auto;">
                                @foreach ($availableTahun ?? [date('Y')] as $tahun)
                                    <option value="{{ $tahun }}"
                                        {{ ($selectedTahun ?? date('Y')) == $tahun ? 'selected' : '' }}>
                                        {{ $tahun }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert"> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if ($errors->any() && !session('error_modal'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert"> <strong>Error!</strong>
                        Periksa form. <button type="button" class="btn-close" data-bs-dismiss="alert"></button> </div>
                @endif

                <ul class="nav nav-pills mb-3 d-flex flex-wrap gap-8">
                    <li class="nav-item">
                        <a class="nav-link {{ request('kegiatan') == '' ? 'active' : '' }}"
                            href="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => $jenisKegiatan]) }}">All
                            data</a>
                    </li>
                    @foreach ($kegiatanCounts ?? [] as $kegiatan)
                        <li class="nav-item">
                            <a class="nav-link {{ request('kegiatan') == $kegiatan->nama_kegiatan ? 'active' : '' }}"
                                href="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => $jenisKegiatan, 'kegiatan' => $kegiatan->nama_kegiatan, 'tahun' => $selectedTahun ?? date('Y')]) }}">
                                {{ $kegiatan->nama_kegiatan }} <span
                                    class="badge bg-secondary">{{ $kegiatan->total }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <form action="{{ route('tim-produksi.caturwulanan.index', ['jenisKegiatan' => $jenisKegiatan]) }}"
                    method="GET" class="mb-4">
                    <div class="row g-2 align-items-center">
                        <input type="hidden" name="tahun" value="{{ $selectedTahun ?? date('Y') }}">
                        @if (request('kegiatan'))
                            <input type="hidden" name="kegiatan" value="{{ request('kegiatan') }}">
                        @endif
                        <div class="col-md-9 col-12">
                            <input type="text" class="form-control" placeholder="Cari..." name="search"
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3 col-12">
                            <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i> Cari</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th scope="col"><input type="checkbox" class="form-check-input" id="selectAll"></th>
                                <th scope="col">Nama Kegiatan</th>
                                <th scope="col">Blok Sensus/Responden</th>
                                <th scope="col">Pencacah</th>
                                <th scope="col">Pengawas</th>
                                <th scope="col">Target Penyelesaian</th>
                                <th scope="col">Flag Progress</th>
                                <th scope="col">Tanggal Pengumpulan</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Pastikan $listData dikirim dari Controller --}}
                            @forelse ($listData ?? [] as $item)
                                <tr>
                                    <td><input type="checkbox" class="form-check-input row-checkbox"
                                            value="{{ $item->id_produksi_caturwulanan }}"></td>
                                    <td>{{ $item->nama_kegiatan }}</td>
                                    <td>{{ $item->BS_Responden }}</td>
                                    <td>{{ $item->pencacah }}</td>
                                    <td>{{ $item->pengawas }}</td>
                                    {{-- Format Tanggal dari Model Casting --}}
                                    <td>{{ $item->target_penyelesaian ? $item->target_penyelesaian->format('d/m/Y') : '-' }}
                                    </td>
                                    <td><span
                                            class="badge {{ $item->flag_progress == 'Selesai' ? 'bg-success' : 'bg-warning text-dark' }}">{{ $item->flag_progress }}</span>
                                    </td>
                                    {{-- Format Tanggal dari Model Casting --}}
                                    <td>{{ $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="d-flex gap-2">
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#editDataModal"
                                            onclick="editData({{ $item->id_produksi_caturwulanan }})"><i
                                                class="bi bi-pencil-square"></i></button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#deleteDataModal"
                                            onclick="deleteData({{ $item->id_produksi_caturwulanan }})"><i
                                                class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <div class="text-muted small"> Displaying {{ $listData->firstItem() ?? 0 }} -
                    {{ $listData->lastItem() ?? 0 }} of {{ $listData->total() ?? 0 }} </div>
                <div> {{ $listData->links() ?? '' }} </div>
            </div>
        </div>
    </div>

    <!-- Modal Ekspor -->
    <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('tim-produksi.caturwulanan.export', ['jenisKegiatan' => $jenisKegiatan]) }}"
                    method="GET">
                    @csrf
                    <input type="hidden" name="kegiatan" value="{{ request('kegiatan') }}">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="tahun" value="{{ request('tahun', date('Y')) }}">
                    <input type="hidden" name="page" value="{{ request('page', 1) }}">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 20) }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="exportModalLabel">Ekspor Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Jangkauan Data -->
                        <div class="mb-3">
                            <label for="dataRange" class="form-label">Jangkauan Data</label>
                            <select class="form-select" id="dataRange" name="dataRange" required>
                                <option value="all">Semua Catatan</option>
                                <option value="current_page">Hanya Halaman Terkini</option>
                            </select>
                        </div>

                        <!-- Format Data -->
                        <div class="mb-3">
                            <label for="dataFormat" class="form-label">Format Data</label>
                            <select class="form-select" id="dataFormat" name="dataFormat" required>
                                <option value="formatted_values">Formatted Values</option>
                                <option value="raw_values">Raw Values</option>
                            </select>
                        </div>

                        <!-- Format Keluaran -->
                        <div class="mb-3">
                            <label for="exportFormat" class="form-label">Format Keluaran</label>
                            <select class="form-select" id="exportFormat" name="exportFormat" required>
                                <option value="excel">Excel 2007</option>
                                <option value="csv">CSV (Nilai Terpisah Koma)</option>
                                <option value="word">Word (.docx)</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Ekspor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function initAutocomplete(inputId, suggestionsId, searchUrl) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const suggestionsContainer = document.getElementById(suggestionsId);
            let debounceTimer;
            let activeSuggestionIndex = -1;

            input.addEventListener('input', function() {
                const query = this.value;
                clearTimeout(debounceTimer);
                if (query.length < 1) {
                    if (suggestionsContainer) suggestionsContainer.innerHTML = '';
                    activeSuggestionIndex = -1;
                    return;
                }
                debounceTimer = setTimeout(() => {
                    const finalSearchUrl = `${searchUrl}?query=${query}`;
                    fetch(finalSearchUrl).then(response => response.json()).then(data => {
                        suggestionsContainer.innerHTML = '';
                        activeSuggestionIndex = -1;
                        data.forEach((item, index) => {
                            const div = document.createElement('div');
                            div.textContent = item;
                            div.classList.add('autocomplete-suggestion-item');
                            div.onclick = () => {
                                input.value = item;
                                suggestionsContainer.innerHTML = '';
                            };
                            div.onmouseover = () => {
                                document.querySelectorAll(
                                    `#${suggestionsId} .autocomplete-suggestion-item`
                                ).forEach(el => el.classList.remove('active'));
                                div.classList.add('active');
                                activeSuggestionIndex = index;
                            };
                            suggestionsContainer.appendChild(div);
                        });
                    }).catch(error => console.error('Autocomplete error:', error));
                }, 300);
            });

            input.addEventListener('keydown', function(e) {
                const suggestions = suggestionsContainer.querySelectorAll('.autocomplete-suggestion-item');
                if (suggestions.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeSuggestionIndex = (activeSuggestionIndex + 1) % suggestions.length;
                    updateActiveSuggestion(suggestions, activeSuggestionIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeSuggestionIndex = (activeSuggestionIndex - 1 + suggestions.length) % suggestions.length;
                    updateActiveSuggestion(suggestions, activeSuggestionIndex);
                } else if (e.key === 'Enter') {
                    if (activeSuggestionIndex > -1) {
                        e.preventDefault();
                        input.value = suggestions[activeSuggestionIndex].textContent;
                        suggestionsContainer.innerHTML = '';
                        activeSuggestionIndex = -1;
                    }
                } else if (e.key === 'Escape') {
                    suggestionsContainer.innerHTML = '';
                    activeSuggestionIndex = -1;
                }
            });

            function updateActiveSuggestion(suggestions, index) {
                suggestions.forEach(el => el.classList.remove('active'));
                if (suggestions[index]) suggestions[index].classList.add('active');
            }

            document.addEventListener('click', (e) => {
                if (e.target.id !== inputId && suggestionsContainer) {
                    suggestionsContainer.innerHTML = '';
                    activeSuggestionIndex = -1;
                }
            });
        }
    </script>
@endpush
