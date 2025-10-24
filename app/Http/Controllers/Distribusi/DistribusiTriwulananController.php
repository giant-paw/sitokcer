<?php

namespace App\Http\Controllers\Distribusi;

use App\Http\Controllers\Controller;
use App\Models\Distribusi\DistribusiTriwulanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DistribusiTriwulananExport;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Validation\Rule;

class DistribusiTriwulananController extends Controller
{
    public function index(Request $request, $jenisKegiatan)
    {
        // 1. Validasi jenis kegiatan
        $validJenis = ['spunp', 'shkk'];
        $jenisKegiatanLower = strtolower($jenisKegiatan);
        if (!in_array($jenisKegiatanLower, $validJenis)) {
            abort(404);
        }
        $prefixKegiatan = strtoupper($jenisKegiatan); // SPUNP atau SHKK

        // 2. Logika Filter Tahun (Konsisten)
        $selectedTahun = $request->input('tahun', date('Y'));
        $availableTahun = DistribusiTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()->whereNotNull('created_at')->orderBy('tahun', 'desc')
            ->pluck('tahun')->toArray();
        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // 3. Kueri Utama
        $query = DistribusiTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Filter jenis
            ->whereYear('created_at', $selectedTahun); // Filter tahun

        // Filter Kegiatan Spesifik (Tab)
        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            $query->where('nama_kegiatan', $selectedKegiatan);
        }

        // Filter Pencarian
        $search = $request->input('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('BS_Responden', 'like', "%{$search}%")
                  ->orWhere('pencacah', 'like', "%{$search}%")
                  ->orWhere('pengawas', 'like', "%{$search}%")
                  ->orWhere('nama_kegiatan', 'like', "%{$search}%");
            });
        }

        // 4. Logika Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        // 5. Ambil Data
        $listData = $query->latest('id_distribusi_triwulanan')->paginate($perPage)->withQueryString();

        // 6. Logika Hitung Tab
        $kegiatanCounts = DistribusiTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        // Ambil master kegiatan hanya untuk jenis yang relevan
        $masterKegiatanList = MasterKegiatan::where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                                            ->orderBy('nama_kegiatan')->get();

        // 7. Kirim ke View
        return view('timDistribusi.distribusiTriwulanan', compact(
            'listData',
            'kegiatanCounts',
            'jenisKegiatan',
            'masterKegiatanList',
            'availableTahun',
            'selectedTahun',
            'selectedKegiatan',
            'search'
        ));
    }

    /**
     * Simpan data baru (AJAX ready).
     */
    public function store(Request $request)
    {
        $baseRules = [
            'nama_kegiatan'       => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden'        => 'required|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'       => ['required', Rule::in(['Belum Selesai', 'Selesai'])], // Sesuaikan opsi
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
                return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput()->with('error_modal', 'tambahDataModal');
        }

        $validatedData = $validator->validated();
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        DistribusiTriwulanan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    /**
     * Ambil data untuk modal edit ($id manual + format tanggal).
     */
    public function edit($id)
    {
        $distribusi_triwulanan = DistribusiTriwulanan::findOrFail($id);
        $data = $distribusi_triwulanan->toArray();

        // Format tanggal ke Y-m-d
        $targetPenyelesaian = $distribusi_triwulanan->target_penyelesaian;
        $tanggalPengumpulan = $distribusi_triwulanan->tanggal_pengumpulan;
        $data['target_penyelesaian'] = $targetPenyelesaian ? Carbon::parse($targetPenyelesaian)->toDateString() : null;
        $data['tanggal_pengumpulan'] = $tanggalPengumpulan ? Carbon::parse($tanggalPengumpulan)->toDateString() : null;

        return response()->json($data);
    }

    /**
     * Update data ($id manual).
     */
    public function update(Request $request, $id)
    {
        $distribusi_triwulanan = DistribusiTriwulanan::findOrFail($id);
        $baseRules = [ // Sama seperti store, tambahkan exists
            'nama_kegiatan'       => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden'        => 'required|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'       => ['required', Rule::in(['Belum Selesai', 'Selesai'])],
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
                ->with('edit_id', $distribusi_triwulanan->id_distribusi_triwulanan);
        }

        $validatedData = $validator->validated();
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        $distribusi_triwulanan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        return back()->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }

    /**
     * Hapus data ($id manual).
     */
    public function destroy($id)
    {
        $distribusi_triwulanan = DistribusiTriwulanan::findOrFail($id);
        $distribusi_triwulanan->delete();

        if (request()->ajax() || request()->wantsJson()) {
             return response()->json(['success' => 'Data berhasil dihapus!']);
        }
        return back()->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Hapus banyak data.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:distribusi_triwulanan,id_distribusi_triwulanan'
        ]);
        DistribusiTriwulanan::whereIn('id_distribusi_triwulanan', $request->ids)->delete();
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Cari petugas (autocomplete).
     */
    public function searchPetugas(Request $request)
    {
         $request->validate(['query' => 'nullable|string|max:100']);
        $query = $request->input('query', '');
        $data = MasterPetugas::query()
            ->where('nama_petugas', 'LIKE', "%{$query}%")
            ->limit(10)
            ->pluck('nama_petugas');
        return response()->json($data);
    }

     /**
      * Cari kegiatan (autocomplete).
      */
    public function searchKegiatan(Request $request, $jenisKegiatan = null) // Tambah $jenisKegiatan opsional
    {
         $request->validate(['query' => 'nullable|string|max:100']);
         $query = $request->input('query', '');
         $kegiatanQuery = MasterKegiatan::query();

         // Filter berdasarkan jenisKegiatan jika diberikan di URL
         if ($jenisKegiatan) {
             $jenisKegiatanLower = strtolower($jenisKegiatan);
             if (in_array($jenisKegiatanLower, ['spunp', 'shkk'])) {
                 $kegiatanQuery->where('nama_kegiatan', 'LIKE', strtoupper($jenisKegiatan) . '%');
             }
         }

         $data = $kegiatanQuery
             ->where('nama_kegiatan', 'LIKE', "%{$query}%")
             ->limit(10)
             ->pluck('nama_kegiatan');
         return response()->json($data);
    }

    // PERBAIKAN 4: Method export yang benar
    public function export(Request $request, $jenisKegiatan)
    {
        // Validasi jenis kegiatan
        if (!in_array(strtolower($jenisKegiatan), ['spunp', 'shkk'])) {
            abort(404);
        }

        $dataRange = $request->input('dataRange', 'all'); // default 'all'
        $dataFormat = $request->input('dataFormat');
        $exportFormat = $request->input('exportFormat');
        $kegiatan = $request->input('kegiatan');
        $search = $request->input('search');
        $currentPage = $request->input('page', 1); // Ambil halaman aktif
        $perPage = $request->input('per_page', 20);

        // Kirim semua parameter yang diperlukan
        $exportClass = new DistribusiTriwulananExport(
            $dataRange,
            $dataFormat,
            $jenisKegiatan,
            $kegiatan,
            $search,
            $currentPage,
            $perPage
        );

        if ($exportFormat == 'excel') {
            return Excel::download($exportClass, 'DistribusiTriwulanan_' . strtoupper($jenisKegiatan) . '.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, 'DistribusiTriwulanan_' . strtoupper($jenisKegiatan) . '.csv');
        } elseif ($exportFormat == 'word') {
            return $exportClass->exportToWord();
        }

        return back()->with('error', 'Format ekspor tidak didukung.');
    }
}
