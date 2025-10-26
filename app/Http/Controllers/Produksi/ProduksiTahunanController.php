<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\Produksi\ProduksiTahunan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProduksiTahunanExport;
use App\Imports\ProduksiTahunanImport;

class ProduksiTahunanController extends Controller
{
    public function index(Request $request)
    {
        // 1. Logika Filter Tahun
        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = ProduksiTahunan::query()
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->whereNotNull('created_at') // Penting
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // 2. Kueri Utama
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

        // 3. Logika Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        // 4. Ambil Data (Gunakan primary key yang benar, misal: 'id_produksi')
        $listData = $query->latest('id_produksi')->paginate($perPage)->withQueryString();

        // 5. Logika Hitung Tab
        $kegiatanCounts = ProduksiTahunan::query()
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        // 6. Kirim ke View
        return view('timProduksi.produksiTahunan', compact(
            'listData',
            'kegiatanCounts',
            'masterKegiatanList',
            'availableTahun',
            'selectedTahun'
        ));
    }

    /**
     * Simpan data baru.
     */
    public function store(Request $request)
    {
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            // Sesuaikan Rule::in jika opsinya beda
            'flag_progress' => ['required', Rule::in(['Belum Selesai', 'Selesai'])],
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

        ProduksiTahunan::create($validatedData);

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
        // Ganti 'id_produksi' jika primary key-nya beda
        $produksi_tahunan = ProduksiTahunan::findOrFail($id);

        $data = $produksi_tahunan->toArray();

        // --- INI ADALAH LOGIKA YANG MEMPERBAIKI MASALAH ---
        $targetPenyelesaian = $produksi_tahunan->target_penyelesaian;
        $tanggalPengumpulan = $produksi_tahunan->tanggal_pengumpulan;

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
        // Ganti 'id_produksi' jika primary key-nya beda
        $produksi_tahunan = ProduksiTahunan::findOrFail($id);

        $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => ['required', Rule::in(['Belum Selesai', 'Selesai'])],
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
                // Ganti 'id_produksi' jika primary key-nya beda
                ->with('edit_id', $produksi_tahunan->id_produksi);
        }

        $validatedData = $validator->validated();

        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) {
            }
        }

        $produksi_tahunan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        return back()->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }

    /**
     * PERBAIKAN KUNCI:
     * 1. Ambil $id manual.
     */
    public function destroy($id)
    {
        // Ganti 'id_produksi' jika primary key-nya beda
        $produksi_tahunan = ProduksiTahunan::findOrFail($id);
        $produksi_tahunan->delete();

        return back()->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Hapus banyak data.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            // Ganti 'id_produksi' jika primary key-nya beda
            'ids.*' => 'exists:produksi_tahunan,id_produksi'
        ]);

        // Ganti 'id_produksi' jika primary key-nya beda
        ProduksiTahunan::whereIn('id_produksi', $request->ids)->delete();

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Cari petugas (autocomplete).
     */
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

    public function export(Request $request)
    {
        $dataRange = $request->input('dataRange', 'all');
        $exportFormat = $request->input('exportFormat');

        $tahun = $request->input('tahun', date('Y'));
        $kegiatan = $request->input('kegiatan');
        $search = $request->input('search');

        $currentPage = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        if (!in_array($exportFormat, ['excel', 'csv', 'word'])) {
            return back()->with('error', 'Format export tidak valid!');
        }
        $exportClass = new ProduksiTahunanExport(
            $dataRange,
            null,
            $tahun,
            $kegiatan,
            $search,
            $currentPage,
            $perPage
        );
        $fileName = 'ProduksiTahunan_' . $tahun . '_' . date('YmdHis');
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
            'file' => 'required|mimes:xlsx,xls|max:2048', // Max 2MB
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls).',
            'file.max' => 'Ukuran file maksimal 2MB.',
        ]);
        try {
            $file = $request->file('file');

            // Buat instance import
            $import = new ProduksiTahunanImport();

            // Import file
            Excel::import($import, $file);
            // Ambil hasil import
            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();
            // Jika ada error, kirim ke session
            if (!empty($errors)) {
                return back()
                    ->with('import_errors', $errors)
                    ->with('success_count', $successCount)
                    ->with('warning', "Import selesai dengan {$successCount} data berhasil dan " . count($errors) . " data gagal. Lihat detail error di bawah.");
            }
            // Jika semua berhasil
            return back()->with([
                'success' => "Berhasil mengimpor {$successCount} data!",
                'auto_hide' => true
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            // ===== SET HEADER (Baris 1) =====
            $headers = [
                'nama_kegiatan',
                'bs_responden',
                'pencacah',
                'pengawas',
                'target_penyelesaian',
                'flag_progress',
                'tanggal_pengumpulan'
            ];

            $headerLabels = [
                'Nama Kegiatan',
                'BS Responden',
                'Pencacah',
                'Pengawas',
                'Target Penyelesaian',
                'Flag Progress',
                'Tanggal Pengumpulan'
            ];
            // Tulis header
            $sheet->fromArray([$headers], null, 'A1');

            // Style header (Bold + Background hijau muda)
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);
            $sheet->getStyle('A1:G1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD9EAD3'); // Hijau muda

            // Tambahkan border pada header
            $sheet->getStyle('A1:G1')->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            // ===== CONTOH DATA (Baris 2-3) =====
            // Ambil contoh dari database (opsional)
            $contohKegiatan = MasterKegiatan::limit(2)->pluck('nama_kegiatan')->toArray();
            $contohPetugas = MasterPetugas::limit(3)->pluck('nama_petugas')->toArray();
            $exampleData = [
                [
                    $contohKegiatan[0] ?? 'Sensus Penduduk 2025',
                    'BS001',
                    $contohPetugas[0] ?? 'Ahmad Zaki',
                    $contohPetugas[1] ?? 'Budi Santoso',
                    '2025-12-31',
                    'Belum Selesai',
                    '2025-11-15'
                ],
                [
                    $contohKegiatan[1] ?? 'Survey Ekonomi Q1',
                    'BS002',
                    $contohPetugas[2] ?? 'Siti Nurhaliza',
                    $contohPetugas[0] ?? 'Andi Wijaya',
                    '2025-06-30',
                    'Selesai',
                    '2025-06-20'
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
            // Style contoh data (background kuning muda)
            $sheet->getStyle('A2:G3')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFF4CC'); // Kuning muda
            // ===== AUTO WIDTH COLUMNS =====
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            // ===== PETUNJUK PENGISIAN (Baris 5-12) =====
            $sheet->setCellValue('A5', 'PETUNJUK PENGISIAN:');
            $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A5')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFB4C7E7'); // Biru muda
            $instructions = [
                '1. Semua kolom WAJIB diisi kecuali Tanggal Pengumpulan (boleh kosong)',
                '2. Header baris 1 HARUS tetap ada dengan format lowercase dan underscore',
                '3. Nama Kegiatan, Pencacah, Pengawas harus berisi huruf (tidak boleh hanya angka)',
                '4. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY (contoh: 2025-12-31 atau 31/12/2025)',
                '5. Flag Progress hanya boleh diisi: "Belum Selesai" atau "Selesai" (case-sensitive)',
                '6. BS Responden boleh berisi angka atau kombinasi huruf-angka',
                '7. HAPUS baris contoh (baris 2-3) dan petunjuk ini sebelum import!',
                '8. Simpan file dalam format .xlsx atau .xls'
            ];
            $instructionRow = 6;
            foreach ($instructions as $instruction) {
                $sheet->setCellValue('A' . $instructionRow, $instruction);
                $sheet->getStyle('A' . $instructionRow)->getFont()->setItalic(true);
                $instructionRow++;
            }
            // Merge cells untuk petunjuk agar lebih rapi
            foreach (range(5, 13) as $r) {
                $sheet->mergeCells("A{$r}:G{$r}");
            }
            // ===== TAMBAHKAN KOMENTAR/TOOLTIP PADA HEADER =====
            $sheet->getComment('A1')->getText()->createTextRun('Isi dengan nama kegiatan sesuai Master Kegiatan');
            $sheet->getComment('B1')->getText()->createTextRun('Kode BS Responden (contoh: BS001, 030001B)');
            $sheet->getComment('C1')->getText()->createTextRun('Nama Pencacah sesuai Master Petugas');
            $sheet->getComment('D1')->getText()->createTextRun('Nama Pengawas sesuai Master Petugas');
            $sheet->getComment('E1')->getText()->createTextRun('Format: YYYY-MM-DD atau DD/MM/YYYY (WAJIB diisi)');
            $sheet->getComment('F1')->getText()->createTextRun('Isi: "Belum Selesai" atau "Selesai" saja');
            $sheet->getComment('G1')->getText()->createTextRun('Format tanggal, boleh kosong jika belum ada');
            // ===== FREEZE HEADER ROW =====
            $sheet->freezePane('A2');
            // ===== SAVE TO TEMPORARY FILE =====
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'Template_Import_Produksi_Tahunan_' . date('Ymd') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'template_produksi_');

            $writer->save($tempFile);
            // Return download dan hapus file temporary setelah didownload
            return response()->download($tempFile, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat template: ' . $e->getMessage());
        }
    }
}
