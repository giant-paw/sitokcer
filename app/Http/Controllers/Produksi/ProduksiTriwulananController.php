<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produksi\ProduksiTriwulanan;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProduksiTriwulananExport;
use PhpOffice\PhpWord\TemplateProcessor;

class ProduksiTriwulananController extends Controller
{
    public function index(Request $request, $jenisKegiatan)
    {
        $validJenis = ['sktr', 'tpi', 'sphbst', 'sphtbf', 'sphth', 'airbersih'];
        if (!in_array(strtolower($jenisKegiatan), $validJenis)) {
            abort(404);
        }

        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = ProduksiTriwulanan::query()
            ->where('nama_kegiatan', 'Like', strtoupper($jenisKegiatan) . '%')
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (!in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        $query = ProduksiTriwulanan::query()
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

        $perPage = $request->input('per_page', 20);

        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        $listData = $query->latest('id_produksi_triwulanan')->paginate($perPage)->withQueryString();

        $kegiatanCounts = ProduksiTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', strtoupper($jenisKegiatan) . '%')
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();


        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        return view('timProduksi.produksiTriwulanan', compact(
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
        $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;

        ProduksiTriwulanan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }

        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    public function edit(ProduksiTriwulanan $produksi_triwulanan)
    {
        $data = $produksi_triwulanan->toArray();

        $targetPenyelesaian = $produksi_triwulanan->target_penyelesaian;
        $tanggalPengumpulan = $produksi_triwulanan->tanggal_pengumpulan;

        $data['target_penyelesaian'] = $targetPenyelesaian
            ? Carbon::parse($targetPenyelesaian)->toDateString()
            : null;

        $data['tanggal_pengumpulan'] = $tanggalPengumpulan
            ? Carbon::parse($tanggalPengumpulan)->toDateString()
            : null;

        return response()->json($data);
    }

    public function update(Request $request, ProduksiTriwulanan $produksi_triwulanan)
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
                ->with('error_modal', 'editDataModal')
                ->with('edit_id', $produksi_triwulanan->id_produksi_triwulanan);
        }

        $validatedData = $validator->validated();
        $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;

        $produksi_triwulanan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }

        return back()->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:produksi_triwulanan,id_produksi_triwulanan'
        ]);

        ProduksiTriwulanan::whereIn('id_produksi_triwulanan', $request->ids)->delete();

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    public function destroy(ProduksiTriwulanan $produksi_triwulanan)
    {
        $produksi_triwulanan->delete();

        return back()->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    public function export(Request $request, $jenisKegiatan)
    {
        // Validasi jenis kegiatan
        $validJenis = ['sktr', 'tpi', 'sphbst', 'sphtbf', 'sphth', 'airbersih'];
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
        $exportClass = new ProduksiTriwulananExport(
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
        $fileName = 'ProduksiTriwulanan_' . strtoupper($jenisKegiatan);
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
