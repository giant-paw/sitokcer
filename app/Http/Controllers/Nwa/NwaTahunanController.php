<?php

namespace App\Http\Controllers\Nwa;

use App\Http\Controllers\Controller;
use App\Models\Nwa\NwaTahunan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;     
use Illuminate\Support\Facades\Validator;

class NwaTahunanController extends Controller
{
    public function index(Request $request)
    {
        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = NwaTahunan::query()
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

        // Kueri Utama dengan filter tahun
        $query = NwaTahunan::query()
                 ->whereYear('created_at', $selectedTahun); 


        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            $query->where('nama_kegiatan', $selectedKegiatan);
        }

        // Filter pencarian ('q' diganti 'search')
        $search = $request->input('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('BS_Responden', 'like', "%{$search}%")
                  ->orWhere('pencacah', 'like', "%{$search}%")
                  ->orWhere('pengawas', 'like', "%{$search}%")
                  ->orWhere('nama_kegiatan', 'like', "%{$search}%") // Tambah search nama kegiatan
                  ->orWhere('flag_progress', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        // Ganti 'rows' menjadi 'listData'
        $listData = $query->latest('id_nwa')->paginate($perPage)->withQueryString(); // Order by primary key

        // Hitung jumlah data per kegiatan (untuk Tabs)
        // Ganti 'katMap' menjadi 'kegiatanCounts'
        $kegiatanCounts = NwaTahunan::query()
            ->whereYear('created_at', $selectedTahun) // Filter tahun
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        // Ambil data master kegiatan (untuk autocomplete)
        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        // Kirim data ke view (sesuaikan nama variabel)
        return view('timNWA.tahunan.NWAtahunan', compact(
            'listData',         // Ganti 'rows'
            'kegiatanCounts',   // Ganti 'kategoris' & 'katMap'
            'selectedKegiatan', // Ganti 'kategori'
            'search',           // Ganti 'q'
            'masterKegiatanList',
            'availableTahun',
            'selectedTahun'
        ));
    }

    public function store(Request $request)
    {
        // --- Gunakan Validasi Seperti Produksi ---
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:100|exists:master_kegiatan,nama_kegiatan', // Max 100 & exists
            'BS_Responden' => 'nullable|string|max:150',
            'pencacah' => 'required|string|max:100|exists:master_petugas,nama_petugas', // Max 100 & exists
            'pengawas' => 'required|string|max:100|exists:master_petugas,nama_petugas', // Max 100 & exists
            'target_penyelesaian' => 'nullable|date',
             // Sesuaikan opsi jika berbeda, pastikan required
            'flag_progress' => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar.',
            'pencacah.exists' => 'Nama pencacah tidak terdaftar.',
            'pengawas.exists' => 'Nama pengawas tidak terdaftar.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        // --- Logika AJAX 422 ---
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput()->with('error_modal', 'tambahDataModal');
        }

        $validatedData = $validator->validated();

        // Hapus format manual tanggal_pengumpulan, biarkan Eloquent handle
        // if (!empty($validatedData['tanggal_pengumpulan'])) {
        //     $validatedData['tanggal_pengumpulan'] = Carbon::parse($validatedData['tanggal_pengumpulan'])->format('Y-m-d H:i:s');
        // }

        // Set tahun_kegiatan jika perlu dan ada kolomnya
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        NwaTahunan::create($validatedData);

        // --- Logika AJAX 200 ---
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data NWA Tahunan berhasil ditambahkan!']);
        }
        return back()->with('success', 'Data NWA Tahunan ditambahkan.'); // Ganti 'ok' jadi 'success'
    }

    // --- Gunakan Method Edit untuk AJAX ---
    public function edit(NwaTahunan $tahunan) // Gunakan Route Model Binding
    {
        return response()->json($tahunan); // Kirim data sebagai JSON
    }

    // Fungsi show mungkin tidak diperlukan jika detail via modal/AJAX
    // public function show(NwaTahunan $tahunan) { ... }

    public function update(Request $request, NwaTahunan $tahunan)
    {
         // --- Gunakan Validasi Seperti Produksi ---
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:100|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'nullable|string|max:150',
            'pencacah' => 'required|string|max:100|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:100|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'nullable|date',
            'flag_progress' => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];
        $customMessages = [ /* ... sama seperti store ... */ ];
        $validator = Validator::make($request->all(), $baseRules, $customMessages);

         // --- Logika AJAX 422 ---
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput()
                         ->with('error_modal', 'editDataModal')
                         ->with('edit_id', $tahunan->id_nwa); // Kirim ID untuk JS fallback
        }

        $validatedData = $validator->validated();

        // Hapus format manual
        // $validatedData['tanggal_pengumpulan'] = ...

        // Set tahun_kegiatan jika perlu
         if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        $tahunan->update($validatedData);

        // --- Logika AJAX 200 ---
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Perubahan berhasil disimpan!']);
        }
        return back()->with('success', 'Perubahan disimpan.'); // Ganti 'ok' jadi 'success'
    }

    public function destroy(NwaTahunan $tahunan)
    {
        $tahunan->delete();
         // Respons standar (redirect) biasanya cukup untuk delete
        return back()->with('success', 'Data dihapus.'); // Ganti 'ok' jadi 'success'
    }

     // --- TAMBAHKAN FUNGSI BULK DELETE (jika belum ada/perlu disesuaikan) ---
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:nwa_tahunan,id_nwa' // Sesuaikan nama tabel & primary key
        ]);

        NwaTahunan::whereIn('id_nwa', $request->ids)->delete();
        return back()->with('success', 'Data yang dipilih berhasil dihapus!');
    }

     public function searchPetugas(Request $request)
    {
         $request->validate([+
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
