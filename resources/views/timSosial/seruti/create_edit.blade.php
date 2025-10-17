@extends('layouts.app')
@section('title', $mode === 'edit' ? 'Edit Seruti' : 'Tambah Seruti')
@section('header-title', 'Seruti - ' . ($mode === 'edit' ? 'Edit' : 'Tambah'))

@section('content')
    <div class="p-4 md:p-6">
        <div class="bg-white shadow rounded-xl p-5 max-w-5xl mx-auto">

            {{-- Flash & Errors --}}
            @if (session('ok'))
                <div class="alert alert-success mb-3">{{ session('ok') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post"
                action="{{ $mode === 'edit' ? route('sosial.seruti.update', $seruti) : route('sosial.seruti.store') }}">
                @csrf
                @if ($mode === 'edit')
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nama Kegiatan --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">
                            Nama Kegiatan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_kegiatan"
                            value="{{ old('nama_kegiatan', $mode === 'edit' ? $seruti->nama_kegiatan : 'Seruti-' . ($tw ?? 'TW1')) }}"
                            class="w-full border rounded-lg px-3 py-2" required>
                        <p class="text-xs text-gray-500 mt-1">Contoh: Seruti-TW1, Seruti-TW2, Seruti-TW3, Seruti-TW4.</p>
                    </div>

                    {{-- BS / Responden --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Blok Sensus/Responden</label>
                        <input type="text" name="BS_Responden"
                            value="{{ old('BS_Responden', $seruti->BS_Responden ?? '') }}"
                            class="w-full border rounded-lg px-3 py-2">
                    </div>

                    {{-- Pencacah --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Pencacah <span class="text-red-500">*</span></label>
                        <input type="text" name="pencacah" value="{{ old('pencacah', $seruti->pencacah ?? '') }}"
                            class="w-full border rounded-lg px-3 py-2" required>
                    </div>

                    {{-- Pengawas --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Pengawas <span class="text-red-500">*</span></label>
                        <input type="text" name="pengawas" value="{{ old('pengawas', $seruti->pengawas ?? '') }}"
                            class="w-full border rounded-lg px-3 py-2" required>
                    </div>

                    {{-- Target Penyelesaian (date) --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Target Penyelesaian</label>
                        <input type="date" name="target_penyelesaian"
                            value="{{ old('target_penyelesaian', $targetYmd ?? '') }}"
                            class="w-full border rounded-lg px-3 py-2">
                    </div>

                    {{-- Flag Progress --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Flag Progress <span
                                class="text-red-500">*</span></label>
                        @php $sel = old('flag_progress', $seruti->flag_progress ?? 'Belum Mulai'); @endphp
                        <select name="flag_progress" class="w-full border rounded-lg px-3 py-2" required>
                            @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                <option value="{{ $opt }}" {{ $sel === $opt ? 'selected' : '' }}>
                                    {{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal Pengumpulan (datetime) --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Tanggal Pengumpulan</label>
                        <input type="datetime-local" name="tanggal_pengumpulan"
                            value="{{ old('tanggal_pengumpulan', $kumpulLocal ?? '') }}"
                            class="w-full border rounded-lg px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Boleh dikosongkan.</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white">
                        {{ $mode === 'edit' ? 'Simpan Perubahan' : 'Simpan' }}
                    </button>
                    <a href="{{ route('sosial.seruti.index', ['tw' => $tw ?? 'TW1']) }}"
                        class="px-4 py-2 rounded-lg border">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
