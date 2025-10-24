<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produksi\ProduksiCaturwulanan;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProduksiCaturwulananExport;
use PhpOffice\PhpWord\TemplateProcessor;

class ProduksiCaturwulananController extends Controller
{
    public function index(Request $request, $jenisKegiatan)
    {
        $lowercaseJenis = strtolower($jenisKegiatan);
        $validJenis = ['ubinan padi palawija', 'updating utp palawija'];

        if (!in_array($lowercaseJenis, $validJenis)) {
            abort(404);
        }

        $searchString = str_replace(' ', '', $lowercaseJenis);

        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = ProduksiCaturwulanan::query()
            ->whereRaw('LOWER(REPLACE(nama_kegiatan, " ", "")) LIKE ?', [$searchString . '%'])
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

        $query = ProduksiCaturwulanan::query()
            ->whereRaw('LOWER(REPLACE(nama_kegiatan, " ", "")) LIKE ?', [$searchString . '%'])
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

        $listData = $query->latest('id_produksi_caturwulanan')->paginate($perPage)->withQueryString();

        $kegiatanCounts = ProduksiCaturwulanan::query()
            ->whereRaw('LOWER(REPLACE(nama_kegiatan, " ", "")) LIKE ?', [$searchString . '%'])
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        return view('timProduksi.produksiCaturwulanan', compact(
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
            'tanggal_pengumpulan' => 'required|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar di master kegiatan.',
            'pencacah.exists' => 'Nama pencacah tidak terdaftar di master petugas.',
            'pengawas.exists' => 'Nama pengawas tidak terdaftar di master petugas.',
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
            try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) {
            }
        }

        ProduksiCaturwulanan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    public function edit(ProduksiCaturwulanan $produksi_caturwulanan)
    {
        $data = $produksi_caturwulanan->toArray();

        $targetPenyelesaian = $produksi_caturwulanan->target_penyelesaian;
        $tanggalPengumpulan = $produksi_caturwulanan->tanggal_pengumpulan;

        $data['target_penyelesaian'] = $targetPenyelesaian
            ? Carbon::parse($targetPenyelesaian)->toDateString()
            : null;

        $data['tanggal_pengumpulan'] = $tanggalPengumpulan
            ? Carbon::parse($tanggalPengumpulan)->toDateString()
            : null;

        return response()->json($data);
    }

    public function update(Request $request, ProduksiCaturwulanan $produksi_caturwulanan)
    {
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => 'required|string',
            'tanggal_pengumpulan' => 'required|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar di master kegiatan.',
            'pencacah.exists' => 'Nama pencacah tidak terdaftar di master petugas.',
            'pengawas.exists' => 'Nama pengawas tidak terdaftar di master petugas.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput()
                ->with('error_modal', 'editDataModal')
                ->with('edit_id', $produksi_caturwulanan->id_produksi_caturwulanan);
        }

        $validatedData = $validator->validated();

        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) {
            }
        }

        $produksi_caturwulanan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        return back()->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }


    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:produksi_caturwulanan,id_produksi_caturwulanan'
        ]);

        ProduksiCaturwulanan::whereIn('id_produksi_caturwulanan', $request->ids)->delete();

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }


    public function destroy(ProduksiCaturwulanan $produksi_caturwulanan)
    {
        $produksi_caturwulanan->delete();

        return back()->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

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

    public function export(Request $request, $jenisKegiatan)
    {
        // Validasi...
        $dataRange = $request->input('dataRange', 'all');
        $dataFormat = $request->input('dataFormat');
        $exportFormat = $request->input('exportFormat');
        $kegiatan = $request->input('kegiatan');
        $search = $request->input('search');
        $tahun = $request->input('tahun', date('Y'));
        $currentPage = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        // Kirim ke Export class
        $exportClass = new ProduksiCaturwulananExport(
            $dataRange,
            $dataFormat,
            $jenisKegiatan,
            $kegiatan,
            $search,
            $tahun,
            $currentPage,
            $perPage
        );

        if ($exportFormat == 'excel') {
            return Excel::download($exportClass, 'ProduksiCaturwulanan_' . strtoupper($jenisKegiatan) . '_' . date('Ymd_His') . '.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, 'ProduksiCaturwulanan_' . strtoupper($jenisKegiatan) . '_' . date('Ymd_His') . '.csv');
        } elseif ($exportFormat == 'word') {
            return $exportClass->exportToWord();
        }

        return back()->with('error', 'Format ekspor tidak didukung.');
    }
}
