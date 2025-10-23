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

class NwaTriwulananController extends Controller
{
    public function index(Request $request, string $jenisKegiatan)
    {
        $validJenis = ['sklnp', 'snaper', 'sktnp'];
              $lowercaseJenis = strtolower($jenisKegiatan);

              if (!in_array($lowercaseJenis, $validJenis)) {
                       abort(404);
              }
              
        // ==== PERBAIKAN 1: Sederhanakan prefix agar selalu lowercase ====
              $prefix = $lowercaseJenis; // Ini sudah benar 'sklnp', 'snaper', atau 'sktnp'

              $selectedTahun = $request->input('tahun', date('Y'));

              $availableTahun = NwaTriwulanan::query()
                       ->where(DB::raw('LOWER(nama_kegiatan)'), 'LIKE', $prefix . '%') 
                       ->select(DB::raw('YEAR(created_at) as tahun'))
                       ->distinct()
                       ->whereNotNull('created_at')
                       ->orderBy('tahun', 'desc')
                       ->pluck('tahun')
                       ->toArray();
              
        // Logika ini sudah bagus, tidak perlu diubah
              if (!empty($availableTahun) && !in_array(date('Y'), $availableTahun)) {
                         array_unshift($availableTahun, date('Y'));
              } elseif (empty($availableTahun)) {
                         $availableTahun = [date('Y')];
              }
              
              // ==== PERBAIKAN 3: Ubah kueri utama menjadi case-insensitive ====
              $query = NwaTriwulanan::query()
                       ->where(DB::raw('LOWER(nama_kegiatan)'), 'LIKE', $prefix . '%') // Gunakan DB::raw(LOWER(...))
                       ->whereYear('created_at', $selectedTahun);        

              // Filter 'kegiatan' (dari tab)
              if ($request->filled('kegiatan')) {
                       $query->where('nama_kegiatan', $request->kegiatan);
              }

              $search = $request->input('search', ''); 
              if ($search !== '') {
                       $query->where(function ($q) use ($search) {
                            $q->where('BS_Responden', 'LIKE', "%$search%")
                                 ->orWhere('pencacah', 'LIKE', "%$search%")
                                 ->orWhere('pengawas', 'LIKE', "%$search%")
                                 ->orWhere('flag_progress', 'LIKE', "%$search%")
                                 ->orWhere('nama_kegiatan', 'LIKE', "%$search%");
                       });
              }

              // Pagination
              $perPage = $request->input('per_page', 20); 
              if ($perPage == 'all') {
                       $total = (clone $query)->count();
                       $perPage = $total > 0 ? $total : 20;
              }

              $listData = $query->latest('id_nwa_triwulanan')->paginate($perPage)->withQueryString();

              $kegiatanCounts = NwaTriwulanan::query()
                       ->where(DB::raw('LOWER(nama_kegiatan)'), 'LIKE', $prefix . '%')
                       ->whereYear('created_at', $selectedTahun)
                       ->select('nama_kegiatan', DB::raw('count(*) as total'))
                       ->groupBy('nama_kegiatan')
                       ->orderBy('nama_kegiatan')
                       ->get();
                       
              $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

              return view('timNWA.triwulanan.NWATriwulanan', [
                       'listData' => $listData,
                       'kegiatanCounts' => $kegiatanCounts,
                       'jenisKegiatan' => $jenisKegiatan, 
                       'masterKegiatanList' => $masterKegiatanList,
                       'availableTahun' => $availableTahun,
                       'selectedTahun' => $selectedTahun,
                       'search' => $search, 
                       'selectedKegiatan' => $request->input('kegiatan', ''), 
              ]);
    }

    /** Simpan data baru */
    public function store(Request $request) // Hapus $jenis
    {
        // Validasi standar
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:100|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'nullable|string|max:150',
            'pencacah' => 'required|string|max:100|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:100|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'nullable|date',
            'flag_progress' => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
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
                 return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput()->with('error_modal', 'tambahDataModal');
        }

        $validatedData = $validator->validated();
        
        // Set tahun_kegiatan (jika ada kolomnya)
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }
        
        NwaTriwulanan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back()->with('success', 'Data NWA Triwulanan ditambahkan.');
    }

    /** Ambil data untuk modal edit */
    public function edit(NwaTriwulanan $triwulanan) // Ganti nama parameter
    {
        return response()->json($triwulanan);
    }

    /** Update data */
    public function update(Request $request, NwaTriwulanan $triwulanan) // Hapus $jenis, ganti nama parameter
    {
        // Validasi standar (sama seperti store)
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

        if ($validator->fails()) {
             if ($request->ajax() || $request->wantsJson()) {
                 return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput()
                         ->with('error_modal', 'editDataModal')
                         ->with('edit_id', $triwulanan->id_nwa_triwulanan);
        }

        $validatedData = $validator->validated();
        
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        $triwulanan->update($validatedData);
        
        if ($request->ajax() || $request->wantsJson()) {
             return response()->json(['success' => 'Perubahan berhasil disimpan!']);
        }
        return back()->with('success', 'Perubahan disimpan.');
    }

    /** Hapus data */
    public function destroy(NwaTriwulanan $triwulanan) // Hapus $jenis
    {
        $triwulanan->delete();
        return back()->with('success', 'Data dihapus.');
    }

    /** Hapus data massal */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:nwa_triwulanan,id_nwa_triwulanan' 
        ]);
        NwaTriwulanan::whereIn('id_nwa_triwulanan', $request->ids)->delete();
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }
}
