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
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SosialTahunanExport;
use App\Imports\SosialTahunanImport;

class SosialTahunanController extends Controller
{
    public function index(Request $request)
    {
        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = SosialTahunan::query()
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->whereNotNull('created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        $query = SosialTahunan::query()
            ->whereYear('created_at', $selectedTahun);

        // Filter Kegiatan Spesifik (dari Tab)
        $selectedKegiatan = $request->input('kegiatan', ''); // Simpan untuk dikirim ke view
        if ($selectedKegiatan !== '') {
            $query->where('nama_kegiatan', $selectedKegiatan);
        }

        // Filter Pencarian
        $search = $request->input('search', ''); // Simpan untuk dikirim ke view
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('BS_Responden', 'like', "%{$search}%")
                    ->orWhere('pencacah', 'like', "%{$search}%")
                    ->orWhere('pengawas', 'like', "%{$search}%")
                    ->orWhere('nama_kegiatan', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        // Pastikan primary key 'id_sosial' benar
        $listData = $query->latest('id_sosial')->paginate($perPage)->withQueryString();

        $kegiatanCounts = SosialTahunan::query()
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        return view('timSosial.tahunan.sosialtahunan', compact(
            'listData',
            'kegiatanCounts',
            'masterKegiatanList',
            'availableTahun',
            'selectedTahun',
            'selectedKegiatan', // Kirim ini ke view
            'search'           // Kirim ini ke view
        ));
    }

    /**
     * Simpan data baru.
     */
    public function store(Request $request)
    {
        $baseRules = [
            'nama_kegiatan'       => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden'        => 'nullable|string|max:255', // Sesuai controller asli
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])], // Sesuai controller asli
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

        SosialTahunan::create($validatedData);

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
        // Pastikan primary key 'id_sosial' benar
        $sosial_tahunan = SosialTahunan::findOrFail($id);

        $data = $sosial_tahunan->toArray();

        // --- INI ADALAH LOGIKA YANG MEMPERBAIKI MASALAH ---
        $targetPenyelesaian = $sosial_tahunan->target_penyelesaian;
        $tanggalPengumpulan = $sosial_tahunan->tanggal_pengumpulan;

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
        // Pastikan primary key 'id_sosial' benar
        $sosial_tahunan = SosialTahunan::findOrFail($id);

        $baseRules = [
            'nama_kegiatan'       => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden'        => 'nullable|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [ /* ... sama seperti store ... */];
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
                // Pastikan primary key 'id_sosial' benar
                ->with('edit_id', $sosial_tahunan->id_sosial);
        }

        $validatedData = $validator->validated();

        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) {
            }
        }

        $sosial_tahunan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        // Redirect ke index setelah update sukses (sesuaikan nama route)
        return redirect()->route('sosial.tahunan.index')->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }

    public function destroy($id)
    {
        $sosial_tahunan = SosialTahunan::findOrFail($id);
        $sosial_tahunan->delete();

        return redirect()->route('sosial.tahunan.index')->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            // Pastikan primary key 'id_sosial' benar
            'ids.*' => 'exists:sosial_tahunan,id_sosial'
        ]);

        // Pastikan primary key 'id_sosial' benar
        SosialTahunan::whereIn('id_sosial', $request->ids)->delete();

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Cari petugas (autocomplete).
     */
    public function searchPetugas(Request $request)
    {
        // Hapus validasi 'field' karena tidak dipakai di Blade
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
    public function export(Request $request)
    {
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
        $exportClass = new SosialTahunanExport(
            $dataRange,
            $dataFormat,
            $kegiatan,
            $search,
            $tahun,
            $currentPage,
            $perPage
        );
        // Generate nama file
        $fileName = 'Sosial_Tahunan';
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
     public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048'
        ]);
        try {
            $import = new SosialTahunanImport();
            Excel::import($import, $request->file('file'));
            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();
            if (count($errors) > 0) {
                // Ada error, tapi tetap ada yang berhasil
                $formattedErrors = array_map(function ($error) {
                    return ['error' => $error['error']];
                }, $errors);
                if ($successCount > 0) {
                    return redirect()->back()
                        ->with('import_errors', $formattedErrors)
                        ->with('success', "{$successCount} data berhasil diimport");
                } else {
                    return redirect()->back()
                        ->with('import_errors', $formattedErrors)
                        ->with('error', 'Semua data gagal diimport');
                }
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

        // Style header
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD9EAD3');

        // Sample data
        $sheet->setCellValue('A2', 'Survey Penduduk 2025');
        $sheet->setCellValue('B2', 'BS001');
        $sheet->setCellValue('C2', 'Ahmad Hasan');
        $sheet->setCellValue('D2', 'Budi Santoso');
        $sheet->setCellValue('E2', '2025-12-31');
        $sheet->setCellValue('F2', 'BELUM');
        $sheet->setCellValue('G2', '2025-11-15');

        // Auto width
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Instructions
        $sheet->setCellValue('A4', 'PETUNJUK PENGISIAN:');
        $sheet->getStyle('A4')->getFont()->setBold(true);
        $sheet->setCellValue('A5', '1. Semua kolom WAJIB diisi (tidak boleh kosong)');
        $sheet->setCellValue('A6', '2. Nama Kegiatan, Pencacah, Pengawas harus berisi huruf (tidak boleh hanya angka)');
        $sheet->setCellValue('A7', '3. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY (contoh: 2025-12-31 atau 31/12/2025)');
        $sheet->setCellValue('A8', '4. Flag Progress hanya boleh: BELUM atau SELESAI');
        $sheet->setCellValue('A9', '5. Hapus baris contoh dan petunjuk ini sebelum import');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $fileName = 'Template_Import_Sosial_Tahunan.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }
    /**
     * ✅ Generate template Excel
     */
    private function generateTemplate()
    {
        // Ambil contoh nama kegiatan dari master
        $contohKegiatan = MasterKegiatan::where('nama_kegiatan', 'LIKE', 'SPAK%')
            ->orWhere('nama_kegiatan', 'LIKE', 'Podes%')
            ->limit(2)
            ->pluck('nama_kegiatan')
            ->toArray();
        // Ambil contoh petugas dari master
        $contohPetugas = MasterPetugas::limit(3)->pluck('nama_petugas')->toArray();
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // Set header
        $headers = [
            'A1' => 'Nama Kegiatan',
            'B1' => 'BS Responden',
            'C1' => 'Pencacah',
            'D1' => 'Pengawas',
            'E1' => 'Target Penyelesaian',
            'F1' => 'Flag Progress',
            'G1' => 'Tanggal Pengumpulan'
        ];
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE0E0E0');
        }
        // Tambahkan contoh data
        $exampleData = [
            [
                $contohKegiatan[0] ?? 'SPAK',
                '030001B',
                $contohPetugas[0] ?? 'Saryanto',
                $contohPetugas[1] ?? 'Fauzie',
                '20/02/2024',
                'Belum Mulai',
                ''
            ],
            [
                $contohKegiatan[1] ?? 'Podes',
                '030002C',
                $contohPetugas[1] ?? 'Budi',
                $contohPetugas[2] ?? 'Andi',
                '25/03/2024',
                'Proses',
                '15/03/2024'
            ]
        ];
        $row = 2;
        foreach ($exampleData as $data) {
            $col = 'A';
            foreach ($data as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }
        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Tambahkan komentar/instruksi
        $sheet->getComment('A1')->getText()->createText('Nama kegiatan harus sesuai dengan Master Kegiatan');
        $sheet->getComment('C1')->getText()->createText('Nama pencacah harus sesuai dengan Master Petugas');
        $sheet->getComment('D1')->getText()->createText('Nama pengawas harus sesuai dengan Master Petugas');
        $sheet->getComment('E1')->getText()->createText('Format: DD/MM/YYYY atau YYYY-MM-DD');
        $sheet->getComment('F1')->getText()->createText('Isi dengan: Belum Mulai / Proses / Selesai');
        // Simpan ke temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'template_');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);
        return $tempFile;
    }

}
