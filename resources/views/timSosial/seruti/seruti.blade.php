@extends('layouts.app') {{-- ganti dengan nama layout utama kamu --}}

@section('title', 'Seruti')
@section('header-title', 'List Target Seruti (Kegiatan Triwulanan)')

@section('content')
    <div class="p-4 md:p-6">

        {{-- Tabs TW1..TW4 --}}
        @php
            $tabs = ['TW1', 'TW2', 'TW3', 'TW4'];
        @endphp
        <div class="flex flex-wrap items-center gap-2 mb-4">
            @foreach ($tabs as $t)
                <a href="{{ route('sosial.seruti.index', ['tw' => $t, 'q' => request('q')]) }}"
                    class="px-3 py-1.5 rounded-lg border text-sm
                {{ $tw === $t ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                    Seruti-{{ $t }}
                </a>
            @endforeach
        </div>

        {{-- Toolbar: Tambah, Import, Ekspor (opsional/hanya tombol UI) + Search --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <button class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm shadow hover:opacity-90">Tambah
                    baru</button>
                <button class="px-3 py-2 bg-sky-600 text-white rounded-lg text-sm shadow hover:opacity-90">Import</button>
                <button class="px-3 py-2 bg-gray-800 text-white rounded-lg text-sm shadow hover:opacity-90">Ekspor
                    hasil</button>
                <button class="px-3 py-2 bg-rose-600 text-white rounded-lg text-sm shadow hover:opacity-90">Hapus</button>
            </div>

            <form action="{{ route('sosial.seruti.index') }}" method="GET" class="w-full md:w-auto">
                <input type="hidden" name="tw" value="{{ $tw }}">
                <div class="relative">
                    <input type="text" name="q" value="{{ $q }}" placeholder="Search"
                        class="w-full md:w-72 pl-3 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button class="absolute right-0 top-0 h-full px-3" aria-label="Cari">
                        🔍
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-3 py-3 w-10"><input type="checkbox"></th>
                            <th class="px-3 py-3">Nama Kegiatan</th>
                            <th class="px-3 py-3">Blok Sensus/Responden</th>
                            <th class="px-3 py-3">Pencacah</th>
                            <th class="px-3 py-3">Pengawas</th>
                            <th class="px-3 py-3">Target Penyelesaian</th>
                            <th class="px-3 py-3">Flag Progress</th>
                            <th class="px-3 py-3">Tanggal Pengumpulan</th>
                            <th class="px-3 py-3 w-16 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($rows as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3"><input type="checkbox"></td>
                                <td class="px-3 py-3 text-blue-700 font-medium">{{ $row->nama_kegiatan }}</td>
                                <td class="px-3 py-3">{{ $row->BS_Responden }}</td>
                                <td class="px-3 py-3">{{ $row->pencacah }}</td>
                                <td class="px-3 py-3">{{ $row->pengawas }}</td>
                                <td class="px-3 py-3">
                                    @if ($row->target_penyelesaian)
                                        {{ \Carbon\Carbon::parse($row->target_penyelesaian)->format('d/m/Y') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    @php
                                        $done = strtolower($row->flag_progress) === 'selesai';
                                    @endphp
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $done ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $row->flag_progress ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($row->tanggal_pengumpulan)
                                        {{ \Carbon\Carbon::parse($row->tanggal_pengumpulan)->format('Y-m-d H:i') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <button class="px-2 py-1 rounded bg-lime-600 text-white text-xs">Edit</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-6 text-center text-gray-500">
                                    Data tidak ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-4 py-3 border-t bg-gray-50 flex items-center justify-between text-sm text-gray-600">
                <div>
                    Menampilkan
                    <span class="font-medium">{{ $rows->firstItem() ?? 0 }}</span>
                    –
                    <span class="font-medium">{{ $rows->lastItem() ?? 0 }}</span>
                    dari
                    <span class="font-medium">{{ $rows->total() }}</span>
                </div>
                <div class="flex gap-1">
                    {{ $rows->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
