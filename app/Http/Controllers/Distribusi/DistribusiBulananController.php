<?php

namespace App\Http\Controllers\Distribusi;

use App\Http\Controllers\Controller;
use App\Models\Distribusi\DistribusiBulanan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpWord\TemplateProcessor;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DistribusiBulananExport;
use Illuminate\Http\Request;
use App\Imports\DistribusiBulananImport;

class DistribusiBulananController extends Controller
{
    public function index(Request $request, $jenisKegiatan)
    {
        $validJenis = ['vhts', 'hkd', 'shpb', 'shp', 'shpj', 'shpbg'];
        if (!in_array(strtolower($jenisKegiatan), $validJenis)) {
            abort(404);
        }

        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = DistribusiBulanan::query()
            ->where('nama_kegiatan', 'Like', strtoupper($jenisKegiatan) . '%')
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (!in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        $query = DistribusiBulanan::query()
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

        $listData = $query->latest('id_distribusi_bulanan')->paginate($perPage)->withQueryString();

        $kegiatanCounts = DistribusiBulanan::query()
            ->where('nama_kegiatan', 'LIKE', strtoupper($jenisKegiatan) . '%')
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        return view('timDistribusi.distribusiBulanan', compact(
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

        DistribusiBulanan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }

        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    public function edit(DistribusiBulanan $distribusi_bulanan)
    {
        $data = $distribusi_bulanan->toArray();

        $targetPenyelesaian = $distribusi_bulanan->target_penyelesaian;
        $tanggalPengumpulan = $distribusi_bulanan->tanggal_pengumpulan;

        $data['target_penyelesaian'] = $targetPenyelesaian
            ? Carbon::parse($targetPenyelesaian)->toDateString()
            : null;

        $data['tanggal_pengumpulan'] = $tanggalPengumpulan
            ? Carbon::parse($tanggalPengumpulan)->toDateString()
            : null;

        return response()->json($data);
    }

    public function update(Request $request, DistribusiBulanan $distribusi_bulanan)
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
                ->with('edit_id', $distribusi_bulanan->id_distribusi_bulanan);
        }

        $validatedData = $validator->validated();
        $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;

        $distribusi_bulanan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }

        return back()->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:distribusi_bulanan,id_distribusi_bulanan'
        ]);

        DistribusiBulanan::whereIn('id_distribusi_bulanan', $request->ids)->delete();

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    public function destroy(DistribusiBulanan $distribusi_bulanan)
    {
        $distribusi_bulanan->delete();
        return back()->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    public function searchPetugas(Request $request)
    {
        $request->validate([
            'field' => 'required|in:pencacah,pengawas',
            'query' => 'nullable|string|max:100',
        ]);

        $field = $request->input('field');
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
        $validJenis = ['vhts', 'hkd', 'shpb', 'shp', 'shpj', 'shpbg'];
        if (!in_array(strtolower($jenisKegiatan), $validJenis)) {
            abort(404);
        }

        $dataRange = $request->input('dataRange', 'all');
        $dataFormat = $request->input('dataFormat');
        $exportFormat = $request->input('exportFormat');
        $kegiatan = $request->input('kegiatan');
        $search = $request->input('search');
        $tahun = $request->input('tahun', date('Y'));
        $currentPage = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        // Kirim semua parameter ke export class
        $exportClass = new DistribusiBulananExport(
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
            return Excel::download($exportClass, 'DistribusiBulanan_' . strtoupper($jenisKegiatan) . '_' . date('Ymd_His') . '.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, 'DistribusiBulanan_' . strtoupper($jenisKegiatan) . '_' . date('Ymd_His') . '.csv');
        } elseif ($exportFormat == 'word') {
            return $exportClass->exportToWord();
        }

        return back()->with('error', 'Format ekspor tidak didukung.');
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048'
        ]);
        try {
            $import = new DistribusiBulananImport();
            Excel::import($import, $request->file('file'));
            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();
            $formattedErrors = array_map(function ($err) {
                return ['error' => $err['error']];
            }, $errors);
            if ($successCount > 0 && count($errors) > 0) {
                return redirect()->back()
                    ->with('import_errors', $formattedErrors)
                    ->with('success', "{$successCount} data berhasil diimport");
            } elseif ($successCount === 0 && count($errors) > 0) {
                return redirect()->back()
                    ->with('import_errors', $formattedErrors)
                    ->with('error', 'Semua data gagal diimport');
            }
            return redirect()->back()
                ->with('success', "Import berhasil! Total {$successCount} data ditambahkan");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['Nama Kegiatan', 'BS Responden', 'Pencacah', 'Pengawas', 'Target Penyelesaian', 'Flag Progress', 'Tanggal Pengumpulan'];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9EAD3');

        // Sample data
        $sheet->setCellValue('A2', 'SPUNP/SHKK');
        $sheet->setCellValue('B2', 'BS001');
        $sheet->setCellValue('C2', 'Ani Rahmawati');
        $sheet->setCellValue('D2', 'Budi Hariyadi');
        $sheet->setCellValue('E2', '2025-07-11');
        $sheet->setCellValue('F2', 'BELUM');
        $sheet->setCellValue('G2', '2025-06-14');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Petunjuk
        $sheet->setCellValue('A4', 'PETUNJUK:');
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->setCellValue('A5', '1. Semua kolom wajib diisi (tidak boleh kosong)');
        $sheet->setCellValue('A6', '2. Nama Kegiatan, Pencacah, Pengawas harus mengandung huruf');
        $sheet->setCellValue('A7', '3. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY');
        $sheet->setCellValue('A8', '4. Flag Progress hanya boleh: BELUM atau SELESAI');
        $sheet->setCellValue('A9', '5. Hapus baris sample sebelum upload');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Import_Distribusi_Bulanan.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }
}
