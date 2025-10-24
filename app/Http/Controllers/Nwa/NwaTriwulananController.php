<?php

namespace App\Http\Controllers\Nwa;

use App\Http\Controllers\Controller;
use App\Models\Nwa\NwaTriwulanan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NwaTriwulananExport;

class NwaTriwulananController extends Controller
{
    public function index(Request $request, $jenisKegiatan)
    {
        // 1. Validasi jenis kegiatan (diambil dari NWA lama)
        $validJenis = ['sklnp', 'snaper', 'sktnp'];
        if (!in_array(strtolower($jenisKegiatan), $validJenis)) {
            abort(404);
        }

        // 2. Logika Filter Tahun (diambil dari template Produksi)
        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = NwaTriwulanan::query()
            // 3. Logika Query (diambil dari template Produksi)
            ->where('nama_kegiatan', 'Like', strtoupper($jenisKegiatan) . '%')
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // 4. Kueri Utama (diambil dari template Produksi)
        $query = NwaTriwulanan::query()
            ->where('nama_kegiatan', 'Like', strtoupper($jenisKegiatan) . '%')
            ->whereYear('created_at', $selectedTahun);

        if ($request->filled('kegiatan')) {
            $query->where('nama_kegiatan', $request->kegiatan);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('BS_Responden', 'like', "%{$searchTerm}%")
                    ->orWhere('pencacah', 'like', "%{$searchTerm}%")
                    ->orWhere('pengawas', 'like', "%{$searchTerm}%")
                    ->orWhere('nama_kegiatan', 'like', "%{$searchTerm}%");
            });
        }

        // 5. Logika Pagination (diambil dari template Produksi)
        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        // 6. Ganti Primary Key
        $listData = $query->latest('id_nwa_triwulanan')->paginate($perPage)->withQueryString();

        // 7. Logika Hitung Tab (diambil dari template Produksi)
        $kegiatanCounts = NwaTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', strtoupper($jenisKegiatan) . '%')
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();


        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        // 8. Ganti path view
        return view('timNWA.triwulanan.NWATriwulanan', compact(
            'listData',
            'kegiatanCounts',
            'jenisKegiatan',
            'masterKegiatanList',
            'availableTahun',
            'selectedTahun'
        ));
    }

    /**
     * Simpan data baru.
     */
    public function store(Request $request)
    {
        // 1. Validasi (diambil dari template Produksi)
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255', // Diubah jadi required
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => 'required|string', // Diubah jadi string simpel
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar di master kegiatan.',
            'pencacah.exists' => 'Nama pencacah tidak terdaftar di master petugas.',
            'pengawas.exists' => 'Nama pengawas tidak terdaftar di master petugas.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data yang diberikan tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error_modal', 'tambahDataModal');
        }

        $validatedData = $validator->validated();

        // 2. Logika Tahun (diambil dari template Produksi)
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) {
            }
        }

        // 3. Ganti Model
        NwaTriwulanan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }

        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    /**
     * Ambil data untuk modal edit.
     * MENGGUNAKAN $id MANUAL, BUKAN MODEL BINDING.
     */
    public function edit($id)
    {
        // 1. Cari data secara manual
        $nwa_triwulanan = NwaTriwulanan::findOrFail($id);

        $data = $nwa_triwulanan->toArray();

        // 2. Logika format tanggal (diambil dari template Produksi)
        $targetPenyelesaian = $nwa_triwulanan->target_penyelesaian;
        $tanggalPengumpulan = $nwa_triwulanan->tanggal_pengumpulan;

        $data['target_penyelesaian'] = $targetPenyelesaian
            ? Carbon::parse($targetPenyelesaian)->toDateString()
            : null;

        $data['tanggal_pengumpulan'] = $tanggalPengumpulan
            ? Carbon::parse($tanggalPengumpulan)->toDateString()
            : null;

        return response()->json($data);
    }

    /**
     * Update data yang ada.
     * MENGGUNAKAN $id MANUAL, BUKAN MODEL BINDING.
     */
    public function update(Request $request, $id)
    {
        // 1. Cari data secara manual
        $nwa_triwulanan = NwaTriwulanan::findOrFail($id);

        // 2. Validasi (diambil dari template Produksi)
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255', // Diubah jadi required
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => 'required|string', // Diubah jadi string simpel
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            // ... (sama seperti store)
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data yang diberikan tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error_modal', 'editDataModal')
                // 3. Ganti Primary Key
                ->with('edit_id', $nwa_triwulanan->id_nwa_triwulanan);
        }

        $validatedData = $validator->validated();

        // 4. Logika Tahun (diambil dari template Produksi)
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) {
            }
        }

        // 5. Update data
        $nwa_triwulanan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }

        return back()->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }

    /**
     * Hapus banyak data.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            // 1. Ganti tabel dan primary key
            'ids.*' => 'exists:nwa_triwulanan,id_nwa_triwulanan'
        ]);

        // 2. Ganti Model
        NwaTriwulanan::whereIn('id_nwa_triwulanan', $request->ids)->delete();

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Hapus satu data.
     * MENGGUNAKAN $id MANUAL, BUKAN MODEL BINDING.
     */
    public function destroy($id)
    {
        // 1. Cari data secara manual
        $nwa_triwulanan = NwaTriwulanan::findOrFail($id);

        // 2. Hapus data
        $nwa_triwulanan->delete();

        return back()->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    public function searchPetugas(Request $request)
    {
        // Perbaikan typo: [ + menjadi [
        $request->validate([
            'query' => 'nullable|string|max:100',
        ]);
        $query = $request->input('query', '');
        $data = MasterPetugas::query()
            ->where('nama_petugas', 'LIKE', "%{$query}%")
            ->limit(10)
            ->pluck('nama_petugas');
        return response()->json($data);
    }

    public function export(Request $request, $jenisKegiatan)
    {
        // Validasi jenis kegiatan
        $validJenis = ['sklnp', 'snaper', 'sktnp'];
        if (!in_array(strtolower($jenisKegiatan), $validJenis)) {
            abort(404);
        }
        // Validasi input
        $request->validate([
            'dataRange' => 'required|in:all,current_page',
            'dataFormat' => 'required|in:formatted_values,raw_values',
            'exportFormat' => 'required|in:excel,csv,word',
        ]);
        $dataRange = $request->input('dataRange', 'all');
        $dataFormat = $request->input('dataFormat');
        $exportFormat = $request->input('exportFormat');
        $kegiatan = $request->input('kegiatan');
        $search = $request->input('search');
        $tahun = $request->input('tahun', date('Y'));
        $currentPage = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        // Buat instance export class
        $exportClass = new NwaTriwulananExport(
            $dataRange,
            $dataFormat,
            $jenisKegiatan,
            $kegiatan,
            $search,
            $tahun,
            $currentPage,
            $perPage
        );
        // Generate nama file
        $fileName = 'NWA_Triwulanan_' . strtoupper($jenisKegiatan);
        if (!empty($kegiatan)) {
            $fileName .= '_' . str_replace(' ', '_', $kegiatan);
        }
        $fileName .= '_' . date('Ymd_His');
        // Export berdasarkan format
        if ($exportFormat == 'excel') {
            return Excel::download($exportClass, $fileName . '.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, $fileName . '.csv');
        } elseif ($exportFormat == 'word') {
            return $exportClass->exportToWord();
        }
        return back()->with('error', 'Format ekspor tidak didukung.');
    }
}
