<?php

namespace App\Http\Controllers\Sosial;

use App\Http\Controllers\Controller;
use App\Models\Sosial\SosialTriwulanan; // Pastikan model ini ada
use Illuminate\Http\Request;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SosialTriwulananExport;
use App\Imports\SosialTriwulananImport;

class SosialTriwulanController extends Controller
{
    public function index(Request $request, $jenisKegiatan = 'seruti') // Default ke seruti jika tidak ada
    {
        // 1. Validasi jenis kegiatan (opsional, sesuaikan jika ada jenis lain)
        $validJenis = ['seruti']; // Hanya 'seruti' untuk saat ini
        if (!in_array(strtolower($jenisKegiatan), $validJenis)) {
            abort(404);
        }
        $prefixKegiatan = 'Seruti'; // Awalan nama kegiatan untuk query LIKE

        // 2. Logika Filter Tahun (konsisten dengan controller lain)
        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Filter berdasarkan jenis kegiatan
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->whereNotNull('created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // 3. Kueri Utama
        $query = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Filter utama berdasarkan jenis
            ->whereYear('created_at', $selectedTahun);

        // Filter Kegiatan Spesifik (dari Tab, misal: Seruti-TW1, Seruti-TW2)
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
                    ->orWhere('nama_kegiatan', 'like', "%{$search}%") // Bisa cari nama kegiatan spesifik
                    ->orWhere('flag_progress', 'like', "%{$search}%");
            });
        }

        // 4. Logika Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        // 5. Ambil Data (Gunakan primary key 'id_sosial_triwulanan')
        $listData = $query->latest('id_sosial_triwulanan')->paginate($perPage)->withQueryString();

        // 6. Logika Hitung Tab (misal: group by Seruti-TW1, Seruti-TW2, dst.)
        $kegiatanCounts = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Filter jenis
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan') // Urutkan TW1, TW2, ...
            ->get();

        $masterKegiatanList = MasterKegiatan::where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Hanya tampilkan master Seruti
            ->orderBy('nama_kegiatan')->get();

        // 7. Kirim ke View BARU
        return view('timSosial.triwulanan.sosialTriwulanan', compact( // Path view baru
            'listData',
            'kegiatanCounts',
            'jenisKegiatan',      // Kirim jenis ('seruti')
            'masterKegiatanList', // Untuk autocomplete tambah/edit
            'availableTahun',
            'selectedTahun',
            'selectedKegiatan',   // Untuk menandai tab aktif
            'search'              // Untuk mengisi kolom search
        ));
    }

    /**
     * Simpan data baru (AJAX ready).
     */
    public function store(Request $request)
    {
        $baseRules = [
            'nama_kegiatan'       => ['required', 'string', 'max:255', 'exists:master_kegiatan,nama_kegiatan', 'regex:/^Seruti-(TW1|TW2|TW3|TW4)/'], // Tetap pakai regex + exists
            'BS_Responden'        => 'nullable|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'nullable|date', // Diubah jadi nullable seperti NWA & Produksi
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan Seruti tidak terdaftar di master.',
            'nama_kegiatan.regex'  => 'Format Nama Kegiatan harus Seruti-TWx (x=1-4).',
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

        // Tambahkan tahun_kegiatan jika ada kolomnya
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) {
            }
        }

        SosialTriwulanan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data Seruti berhasil ditambahkan!']);
        }
        return back()->with(['success' => 'Data Seruti berhasil ditambahkan!', 'auto_hide' => true]);
    }

    /**
     * Ambil data untuk modal edit (AJAX ready).
     * Menggunakan $id manual.
     */
    public function edit($id)
    {
        // Pastikan primary key 'id_sosial_triwulanan' benar
        $sosial_triwulanan = SosialTriwulanan::findOrFail($id);

        $data = $sosial_triwulanan->toArray();

        // Format tanggal untuk input type="date"
        $targetPenyelesaian = $sosial_triwulanan->target_penyelesaian;
        $tanggalPengumpulan = $sosial_triwulanan->tanggal_pengumpulan;

        $data['target_penyelesaian'] = $targetPenyelesaian
            ? Carbon::parse($targetPenyelesaian)->toDateString()
            : null;

        $data['tanggal_pengumpulan'] = $tanggalPengumpulan
            ? Carbon::parse($tanggalPengumpulan)->toDateString()
            : null;

        return response()->json($data);
    }

    /**
     * Update data yang ada (AJAX ready).
     * Menggunakan $id manual.
     */
    public function update(Request $request, $id)
    {
        // Pastikan primary key 'id_sosial_triwulanan' benar
        $sosial_triwulanan = SosialTriwulanan::findOrFail($id);

        $baseRules = [ // Sama seperti store
            'nama_kegiatan'       => ['required', 'string', 'max:255', 'exists:master_kegiatan,nama_kegiatan', 'regex:/^Seruti-(TW1|TW2|TW3|TW4)/'],
            'BS_Responden'        => 'nullable|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'nullable|date',
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
                // Pastikan primary key 'id_sosial_triwulanan' benar
                ->with('edit_id', $sosial_triwulanan->id_sosial_triwulanan);
        }

        $validatedData = $validator->validated();

        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) {
            }
        }

        $sosial_triwulanan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data Seruti berhasil diperbarui!']);
        }
        // Redirect ke index dengan filter yang relevan (misal: TW dari nama kegiatan)
        $tw = $this->extractTW($validatedData['nama_kegiatan']);
        // Ganti nama route ke index yang baru
        return redirect()->route('sosial.triwulanan.index', ['jenisKegiatan' => 'seruti', /* 'tw' => $tw, */ 'tahun' => $selectedTahun ?? date('Y')])
            ->with(['success' => 'Data Seruti berhasil diperbarui!', 'auto_hide' => true]);
    }

    /**
     * Hapus satu data (AJAX ready).
     * Menggunakan $id manual.
     */
    public function destroy($id)
    {
        // Pastikan primary key 'id_sosial_triwulanan' benar
        $sosial_triwulanan = SosialTriwulanan::findOrFail($id);
        $namaKegiatan = $sosial_triwulanan->nama_kegiatan; // Simpan nama sebelum dihapus
        $sosial_triwulanan->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => 'Data Seruti berhasil dihapus!']);
        }

        // Redirect ke index dengan filter yang relevan
        $tw = $this->extractTW($namaKegiatan);
        // Ganti nama route ke index yang baru
        return redirect()->route('sosial.triwulanan.index', ['jenisKegiatan' => 'seruti', /* 'tw' => $tw */ 'tahun' => session('selected_tahun', date('Y'))])
            ->with(['success' => 'Data Seruti berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Hapus banyak data.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            // Pastikan primary key 'id_sosial_triwulanan' benar
            'ids.*' => 'exists:sosial_triwulanan,id_sosial_triwulanan'
        ]);

        // Pastikan primary key 'id_sosial_triwulanan' benar
        SosialTriwulanan::whereIn('id_sosial_triwulanan', $request->ids)->delete();

        return back()->with(['success' => 'Data Seruti yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Cari petugas (autocomplete). (Copy dari controller lain)
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
     * Cari kegiatan Seruti (autocomplete). (Copy dari controller lain)
     */
    public function searchKegiatan(Request $request)
    {
        $request->validate(['query' => 'nullable|string|max:100']);
        $query = $request->input('query', '');

        $data = MasterKegiatan::query()
            ->where('nama_kegiatan', 'LIKE', "Seruti%") // Filter hanya Seruti
            ->where('nama_kegiatan', 'LIKE', "%{$query}%")
            ->limit(10)
            ->pluck('nama_kegiatan');

        return response()->json($data);
    }

    private function extractTW(string $nama): string
    {
        return preg_match('/Seruti\-(TW[1-4])/', $nama, $m) ? $m[1] : 'TW1';
    }

    public function export(Request $request, $jenisKegiatan = 'seruti')
    {
        // Validasi jenis kegiatan
        $validJenis = ['seruti'];
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
        $exportClass = new SosialTriwulananExport(
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
        $fileName = 'Sosial_Triwulanan_Seruti';
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
            $import = new SosialTriwulananImport();
            Excel::import($import, $request->file('file'));
            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();
            if (count($errors) > 0) {
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
    /**
     * Download template Excel untuk import
     */
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
            ->getStartColor()->setARGB('FFFFD966'); // Warna kuning untuk Triwulanan
        // Sample data
        $sheet->setCellValue('A2', 'Survey Triwulan I 2025');
        $sheet->setCellValue('B2', 'BS001');
        $sheet->setCellValue('C2', 'Ahmad Hasan');
        $sheet->setCellValue('D2', 'Budi Santoso');
        $sheet->setCellValue('E2', '2025-03-31');
        $sheet->setCellValue('F2', 'BELUM');
        $sheet->setCellValue('G2', '2025-03-15');
        $sheet->setCellValue('A3', 'Survey Triwulan II 2025');
        $sheet->setCellValue('B3', 'BS002');
        $sheet->setCellValue('C3', 'Siti Aminah');
        $sheet->setCellValue('D3', 'Eko Prasetyo');
        $sheet->setCellValue('E3', '2025-06-30');
        $sheet->setCellValue('F3', 'SELESAI');
        $sheet->setCellValue('G3', '2025-06-25');
        // Auto width
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        // Instructions
        $sheet->setCellValue('A5', 'PETUNJUK PENGISIAN:');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A5')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2EFDA');

        $sheet->setCellValue('A6', '1. Semua kolom WAJIB diisi (tidak boleh kosong)');
        $sheet->setCellValue('A7', '2. Nama Kegiatan, Pencacah, Pengawas harus berisi huruf (tidak boleh hanya angka)');
        $sheet->setCellValue('A8', '3. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY (contoh: 2025-03-31 atau 31/03/2025)');
        $sheet->setCellValue('A9', '4. Flag Progress hanya boleh: BELUM atau SELESAI');
        $sheet->setCellValue('A10', '5. Hapus baris contoh dan petunjuk ini sebelum import');
        $sheet->setCellValue('A11', '6. Triwulan I (Jan-Mar), Triwulan II (Apr-Jun), Triwulan III (Jul-Sep), Triwulan IV (Okt-Des)');
        // Style instructions
        foreach (range(6, 11) as $row) {
            $sheet->getStyle("A{$row}")->getAlignment()->setWrapText(true);
        }
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $fileName = 'Template_Import_Sosial_Triwulanan.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }
    
}
