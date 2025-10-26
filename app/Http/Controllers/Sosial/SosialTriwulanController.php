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
use App\Exports\SosialTriwulananExport; // Pastikan file ini ada/dibuat
use App\Imports\SosialTriwulananImport; // Pastikan file ini ada/dibuat

class SosialTriwulanController extends Controller
{
    public function index(Request $request, $jenisKegiatan = 'seruti')
    {
        $validJenis = ['seruti'];
        if (!in_array(strtolower($jenisKegiatan), $validJenis)) {
            abort(404);
        }
        $prefixKegiatan = 'Seruti';

        $selectedTahun = $request->input('tahun', date('Y'));
        $availableTahun = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()->whereNotNull('created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')->toArray();

        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        $query = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
            ->whereYear('created_at', $selectedTahun);

        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            $query->where('nama_kegiatan', $selectedKegiatan);
        }

        $search = $request->input('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('BS_Responden', 'like', "%{$search}%")
                    ->orWhere('pencacah', 'like', "%{$search}%")
                    ->orWhere('pengawas', 'like', "%{$search}%")
                    ->orWhere('nama_kegiatan', 'like', "%{$search}%");
                // Menghapus search by flag_progress agar konsisten
            });
        }

        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        $listData = $query->latest('id_sosial_triwulanan')->paginate($perPage)->withQueryString();

        $kegiatanCounts = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
            ->orderBy('nama_kegiatan')->get();

        return view('timSosial.triwulanan.sosialTriwulanan', compact(
            'listData', 'kegiatanCounts', 'jenisKegiatan', 'masterKegiatanList',
            'availableTahun', 'selectedTahun', 'selectedKegiatan', 'search'
        ));
    }

    public function store(Request $request)
    {
        // [FIX] Disesuaikan dengan Produksi
        $baseRules = [
            'nama_kegiatan'       => ['required', 'string', 'max:255', 'exists:master_kegiatan,nama_kegiatan', 'regex:/^Seruti-(TW1|TW2|TW3|TW4)/'],
            'BS_Responden'        => 'required|string|max:255', // Jadi required
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date', // Jadi required
            'flag_progress'       => ['required', Rule::in(['Belum Selesai', 'Selesai'])], // Disesuaikan
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
                return response()->json(['message' => 'Data yang diberikan tidak valid.', 'errors' => $validator->errors()], 422);
            }
            // [FIX] Tambah error bag 'tambahForm'
            return back()->withErrors($validator, 'tambahForm')->withInput()->with('error_modal', 'tambahDataModal');
        }

        $validatedData = $validator->validated();
        if ($request->filled('target_penyelesaian')) {
            try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }
        SosialTriwulanan::create($validatedData);

        // [FIX] Set session flash SEBELUM return JSON
        $request->session()->flash('success', 'Data Seruti berhasil ditambahkan!');
        $request->session()->flash('auto_hide', true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data Seruti berhasil ditambahkan!']);
        }
        return back()->with(['success' => 'Data Seruti berhasil ditambahkan!', 'auto_hide' => true]);
    }

    public function edit($id)
    {
        $sosial_triwulanan = SosialTriwulanan::findOrFail($id);
        $data = $sosial_triwulanan->toArray();
        $targetPenyelesaian = $sosial_triwulanan->target_penyelesaian;
        $tanggalPengumpulan = $sosial_triwulanan->tanggal_pengumpulan;
        $data['target_penyelesaian'] = $targetPenyelesaian ? Carbon::parse($targetPenyelesaian)->toDateString() : null;
        $data['tanggal_pengumpulan'] = $tanggalPengumpulan ? Carbon::parse($tanggalPengumpulan)->toDateString() : null;
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $sosial_triwulanan = SosialTriwulanan::findOrFail($id);

        // [FIX] Disesuaikan dengan Produksi
        $baseRules = [
            'nama_kegiatan'       => ['required', 'string', 'max:255', 'exists:master_kegiatan,nama_kegiatan', 'regex:/^Seruti-(TW1|TW2|TW3|TW4)/'],
            'BS_Responden'        => 'required|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'       => ['required', Rule::in(['Belum Selesai', 'Selesai'])], // Disesuaikan
            'tanggal_pengumpulan' => 'nullable|date',
        ];
        $customMessages = [ /* ... sama seperti store ... */ ];
        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
            }
             // [FIX] Tambah error bag 'editForm'
            return back()->withErrors($validator, 'editForm')->withInput()
                ->with('error_modal', 'editDataModal')
                ->with('edit_id', $sosial_triwulanan->id_sosial_triwulanan);
        }

        $validatedData = $validator->validated();
        if ($request->filled('target_penyelesaian')) {
            try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }
        $sosial_triwulanan->update($validatedData);

        // [FIX] Set session flash SEBELUM return JSON
        $request->session()->flash('success', 'Data Seruti berhasil diperbarui!');
        $request->session()->flash('auto_hide', true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data Seruti berhasil diperbarui!']);
        }
        
        // [FIX] Ganti redirect() menjadi back()
        return back()->with(['success' => 'Data Seruti berhasil diperbarui!', 'auto_hide' => true]);
    }

    public function destroy($id)
    {
        $sosial_triwulanan = SosialTriwulanan::findOrFail($id);
        $sosial_triwulanan->delete();

        // [FIX] Ganti redirect() menjadi back()
        return back()->with(['success' => 'Data Seruti berhasil dihapus!', 'auto_hide' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'exists:sosial_triwulanan,id_sosial_triwulanan']);
        SosialTriwulanan::whereIn('id_sosial_triwulanan', $request->ids)->delete();
        return back()->with(['success' => 'Data Seruti yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    public function searchPetugas(Request $request)
    {
        $request->validate(['query' => 'nullable|string|max:100']);
        $query = $request->input('query', '');
        $data = MasterPetugas::where('nama_petugas', 'LIKE', "%{$query}%")->limit(10)->pluck('nama_petugas');
        return response()->json($data);
    }

     public function searchKegiatan(Request $request)
     {
         $request->validate(['query' => 'nullable|string|max:100']);
         $query = $request->input('query', '');
         $data = MasterKegiatan::query()
            ->where('nama_kegiatan', 'LIKE', "Seruti%")
            ->where('nama_kegiatan', 'LIKE', "%{$query}%")
            ->limit(10)->pluck('nama_kegiatan');
         return response()->json($data);
     }

    public function export(Request $request, $jenisKegiatan = 'seruti')
    {
        $request->validate([
            'dataRange' => 'required|in:all,current_page',
            'exportFormat' => 'required|in:excel,csv'],
            ['exportFormat.in' => 'Format export tidak didukung.']
        );

        $dataRange = $request->input('dataRange', 'all');
        $exportFormat = $request->input('exportFormat');
        $kegiatan = $request->input('kegiatan');
        $search = $request->input('search');
        $tahun = $request->input('tahun', date('Y'));
        $currentPage = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);

        // [FIX] Gunakan SosialTriwulananExport dan dataFormat diisi null
        $exportClass = new SosialTriwulananExport(
            $dataRange,
            null, // dataFormat (argumen ke-2) tidak dipakai
            $jenisKegiatan, // jenisKegiatan (argumen ke-3)
            $kegiatan,
            $search,
            $tahun,
            $currentPage,
            $perPage
        );

        $fileName = 'Sosial_Triwulanan_Seruti_' . $tahun . '_' . date('YmdHis');
        if (!empty($kegiatan)) { $fileName .= '_' . str_replace([' ', '/'], '_', $kegiatan); }
        
        if ($exportFormat == 'excel') {
            return Excel::download($exportClass, $fileName . '.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, $fileName . '.csv');
        }
        return back()->with('error', 'Format ekspor tidak didukung.');
    }

     public function import(Request $request)
     {
         $request->validate(['file' => 'required|mimes:xlsx,xls|max:2048'], [ /* messages */ ]);
         try {
             $import = new SosialTriwulananImport(); // [FIX] Gunakan Importer yang benar
             Excel::import($import, $request->file('file'));
             $errors = $import->getErrors();
             $successCount = $import->getSuccessCount();
             $formattedErrors = [];
             foreach ($errors as $error) {
                 $formattedErrors[] = [
                    'row' => $error['row'] ?? '?',
                    'error' => $error['error'] ?? 'Unknown error',
                    'values' => isset($error['values']) ? implode(', ', $error['values']) : 'N/A'
                 ];
             }
             if (count($formattedErrors) > 0) {
                  $message = $successCount > 0 ? "{$successCount} data berhasil diimport, tetapi ada beberapa baris yang gagal." : 'Semua data gagal diimport. Periksa error di bawah.';
                  return redirect()->back()->with('import_errors', $formattedErrors)->with($successCount > 0 ? 'warning' : 'error', $message);
             }
             return redirect()->back()->with(['success' => "Import berhasil! Total {$successCount} data ditambahkan.", 'auto_hide' => true]);
         } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
              $failures = $e->failures(); $errors = [];
              foreach ($failures as $failure) { $errors[] = [ 'row' => $failure->row(), 'error' => implode(', ', $failure->errors()), 'values' => implode(', ', $failure->values() ?? []) ]; }
              return back()->with('import_errors', $errors)->with('error', 'Validasi gagal pada beberapa baris.');
         } catch (\Exception $e) {
            \Log::error('Import Error (SosialTriwulanan): ' . $e->getMessage());
            return redirect()->back()->with('error', 'Import gagal: ' . $e->getMessage());
         }
     }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // [FIX] Header harus lowercase dan underscore
            $headers = ['nama_kegiatan', 'bs_responden', 'pencacah', 'pengawas', 'target_penyelesaian', 'flag_progress', 'tanggal_pengumpulan'];
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);
            $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAD3'); // Hijau (konsisten)
            $sheet->getStyle('A1:G1')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

            // [FIX] Sample data disesuaikan
            $contohKegiatan = MasterKegiatan::where('nama_kegiatan', 'LIKE', 'Seruti%')->limit(2)->pluck('nama_kegiatan')->toArray();
            $contohPetugas = MasterPetugas::limit(3)->pluck('nama_petugas')->toArray();
            $exampleData = [
                [$contohKegiatan[0] ?? 'Seruti-TW1', 'BS001', $contohPetugas[0] ?? 'Ahmad', $contohPetugas[1] ?? 'Budi', '2025-03-31', 'Belum Selesai', '2025-03-15'],
                [$contohKegiatan[1] ?? 'Seruti-TW2', 'BS002', $contohPetugas[2] ?? 'Siti', $contohPetugas[0] ?? 'Ahmad', '2025-06-30', 'Selesai', '2025-06-20']
            ];
             $row = 2;
             foreach ($exampleData as $data) { $sheet->fromArray([$data], null, 'A' . $row); $row++; }
            $sheet->getStyle('A2:G' . ($row - 1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF4CC');
            foreach (range('A', 'G') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

            // [FIX] Petunjuk disesuaikan
            $sheet->setCellValue('A' . $row, 'PETUNJUK PENGISIAN:');
            $sheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFB4C7E7');
            $instructionRow = $row + 1;
            $instructions = [
                 '1. Kolom Nama Kegiatan, BS Responden, Pencacah, Pengawas, Target Penyelesaian, Flag Progress WAJIB diisi.',
                 '2. Header baris 1 HARUS tetap ada (nama_kegiatan, bs_responden, dst).',
                 '3. Nama Kegiatan (format Seruti-TWx), Pencacah, Pengawas harus terdaftar di Master Data.',
                 '4. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY.',
                 '5. Flag Progress hanya boleh diisi: "Belum Selesai" atau "Selesai" (case-sensitive).',
                 '6. HAPUS baris contoh (baris 2-3) dan petunjuk ini sebelum import!',
            ];
             foreach ($instructions as $instruction) { $sheet->setCellValue('A' . $instructionRow, $instruction); $sheet->getStyle('A' . $instructionRow)->getFont()->setItalic(true); $sheet->mergeCells("A{$instructionRow}:G{$instructionRow}"); $instructionRow++; }
             $sheet->mergeCells("A{$row}:G{$row}");

            // [FIX] Komentar disesuaikan
            $sheet->getComment('A1')->getText()->createTextRun('WAJIB: Isi (contoh: Seruti-TW1) sesuai Master Kegiatan');
            $sheet->getComment('B1')->getText()->createTextRun('WAJIB: Kode BS Responden');
            $sheet->getComment('C1')->getText()->createTextRun('WAJIB: Nama Pencacah sesuai Master Petugas');
            $sheet->getComment('D1')->getText()->createTextRun('WAJIB: Nama Pengawas sesuai Master Petugas');
            $sheet->getComment('E1')->getText()->createTextRun('WAJIB: Format: YYYY-MM-DD');
            $sheet->getComment('F1')->getText()->createTextRun('WAJIB: Isi: "Belum Selesai" atau "Selesai"');
            $sheet->getComment('G1')->getText()->createTextRun('OPSIONAL: Format: YYYY-MM-DD');

            $sheet->freezePane('A2');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            // [FIX] Nama file disesuaikan
            $fileName = 'Template_Import_Sosial_Triwulanan_' . date('Ymd') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'template_sosial_tri_');
            $writer->save($tempFile);
            return response()->download($tempFile, $fileName, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat template: ' . $e->getMessage());
        }
    }
}