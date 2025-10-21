<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\Produksi\ProduksiTahunan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan; 
use Illuminate\Support\Facades\Validator;

class ProduksiTahunanController extends Controller
{
    public function index(Request $request)
    {
        $selectedTahun = $request->input('tahun', date('Y'));
        $availableTahun = ProduksiTahunan::query()
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->whereNotNull('created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (!empty($availableTahun) && !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        } elseif (empty($availableTahun)) {
            $availableTahun = [date('Y')];
        }

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

        $perPage = $request->input('per_page', 20);

        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        $listData = $query->latest('id_produksi')->paginate($perPage)->withQueryString();

        $kegiatanCounts = ProduksiTahunan::query()
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        return view('timProduksi.produksiTahunan', compact(
            'listData',
            'kegiatanCounts',
            'masterKegiatanList',
            'availableTahun',
            'selectedTahun'
        ));
    }

    // --- PERBAIKAN DI FUNGSI STORE ---
    public function store(Request $request)
    {
        // Gunakan validasi yang sama seperti di 'update'
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => 'required|string',
            'tanggal_pengumpulan' => 'nullable|date', // Sesuai blade, nullable
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar di master.',
            'pencacah.exists' => 'Nama pencacah tidak terdaftar di master.',
            'pengawas.exists' => 'Nama pengawas tidak terdaftar di master.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        // Tambahkan logika respons AJAX 422
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data yang diberikan tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            // Fallback untuk non-AJAX
            return back()->withErrors($validator)->withInput()->with('error_modal', 'tambahDataModal');
        }
        // --- AKHIR PERBAIKAN VALIDASI ---

        $validatedData = $validator->validated();

        // Set tahun_kegiatan (jika kolomnya ada)
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try {
               $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) { /* Abaikan jika parse gagal */ }
        }

        ProduksiTahunan::create($validatedData);

        // Respons AJAX sukses sudah ada
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        // Fallback respons non-AJAX
        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }
    // --- AKHIR PERBAIKAN STORE ---

    public function edit(ProduksiTahunan $produksi) // Sudah menggunakan Route Model Binding
    {
        return response()->json($produksi);
    }

    public function update(Request $request, ProduksiTahunan $produksi)
    {
         $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => 'required|string',
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar.',
            'pencacah.exists' => 'Nama pencacah tidak terdaftar.',
            'pengawas.exists' => 'Nama pengawas tidak terdaftar.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        // Validasi Gagal: Kirim JSON 422 jika AJAX
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            // Fallback non-AJAX
            return back()->withErrors($validator)->withInput()
                         ->with('error_modal', 'editDataModal')
                         ->with('edit_id', $produksi->id_produksi);
        }

        $validatedData = $validator->validated();

        // Set tahun_kegiatan (opsional, jika ada kolomnya)
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
             } catch (\Exception $e) { /* Abaikan jika gagal parse */ }
        }

        // Lakukan Update
        $isUpdated = $produksi->update($validatedData);

        // Periksa apakah update berhasil
        if (!$isUpdated) {
             // Jika update gagal karena alasan lain (misal masalah database)
             if ($request->ajax() || $request->wantsJson()) {
                 return response()->json(['message' => 'Gagal memperbarui data.'], 500); // Internal Server Error
             }
             return back()->with('error', 'Gagal memperbarui data.'); // Fallback
        }

        // --- INI BAGIAN PENTING UNTUK SUKSES ---
        // Validasi & Update Sukses: Kirim JSON 200 jika AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }

        // Fallback non-AJAX: Lakukan redirect
        return redirect()->route('tim-produksi.tahunan.index')->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
        // --- AKHIR PERUBAHAN ---
    }

    // Fungsi bulkDelete sudah benar
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:produksi_tahunan,id_produksi'
        ]);
        ProduksiTahunan::whereIn('id_produksi', $request->ids)->delete();
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]); // hide_after bisa dihapus jika tidak perlu
    }

    // Fungsi destroy sudah benar (Route Model Binding)
    public function destroy(ProduksiTahunan $produksi)
    {
        $produksi->delete();
        return redirect()->route('tim-produksi.tahunan.index')->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    // Fungsi searchPetugas sudah benar
     public function searchPetugas(Request $request)
    {
         $request->validate([
            'field' => 'required|in:pencacah,pengawas',
            'query' => 'nullable|string|max:100',
        ]);
        $query = $request->input('query', '');
        // Pastikan path Model MasterPetugas benar
        $data = MasterPetugas::query()
            ->where('nama_petugas', 'LIKE', "%{$query}%")
            ->limit(10)
            ->pluck('nama_petugas');
        return response()->json($data);
    }
}
