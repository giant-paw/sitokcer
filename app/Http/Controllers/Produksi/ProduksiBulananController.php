<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\Produksi\ProduksiBulanan;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProduksiBulananExport;


use Illuminate\Http\Request;

class ProduksiBulananController extends Controller
{
    public function index(Request $request, $jenisKegiatan)
    {
        // Daftar jenis kegiatan yang valid (semua huruf kecil)
        $validJenis = ['ksapadi', 'ksajagung', 'lptb', 'sphsbs', 'sppalawija', 'perkebunan', 'ibs'];
        $lowercaseJenis = strtolower($jenisKegiatan);

        if (!in_array($lowercaseJenis, $validJenis)) {
            abort(404);
        }

        // --- Logika Filter Tahun ---
        $selectedTahun = $request->input('tahun', date('Y'));

        // Ambil tahun unik berdasarkan created_at untuk jenis kegiatan ini
        $availableTahun = ProduksiBulanan::query()
            ->where('nama_kegiatan', 'LIKE', $jenisKegiatan . '%') // Filter berdasarkan nama asli
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->whereNotNull('created_at') // Penting: Abaikan data yang created_at nya NULL
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        // Pastikan tahun ini ada di daftar
        if (!empty($availableTahun) && !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        } elseif (empty($availableTahun)) {
            $availableTahun = [date('Y')]; // Default jika belum ada data
        }
        // --- Akhir Logika Tahun ---

        // Kueri Utama dengan filter nama kegiatan dan tahun
        $query = ProduksiBulanan::query()
            ->where('nama_kegiatan', 'LIKE', $jenisKegiatan . '%') // Filter nama asli
            ->whereYear('created_at', $selectedTahun); // Filter tahun

        // Filter berdasarkan tab kegiatan spesifik (jika ada)
        if ($request->filled('kegiatan')) {
            $query->where('nama_kegiatan', $request->kegiatan);
        }

        // Filter pencarian
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('BS_Responden', 'like', "%{$searchTerm}%")
                    ->orWhere('pencacah', 'like', "%{$searchTerm}%")
                    ->orWhere('pengawas', 'like', "%{$searchTerm}%")
                    ->orWhere('nama_kegiatan', 'like', "%{$searchTerm}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        $listData = $query->latest('id_produksi_bulanan')->paginate($perPage)->withQueryString();

        $kegiatanCounts = ProduksiBulanan::query()
            ->where('nama_kegiatan', 'LIKE', $jenisKegiatan . '%')
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        // Ambil data master kegiatan (untuk autocomplete)
        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        // Kirim data ke view
        return view('timProduksi.produksiBulanan', compact(
            'listData',
            'kegiatanCounts',
            'jenisKegiatan',
            'masterKegiatanList',
            'availableTahun',
            'selectedTahun'
        ));
    }

    public function store(Request $request)
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
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar di master.',
            'pencacah.exists' => 'Nama pencacah tidak terdaftar di master.',
            'pengawas.exists' => 'Nama pengawas tidak terdaftar di master.',
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
            try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) {
            }
        }

        ProduksiBulanan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    public function edit(ProduksiBulanan $produksi_bulanan)
    {
        $data = $produksi_bulanan->toArray();

        $targetPenyelesaian = $produksi_bulanan->target_penyelesaian;
        $tanggalPengumpulan = $produksi_bulanan->tanggal_pengumpulan;
        
        $data['target_penyelesaian'] = $targetPenyelesaian 
            ? Carbon::parse($targetPenyelesaian)->toDateString() 
            : null;
            
        $data['tanggal_pengumpulan'] = $tanggalPengumpulan
            ? Carbon::parse($tanggalPengumpulan)->toDateString()
            : null;

        return response()->json($data);
    }


    public function update(Request $request, ProduksiBulanan $produksi_bulanan)
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

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput()
                ->with('error_modal', 'editDataModal')
                ->with('edit_id', $produksi_bulanan->id_produksi_bulanan);
        }

        $validatedData = $validator->validated();

        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) { /* Abaikan */
            }
        }

        $produksi_bulanan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        return back()->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:produksi_bulanan,id_produksi_bulanan' // Pastikan nama tabel & primary key benar
        ]);

        ProduksiBulanan::whereIn('id_produksi_bulanan', $request->ids)->delete();
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    public function destroy(ProduksiBulanan $produksi_bulanan)
    {
        $produksi_bulanan->delete();
        return back()->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

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

    public function export(Request $request, $jenisKegiatan)
    {
        // Validasi jenis kegiatan
        $validJenis = ['ksapadi', 'ksajagung', 'lptb', 'sphsbs', 'sppalawija', 'perkebunan', 'ibs'];
        $lowercaseJenis = strtolower($jenisKegiatan);

        if (!in_array($lowercaseJenis, $validJenis)) {
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
        $exportClass = new ProduksiBulananExport(
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
        $fileName = 'ProduksiBulanan_' . strtoupper($jenisKegiatan);
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
