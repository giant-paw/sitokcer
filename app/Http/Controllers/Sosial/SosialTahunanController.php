<?php

namespace App\Http\Controllers\Sosial;

use App\Http\Controllers\Controller;
use App\Models\Sosial\SosialTahunan; 
use Illuminate\Http\Request;
use App\Models\Master\MasterPetugas; 
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SosialTahunanController extends Controller
{
    public function index(Request $request)
    {
        $selectedTahun = $request->input('tahun', date('Y'));
        
        // Logika disamakan: filter 'availableTahun' dari created_at
        $availableTahun = SosialTahunan::query() // Model diubah
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

        // Logika disamakan: query utama filter dari created_at
        $query = SosialTahunan::query() // Model diubah
            ->whereYear('created_at', $selectedTahun);

        if ($request->filled('kegiatan')) {
            $query->where('nama_kegiatan', $request->kegiatan);
        }

        // Logika disamakan: search case-sensitive
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
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

        // Primary key diubah
        $listData = $query->latest('id_sosial')->paginate($perPage)->withQueryString(); 

        // Logika disamakan: query counts filter dari created_at
        $kegiatanCounts = SosialTahunan::query() // Model diubah
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        // Path view diubah
        return view('timSosial.tahunan.sosialtahunan', compact(
            'listData',
            'kegiatanCounts',
            'masterKegiatanList',
            'availableTahun',
            'selectedTahun'
        ));
    }

    /**
     * Store Data (AJAX)
     */
    public function store(Request $request)
    {
        // Validasi disamakan
        $baseRules = [
            'nama_kegiatan'       => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            // 'BS_Responden' disesuaikan dengan model Sosial (boleh null)
            'BS_Responden'        => 'nullable|string|max:255', 
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
             // 'flag_progress' disesuaikan dengan model Sosial
            'flag_progress'       => 'required|string|in:Belum Mulai,Proses,Selesai',
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar di master.',
            'pencacah.exists'      => 'Nama pencacah tidak terdaftar di master.',
            'pengawas.exists'      => 'Nama pengawas tidak terdaftar di master.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        // Logika disamakan: AJAX 422 response
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

        // Logika disamakan: menyimpan 'tahun_kegiatan'
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try {
               $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) { /* Abaikan */ }
        }

        SosialTahunan::create($validatedData); // Model diubah

        // Logika disamakan: AJAX 200 response
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    /**
     * Edit Data (AJAX)
     */
    // Route model binding diubah ke SosialTahunan $tahunan
    public function edit(SosialTahunan $tahunan) 
    {
        return response()->json($tahunan); // Variabel diubah
    }

    /**
     * Update Data (AJAX)
     */
    // Route model binding diubah ke SosialTahunan $tahunan
    public function update(Request $request, SosialTahunan $tahunan)
    {
         $baseRules = [
            'nama_kegiatan'       => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden'        => 'nullable|string|max:255', // Sesuai model sosial
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'       => 'required|string|in:Belum Mulai,Proses,Selesai', // Sesuai model sosial
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar.',
            'pencacah.exists'      => 'Nama pencacah tidak terdaftar.',
            'pengawas.exists'      => 'Nama pengawas tidak terdaftar.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
             // Primary key diubah
            return back()->withErrors($validator)->withInput()
                         ->with('error_modal', 'editDataModal')
                         ->with('edit_id', $tahunan->id_sosial);
        }

        $validatedData = $validator->validated();

        // Logika disamakan: menyimpan 'tahun_kegiatan'
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
             } catch (\Exception $e) { /* Abaikan */ }
        }

        $isUpdated = $tahunan->update($validatedData); // Variabel diubah

        if (!$isUpdated) {
             if ($request->ajax() || $request->wantsJson()) {
                  return response()->json(['message' => 'Gagal memperbarui data.'], 500);
             }
             return back()->with('error', 'Gagal memperbarui data.');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }

        // Nama route redirect diubah
        return redirect()->route('tim-sosial.tahunan.index')->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }

    /**
     * Bulk Delete
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            // Validasi diubah ke tabel dan pk sosial
            'ids.*' => 'exists:sosial_tahunan,id_sosial' 
        ]);
        
        // Model dan Pk diubah
        SosialTahunan::whereIn('id_sosial', $request->ids)->delete(); 
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    public function destroy(SosialTahunan $tahunan) 
    {
        $tahunan->delete(); // Variabel diubah
        // Nama route redirect diubah
        return redirect()->route('tim-sosial.tahunan.index')->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    public function searchPetugas(Request $request)
    {
         $request->validate([
             'field' => 'required|in:pencacah,pengawas',
             'query' => 'nullable|string|max:100',
         ]);
         $query = $request->input('query', '');
         
         // Path Model MasterPetugas disesuaikan
         $data = \App\Models\Master\MasterPetugas::query() 
             ->where('nama_petugas', 'LIKE', "%{$query}%")
             ->limit(10)
             ->pluck('nama_petugas');
         return response()->json($data);
    }
    
    public function searchKegiatan(Request $request)
    {
         $request->validate(['query' => 'nullable|string|max:100']);
         $query = $request->input('query', '');

         $data = MasterKegiatan::query()
             // ->where('jenis', 'Sosial') // Opsional: filter hanya kegiatan sosial
             ->where('nama_kegiatan', 'LIKE', "%{$query}%")
             ->limit(10)
             ->pluck('nama_kegiatan');
             
         return response()->json($data);
    }
}
