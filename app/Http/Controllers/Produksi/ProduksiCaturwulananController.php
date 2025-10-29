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
use App\Imports\ProduksiCaturwulananImport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProduksiCaturwulananController extends Controller
{
    public function index(Request $request, $jenisKegiatan)
    {
        $jenisKegiatan = strtolower(urldecode($jenisKegiatan));
        // 1. Validasi jenis kegiatan
        $validJenis = ['ubinan', 'updating utp'];
        $jenisKegiatanLower = strtolower($jenisKegiatan);
        if (!in_array($jenisKegiatanLower, $validJenis)) {
            abort(404);
        }
        $prefixKegiatan = strtoupper($jenisKegiatan); 

        // 2. Logika Filter Tahun (Konsisten)
        $selectedTahun = $request->input('tahun', date('Y'));
        $availableTahun = ProduksiCaturwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()->whereNotNull('created_at')->orderBy('tahun', 'desc')
            ->pluck('tahun')->toArray();
        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // 3. Kueri Utama
        $query = ProduksiCaturwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') 
            ->whereYear('created_at', $selectedTahun); 

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
        $listData = $query->latest('id_produksi_caturwulanan')->paginate($perPage)->withQueryString();

        // 6. Logika Hitung Tab
        $kegiatanCounts = ProduksiCaturwulanan::query()
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
        return view('timProduksi.ProduksiCaturwulanan', compact(
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

        ProduksiCaturwulanan::create($validatedData);

        session()->flash('success', 'Data berhasil ditambahkan!');
        session()->flash('auto_hide', true);
        // ========================================================

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back(); // Flash sudah di-set di atas
    }

    /**
     * Ambil data untuk modal edit ($id manual + format tanggal).
     */
    public function edit($id)
    {
        $produksi = ProduksiCaturwulanan::findOrFail($id);
        $data = $produksi->toArray();

        // Format tanggal ke Y-m-d
        $targetPenyelesaian = $produksi->target_penyelesaian;
        $tanggalPengumpulan = $produksi->tanggal_pengumpulan;
        $data['target_penyelesaian'] = $targetPenyelesaian ? Carbon::parse($targetPenyelesaian)->toDateString() : null;
        $data['tanggal_pengumpulan'] = $tanggalPengumpulan ? Carbon::parse($tanggalPengumpulan)->toDateString() : null;

        return response()->json($data);
    }

    /**
     * Update data ($id manual).
     */
    public function update(Request $request, $id)
    {
        $produksi = ProduksiCaturwulanan::findOrFail($id);
        $baseRules = [ // Sama seperti store, tambahkan exists
            'nama_kegiatan'       => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden'        => 'required|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'       => ['required', Rule::in(['Belum Selesai', 'Selesai'])],
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
            return back()->withErrors($validator)->withInput()
                ->with('error_modal', 'editDataModal')
                ->with('edit_id', $produksi->id_produksi_caturwulanan);
        }

        $validatedData = $validator->validated();
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        $produksi->update($validatedData);

        session()->flash('success', 'Data berhasil diperbarui!');
        session()->flash('auto_hide', true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        return back(); // Flash sudah di-set di atas
    }


    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:produksi_caturwulanan,id_produksi_caturwulanan'
        ]);

        ProduksiCaturwulanan::whereIn('id_produksi_caturwulanan', $request->ids)->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data yang dipilih berhasil dihapus!']);
        }
        
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }


    public function destroy(ProduksiCaturwulanan $produksi_caturwulanan, Request $request)
    {
        $produksi_caturwulanan->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data yang dipilih berhasil dihapus!']);
        }
        
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
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
    $validJenisKegiatan = [
        'ubinan padi',
        'ubinan',
        'updating utp',
        'ubinan padi palawija',
        'updating utp palawija',
        'UbinanPadiPalawija',
        'UbinanPadiPalawija-',
    ];
    
    // Normalize input (lowercase & trim)
    $jenisKegiatanNormalized = strtolower(trim($jenisKegiatan));
    
    if (!in_array($jenisKegiatanNormalized, $validJenisKegiatan)) {
        abort(404, 'Jenis kegiatan tidak valid: ' . $jenisKegiatan);
    }

    // Ambil semua parameter filter dari request
    $dataRange = $request->input('dataRange', 'all');
    $dataFormat = $request->input('dataFormat', 'formatted_values');
    $exportFormat = $request->input('exportFormat', 'excel');
    $kegiatan = $request->input('kegiatan');
    $search = $request->input('search');
    $currentPage = (int)$request->input('page', 1);
    $perPageInput = $request->input('per_page', 20);
    $selectedTahun = $request->input('tahun', date('Y'));

    // Tentukan perPage untuk query export
    $perPage = ($perPageInput == 'all' || $dataRange == 'all') ? 999999 : (int)$perPageInput;

    $exportClass = new ProduksiCaturwulananExport(
        $dataRange,
        $dataFormat,
        $jenisKegiatanNormalized,
        $kegiatan,
        $search,
        $selectedTahun,
        $currentPage,
        $perPage
    );

    $fileName = 'ProduksiCaturwulanan_' . str_replace(' ', '_', $jenisKegiatanNormalized) . '_' . $selectedTahun . '_' . now()->format('YmdHis');

    if ($exportFormat == 'excel') {
        return Excel::download($exportClass, $fileName . '.xlsx');
    } elseif ($exportFormat == 'csv') {
        return Excel::download($exportClass, $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV, [
            'Content-Type' => 'text/csv',
        ]);
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
            $import = new ProduksiCaturwulananImport();

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
            $fileName = 'Template_Import_Produksi_Caturwulanan_' . date('Ymd') . '.xlsx';
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
