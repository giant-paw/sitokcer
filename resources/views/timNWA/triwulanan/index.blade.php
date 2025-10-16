@extends('layouts.app')

@section('title', $prefix . ' (NWA Triwulanan)')
@section('header-title', 'List Target ' . $prefix . ' (NWA Triwulanan)')

@section('content')
    <div class="p-4 md:p-6">

        {{-- Tabs TW1..TW4 --}}
        @php $tabs = ['TW1','TW2','TW3','TW4']; @endphp
        <div class="flex flex-wrap items-center gap-2 mb-4">
            @foreach ($tabs as $t)
                <a href="{{ route('nwa.triwulanan.index', [$jenis, 'tw' => $t, 'q' => request('q')]) }}"
                    class="px-3 py-1.5 rounded-lg border text-sm
               {{ $tw === $t ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                    {{ $prefix }}-{{ $t }}
                </a>
            @endforeach
        </div>

        {{-- Toolbar + Search --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-2">
                <a href="javascript:void(0)" onclick="openCreate()"
                    class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm shadow hover:opacity-90">
                    Tambah baru
                </a>
            </div>

            <form method="get" class="w-full md:w-auto" action="{{ route('nwa.triwulanan.index', $jenis) }}">
                <input type="hidden" name="tw" value="{{ $tw }}">
                <div class="relative">
                    <input type="text" name="q" value="{{ $q }}" placeholder="search"
                        class="w-full md:w-72 pl-3 pr-10 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button class="absolute right-0 top-0 h-full px-3" aria-label="Cari">🔍</button>
                </div>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-3 py-3 w-10">#</th>
                            <th class="px-3 py-3">Nama Kegiatan</th>
                            <th class="px-3 py-3">Blok Sensus/Responden</th>
                            <th class="px-3 py-3">Pencacah</th>
                            <th class="px-3 py-3">Pengawas</th>
                            <th class="px-3 py-3">Target Penyelesaian</th>
                            <th class="px-3 py-3">Flag Progress</th>
                            <th class="px-3 py-3">Tanggal Pengumpulan</th>
                            <th class="px-3 py-3 w-44 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($rows as $i => $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3">{{ $rows->firstItem() + $i }}</td>
                                <td class="px-3 py-3 font-medium">{{ $row->nama_kegiatan }}</td>
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
                                    @php $done = strtolower($row->flag_progress) === 'selesai'; @endphp
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-semibold
                                    {{ $done ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                        {{ $row->flag_progress ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($row->tanggal_pengumpulan)
                                        {{ \Carbon\Carbon::parse($row->tanggal_pengumpulan)->format('d/m/Y') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2 justify-center">
                                        <button type="button" class="px-2 py-1 rounded bg-lime-600 text-white text-xs"
                                            onclick="openEdit(this)" data-id="{{ $row->id_nwa_triwulanan }}"
                                            data-nama="{{ $row->nama_kegiatan }}" data-bs="{{ $row->BS_Responden }}"
                                            data-pencacah="{{ $row->pencacah }}" data-pengawas="{{ $row->pengawas }}"
                                            data-target="{{ $row->target_penyelesaian ? \Carbon\Carbon::parse($row->target_penyelesaian)->format('Y-m-d') : '' }}"
                                            data-flag="{{ $row->flag_progress }}"
                                            data-kumpul="{{ $row->tanggal_pengumpulan ? \Carbon\Carbon::parse($row->tanggal_pengumpulan)->format('Y-m-d') : '' }}">
                                            Edit
                                        </button>

                                        <form action="{{ route('nwa.triwulanan.destroy', [$jenis, $row]) }}" method="post"
                                            onsubmit="return confirm('Hapus data ini?')">
                                            @csrf @method('DELETE')
                                            <button class="px-2 py-1 rounded bg-rose-600 text-white text-xs">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-3 py-6 text-center text-gray-500">Belum ada data.</td>
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

    {{-- ============== MODAL CREATE ============== --}}
    <dialog id="dlgCreate" class="rounded-xl w-[95vw] max-w-3xl p-0">
        <form method="post" action="{{ route('nwa.triwulanan.store', $jenis) }}">
            @csrf
            <input type="hidden" name="_mode" value="create">

            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold">Tambah Data {{ $prefix }} ({{ $tw }})</h3>
                <button type="button" onclick="closeCreate()" class="px-2 py-1 rounded border">Tutup</button>
            </div>

            <div class="p-4">
                @if ($errors->any() && old('_mode') === 'create')
                    <div class="mb-3 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                        <ul class="list-disc ms-5">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Nama Kegiatan <span
                                class="text-red-500">*</span></label>
                        <input type="text" name="nama_kegiatan" value="{{ old('nama_kegiatan', $prefix . '-' . $tw) }}"
                            class="w-full border rounded-lg px-3 py-2" required>
                        <p class="text-xs text-gray-500 mt-1">Contoh: {{ $prefix }}-{{ $tw }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Blok Sensus/Responden</label>
                        <input type="text" name="BS_Responden" value="{{ old('BS_Responden') }}"
                            class="w-full border rounded-lg px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Pencacah <span class="text-red-500">*</span></label>
                        <input type="text" name="pencacah" value="{{ old('pencacah') }}"
                            class="w-full border rounded-lg px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Pengawas <span class="text-red-500">*</span></label>
                        <input type="text" name="pengawas" value="{{ old('pengawas') }}"
                            class="w-full border rounded-lg px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Target Penyelesaian</label>
                        <input type="date" name="target_penyelesaian" value="{{ old('target_penyelesaian') }}"
                            class="w-full border rounded-lg px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Flag Progress <span
                                class="text-red-500">*</span></label>
                        @php $oldFlag = old('flag_progress', 'Belum Mulai'); @endphp
                        <select name="flag_progress" class="w-full border rounded-lg px-3 py-2" required>
                            @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                <option value="{{ $opt }}" {{ $oldFlag === $opt ? 'selected' : '' }}>
                                    {{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Tanggal Pengumpulan</label>
                        <input type="date" name="tanggal_pengumpulan" value="{{ old('tanggal_pengumpulan') }}"
                            class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
            </div>

            <div class="p-4 border-t flex items-center gap-2">
                <button class="px-4 py-2 rounded-lg bg-blue-600 text-white">Simpan</button>
                <button type="reset" class="px-4 py-2 rounded-lg border">Reset</button>
                <button type="button" onclick="closeCreate()" class="px-4 py-2 rounded-lg border ms-auto">Batal</button>
            </div>
        </form>
    </dialog>

    {{-- ============== MODAL EDIT ============== --}}
    <dialog id="dlgEdit" class="rounded-xl w-[95vw] max-w-3xl p-0">
        <form id="formEdit" method="post" action="#">
            @csrf
            @method('PUT')
            <input type="hidden" name="_mode" value="edit">
            <input type="hidden" name="_id" id="edit_id">

            <div class="p-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-semibold">Edit Data {{ $prefix }}</h3>
                <button type="button" onclick="closeEdit()" class="px-2 py-1 rounded border">Tutup</button>
            </div>

            <div class="p-4">
                @if ($errors->any() && old('_mode') === 'edit')
                    <div class="mb-3 rounded border border-red-300 bg-red-50 p-3 text-sm text-red-700">
                        <ul class="list-disc ms-5">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Nama Kegiatan <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="edit_nama" name="nama_kegiatan"
                            value="{{ old('_mode') === 'edit' ? old('nama_kegiatan') : '' }}"
                            class="w-full border rounded-lg px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Blok Sensus/Responden</label>
                        <input type="text" id="edit_bs" name="BS_Responden"
                            value="{{ old('_mode') === 'edit' ? old('BS_Responden') : '' }}"
                            class="w-full border rounded-lg px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Pencacah <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_pencacah" name="pencacah"
                            value="{{ old('_mode') === 'edit' ? old('pencacah') : '' }}"
                            class="w-full border rounded-lg px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Pengawas <span class="text-red-500">*</span></label>
                        <input type="text" id="edit_pengawas" name="pengawas"
                            value="{{ old('_mode') === 'edit' ? old('pengawas') : '' }}"
                            class="w-full border rounded-lg px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Target Penyelesaian</label>
                        <input type="date" id="edit_target" name="target_penyelesaian"
                            value="{{ old('_mode') === 'edit' ? old('target_penyelesaian') : '' }}"
                            class="w-full border rounded-lg px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Flag Progress <span
                                class="text-red-500">*</span></label>
                        @php $oldFlagE = old('_mode')==='edit' ? old('flag_progress') : null; @endphp
                        <select id="edit_flag" name="flag_progress" class="w-full border rounded-lg px-3 py-2" required>
                            @foreach (['Belum Mulai', 'Proses', 'Selesai'] as $opt)
                                <option value="{{ $opt }}" {{ $oldFlagE === $opt ? 'selected' : '' }}>
                                    {{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Tanggal Pengumpulan</label>
                        <input type="date" id="edit_kumpul" name="tanggal_pengumpulan"
                            value="{{ old('_mode') === 'edit' ? old('tanggal_pengumpulan') : '' }}"
                            class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
            </div>

            <div class="p-4 border-t flex items-center gap-2">
                <button class="px-4 py-2 rounded-lg bg-blue-600 text-white">Simpan</button>
                <button type="button" onclick="closeEdit()" class="px-4 py-2 rounded-lg border ms-auto">Batal</button>
            </div>
        </form>
    </dialog>

    {{-- Scripts --}}
    <script>
        const dlgCreate = document.getElementById('dlgCreate');
        const dlgEdit = document.getElementById('dlgEdit');
        const updateUrlTemplate = "{{ route('nwa.triwulanan.update', [$jenis, '__ID__']) }}";

        function openCreate() {
            if (dlgCreate?.showModal) dlgCreate.showModal();
            else dlgCreate?.setAttribute('open', 'open');
        }

        function closeCreate() {
            if (dlgCreate?.close) dlgCreate.close();
            else dlgCreate?.removeAttribute('open');
        }

        function openEdit(btn) {
            const id = btn.dataset.id;
            const nama = btn.dataset.nama || '';
            const bs = btn.dataset.bs || '';
            const penc = btn.dataset.pencacah || '';
            const peng = btn.dataset.pengawas || '';
            const target = btn.dataset.target || '';
            const flag = btn.dataset.flag || 'Belum Mulai';
            const kumpul = btn.dataset.kumpul || '';

            const form = document.getElementById('formEdit');
            form.action = updateUrlTemplate.replace('__ID__', id);

            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_bs').value = bs;
            document.getElementById('edit_pencacah').value = penc;
            document.getElementById('edit_pengawas').value = peng;
            document.getElementById('edit_target').value = target;
            document.getElementById('edit_flag').value = flag;
            document.getElementById('edit_kumpul').value = kumpul;

            if (dlgEdit?.showModal) dlgEdit.showModal();
            else dlgEdit?.setAttribute('open', 'open');
        }

        function closeEdit() {
            if (dlgEdit?.close) dlgEdit.close();
            else dlgEdit?.removeAttribute('open');
        }

        // Auto open modal saat validasi error
        @if ($errors->any())
            @if (old('_mode') === 'create')
                openCreate();
            @elseif (old('_mode') === 'edit')
                (function() {
                    const id = "{{ old('_id') }}";
                    if (id) {
                        const form = document.getElementById('formEdit');
                        form.action = updateUrlTemplate.replace('__ID__', id);
                    }
                    if (dlgEdit?.showModal) dlgEdit.showModal();
                    else dlgEdit?.setAttribute('open', 'open');
                })();
            @endif
        @endif
    </script>
@endsection
