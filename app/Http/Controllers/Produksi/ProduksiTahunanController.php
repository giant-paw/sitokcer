<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\Produksi\ProduksiTahunan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\MasterPetugas\MasterPetugas;
use App\Models\Master\MasterKegiatan; 
use Illuminate\Support\Facades\Validator;

class ProduksiTahunanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Logika Filter Tahun
        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = ProduksiTahunan::query()
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->whereNotNull('created_at') // Penting
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // 2. Kueri Utama
        $query = ProduksiTahunan::query()
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

        // 3. Logika Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        // 4. Ambil Data (Gunakan primary key yang benar, misal: 'id_produksi')
        $listData = $query->latest('id_produksi')->paginate($perPage)->withQueryString();

        // 5. Logika Hitung Tab
        $kegiatanCounts = ProduksiTahunan::query()
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        // 6. Kirim ke View
        return view('timProduksi.produksiTahunan', compact(
            'listData',
            'kegiatanCounts',
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
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            // Sesuaikan Rule::in jika opsinya beda
            'flag_progress' => ['required', Rule::in(['Belum Selesai', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar.',
            'pencacah.exists' => 'Nama pencacah tidak terdaftar.',
            'pengawas.exists' => 'Nama pengawas tidak terdaftar.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data yang diberikan tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput()->with('error_modal', 'tambahDataModal');
        }

        $validatedData = $validator->validated();

        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        ProduksiTahunan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    /**
     * PERBAIKAN KUNCI:
     * 1. Ambil $id manual.
     * 2. Format tanggal untuk JavaScript (Y-m-d).
     */
    public function edit($id)
    {
        // Ganti 'id_produksi' jika primary key-nya beda
        $produksi_tahunan = ProduksiTahunan::findOrFail($id); 
        
        $data = $produksi_tahunan->toArray();

        // --- INI ADALAH LOGIKA YANG MEMPERBAIKI MASALAH ---
        $targetPenyelesaian = $produksi_tahunan->target_penyelesaian;
        $tanggalPengumpulan = $produksi_tahunan->tanggal_pengumpulan;
        
        $data['target_penyelesaian'] = $targetPenyelesaian 
            ? Carbon::parse($targetPenyelesaian)->toDateString() 
            : null;
            
        $data['tanggal_pengumpulan'] = $tanggalPengumpulan
            ? Carbon::parse($tanggalPengumpulan)->toDateString()
            : null;
        // --- AKHIR LOGIKA PERBAIKAN ---

        return response()->json($data);
    }

    /**
     * PERBAIKAN KUNCI:
     * 1. Ambil $id manual.
     */
    public function update(Request $request, $id)
    {
        // Ganti 'id_produksi' jika primary key-nya beda
        $produksi_tahunan = ProduksiTahunan::findOrFail($id);

        $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => ['required', Rule::in(['Belum Selesai', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];
        
        $customMessages = [ /* ... sama seperti store ... */ ];
        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput()
                ->with('error_modal', 'editDataModal')
                // Ganti 'id_produksi' jika primary key-nya beda
                ->with('edit_id', $produksi_tahunan->id_produksi); 
        }

        $validatedData = $validator->validated();
        
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }
        
        $produksi_tahunan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        return back()->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }

    /**
     * PERBAIKAN KUNCI:
     * 1. Ambil $id manual.
     */
    public function destroy($id)
    {
        // Ganti 'id_produksi' jika primary key-nya beda
        $produksi_tahunan = ProduksiTahunan::findOrFail($id);
        $produksi_tahunan->delete();

        return back()->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Hapus banyak data.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            // Ganti 'id_produksi' jika primary key-nya beda
            'ids.*' => 'exists:produksi_tahunan,id_produksi' 
        ]);

        // Ganti 'id_produksi' jika primary key-nya beda
        ProduksiTahunan::whereIn('id_produksi', $request->ids)->delete();

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Cari petugas (autocomplete).
     */
    public function searchPetugas(Request $request)
    {
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
}