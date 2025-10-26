<?php

namespace App\Http\Controllers\Distribusi;

use App\Http\Controllers\Controller;
use App\Models\Distribusi\DistribusiBulanan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
// use PhpOffice\PhpWord\TemplateProcessor; // Hapus jika tidak dipakai
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DistribusiBulananExport; // Pastikan ada
use Illuminate\Http\Request;
use App\Imports\DistribusiBulananImport; // Pastikan ada
use Illuminate\Validation\Rule;

class DistribusiBulananController extends Controller
{
    // ... (index method Anda sudah benar, tidak perlu diubah dari sebelumnya) ...
     public function index(Request $request, $jenisKegiatan)
    {
        $validJenis = ['vhts', 'hkd', 'shpb', 'shp', 'shpj', 'shpbg'];
        if (!in_array(strtolower($jenisKegiatan), $validJenis)) {
            abort(404);
        }
        $prefixKegiatan = strtoupper($jenisKegiatan); // Untuk filter LIKE

        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = DistribusiBulanan::query()
            ->where('nama_kegiatan', 'Like', $prefixKegiatan . '%') // Filter jenis
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->whereNotNull('created_at') // Pastikan created_at tidak null
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) { // Perbaiki logika check tahun
            array_unshift($availableTahun, date('Y'));
        }

        $query = DistribusiBulanan::query()
            ->where('nama_kegiatan', 'Like', $prefixKegiatan . '%') // Filter jenis
            ->whereYear('created_at', $selectedTahun); // Filter tahun

        $selectedKegiatan = $request->input('kegiatan', ''); // Default string kosong
        if ($selectedKegiatan !== '') {
            $query->where('nama_kegiatan', $selectedKegiatan);
        }

        $search = $request->input('search', ''); // Simpan juga search term
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('BS_Responden', 'like', "%{$search}%")
                    ->orWhere('pencacah', 'like', "%{$search}%")
                    ->orWhere('pengawas', 'like', "%{$search}%")
                    ->orWhere('nama_kegiatan', 'like', "%{$search}%");
            });
        }

        // --- PERBAIKAN FILTER PAGINATION 'ALL' ---
        $perPageInput = $request->input('per_page', 20);
        $perPage = $perPageInput;
        if ($perPageInput == 'all') {
            $total = (clone $query)->count(); // Hitung total sebelum pagination
            $perPage = $total > 0 ? $total : 20; // Set perPage ke total jika > 0
        }
        // -----------------------------------------

        $listData = $query->latest('id_distribusi_bulanan')->paginate($perPage)->withQueryString();

        $kegiatanCounts = DistribusiBulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Filter jenis
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                                            ->orderBy('nama_kegiatan')->get();

        return view('timDistribusi.distribusiBulanan', compact(
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


    public function store(Request $request)
    {
        // Validasi sudah benar dari kode Anda
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => ['required', Rule::in(['Belum Selesai', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date', // Sebaiknya nullable di backend
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
        if ($request->filled('target_penyelesaian')) {
           try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        DistribusiBulanan::create($validatedData);

        // ===== PERBAIKAN ALERT TAMBAH =====
        session()->flash('success', 'Data berhasil ditambahkan!');
        session()->flash('auto_hide', true);
        // ===================================

        if ($request->ajax() || $request->wantsJson()) {
            // Response JSON tetap dikirim untuk konfirmasi AJAX
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }

        return back(); // Flash message sudah diset untuk non-AJAX / reload
    }

    // edit method Anda sudah benar, tidak perlu diubah
    public function edit($id) // Ganti parameter binding jadi $id
    {
        $distribusi_bulanan = DistribusiBulanan::findOrFail($id); // Gunakan findOrFail
        $data = $distribusi_bulanan->toArray();
        $targetPenyelesaian = $distribusi_bulanan->target_penyelesaian;
        $tanggalPengumpulan = $distribusi_bulanan->tanggal_pengumpulan;
        $data['target_penyelesaian'] = $targetPenyelesaian ? Carbon::parse($targetPenyelesaian)->toDateString() : null;
        $data['tanggal_pengumpulan'] = $tanggalPengumpulan ? Carbon::parse($tanggalPengumpulan)->toDateString() : null;
        return response()->json($data);
    }


    public function update(Request $request, $id) // Ganti parameter binding jadi $id
    {
        $distribusi_bulanan = DistribusiBulanan::findOrFail($id); // Gunakan findOrFail
        // Validasi sudah benar dari kode Anda
         $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => ['required', Rule::in(['Belum Selesai', 'Selesai'])],
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
                return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
           }
            return back()->withErrors($validator)->withInput()
                ->with('error_modal', 'editDataModal')
                ->with('edit_id', $distribusi_bulanan->id_distribusi_bulanan);
        }

        $validatedData = $validator->validated();
         if ($request->filled('target_penyelesaian')) {
            try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        $distribusi_bulanan->update($validatedData);

        // ===== PERBAIKAN ALERT EDIT =====
        session()->flash('success', 'Data berhasil diperbarui!');
        session()->flash('auto_hide', true);
        // ================================

        if ($request->ajax() || $request->wantsJson()) {
             // Response JSON tetap dikirim untuk konfirmasi AJAX
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }

        return back(); // Flash message sudah diset untuk non-AJAX / reload
    }

    // ... (bulkDelete, destroy, searchPetugas, searchKegiatan sudah benar) ...
     public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:distribusi_bulanan,id_distribusi_bulanan' // Pastikan tabel benar
        ]);

        DistribusiBulanan::whereIn('id_distribusi_bulanan', $request->ids)->delete();

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    public function destroy($id) // Ganti parameter binding jadi $id
    {
        $distribusi_bulanan = DistribusiBulanan::findOrFail($id); // Gunakan findOrFail
        $distribusi_bulanan->delete();

        // Set flash message DULU, baru return
        session()->flash('success', 'Data berhasil dihapus!');
        session()->flash('auto_hide', true);

        if (request()->ajax() || request()->wantsJson()) {
             return response()->json(['success' => 'Data berhasil dihapus!']); // Cukup kirim success
        }

        return back(); // Redirect back akan menampilkan flash message
    }

     public function searchPetugas(Request $request)
    {
        $request->validate([
            // 'field' => 'required|in:pencacah,pengawas', // Hapus jika JS tidak pakai
            'query' => 'nullable|string|max:100',
        ]);
        $query = $request->input('query', '');
        $data = MasterPetugas::query()
            ->where('nama_petugas', 'LIKE', "%{$query}%")
            ->limit(10)
            ->pluck('nama_petugas');
        return response()->json($data);
    }

     public function searchKegiatan(Request $request, $jenisKegiatan = null)
     {
         $request->validate(['query' => 'nullable|string|max:100']);
         $query = $request->input('query', '');
         $kegiatanQuery = MasterKegiatan::query();

         if ($jenisKegiatan) {
              $validJenis = ['vhts', 'hkd', 'shpb', 'shp', 'shpj', 'shpbg'];
             if (in_array(strtolower($jenisKegiatan), $validJenis)) {
                 $kegiatanQuery->where('nama_kegiatan', 'LIKE', strtoupper($jenisKegiatan) . '%');
             }
         }

         $data = $kegiatanQuery
             ->where('nama_kegiatan', 'LIKE', "%{$query}%")
             ->limit(10)
             ->pluck('nama_kegiatan');
         return response()->json($data);
     }


    // ... (export, import, downloadTemplate) ...
      public function export(Request $request, $jenisKegiatan)
    {
        $validJenis = ['vhts', 'hkd', 'shpb', 'shp', 'shpj', 'shpbg'];
        if (!in_array(strtolower($jenisKegiatan), $validJenis)) {
            abort(404);
        }

        $dataRange = $request->input('dataRange', 'all');
        $dataFormat = $request->input('dataFormat', 'formatted_values');
        $exportFormat = $request->input('exportFormat', 'excel');
        $kegiatan = $request->input('kegiatan'); // Filter tab
        $search = $request->input('search');
        $tahun = $request->input('tahun', date('Y')); // Ambil tahun dari request
        $currentPage = $request->input('page', 1);
        $perPageInput = $request->input('per_page', 20);

        $perPage = ($perPageInput == 'all' || $dataRange == 'all') ? -1 : (int)$perPageInput;

        // Pastikan DistribusiBulananExport ada dan constructornya sesuai
        $exportClass = new DistribusiBulananExport(
            $dataRange,
            $dataFormat,
            $jenisKegiatan,
            $kegiatan,
            $search,
            $tahun, // Kirim tahun ke export class
            $currentPage,
            $perPage
        );

        $fileName = 'DistribusiBulanan_' . strtoupper($jenisKegiatan) . '_' . $tahun . '_' . now()->format('YmdHis');

        if ($exportFormat == 'excel') {
            return Excel::download($exportClass, $fileName . '.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV, [
                 'Content-Type' => 'text/csv',
             ]);
        }
        // elseif ($exportFormat == 'word') {
             // Pastikan DistribusiBulananExport punya method exportToWord
             // return $exportClass->exportToWord();
        // }

        return back()->with('error', 'Format ekspor tidak didukung.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048' // Tambahkan csv jika didukung
        ],[
            'file.required' => 'File Excel/CSV wajib diunggah.',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV (.csv).',
            'file.max' => 'Ukuran file maksimal 2MB.',
        ]);
        try {
            $file = $request->file('file');
            // Pastikan App\Imports\DistribusiBulananImport ada
            $import = new DistribusiBulananImport();
            Excel::import($import, $file);

            $errors = $import->getErrors(); // Panggil method getErrors() dari trait
            $successCount = $import->getSuccessCount(); // Panggil method getSuccessCount()

             if (!empty($errors)) {
                // Format error agar bisa ditampilkan di blade
                 $formattedErrors = collect($errors)->map(function ($err) {
                     return [
                         'row' => $err['row'] ?? '?',
                         'error' => $err['error'] ?? 'Unknown Error',
                         'values' => $err['values'] ?? 'N/A'
                     ];
                 })->toArray();

                return back()
                    ->with('import_errors', $formattedErrors) // Kirim error yang diformat
                    ->with('success_count', $successCount) // Kirim jumlah sukses (jika ada)
                    ->with('warning', "Import selesai dengan {$successCount} data berhasil dan " . count($errors) . " data gagal. Lihat detail error di bawah.");
            }

            // Jika tidak ada error
            return back()->with([
                'success' => "Berhasil mengimpor {$successCount} data!",
                'auto_hide' => true
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
             $formattedErrors = [];
             foreach ($failures as $failure) {
                 $formattedErrors[] = [
                    'row' => $failure->row(),
                    'error' => implode(', ', $failure->errors()), // Pesan error validasi
                    'values' => $failure->values()[$failure->attribute()] ?? 'N/A'
                 ];
             }
             return back()
                    ->with('import_errors', $formattedErrors)
                    ->with('error', 'Import gagal karena ada data yang tidak valid.');

        } catch (\Exception $e) {
            \Log::error('Import Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString()); // Log error detail
            return back()->with('error', 'Terjadi kesalahan sistem saat import: ' . $e->getMessage());
        }
    }

    // downloadTemplate sudah benar, tidak perlu diubah dari kode Anda
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
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);
            $sheet->getStyle('A1:G1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD9EAD3');

             $exampleData = [
                [
                    'VHTS-JANUARI 2025',
                    'BS001',
                    'Nama Pencacah Valid', // Ganti dengan nama valid
                    'Nama Pengawas Valid', // Ganti dengan nama valid
                    '2025-01-31',
                    'Belum',
                    '2025-01-20'
                ],
                [
                    'HKD-FEBRUARI 2025',
                    'BS002',
                    'Nama Pencacah Lain', // Ganti dengan nama valid
                    'Nama Pengawas Lain', // Ganti dengan nama valid
                    '2025-02-28',
                    'Selesai',
                    '2025-02-25'
                ]
            ];
            $sheet->fromArray($exampleData, null, 'A2');
             $sheet->getStyle('A2:G3')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFF4CC');


            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet->setCellValue('A5', 'PETUNJUK PENGISIAN:');
            $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A5')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFB4C7E7');

             $instructions = [
                '1. Header (Baris 1) WAJIB ada (format: lowercase_underscore).',
                '2. Semua kolom WAJIB diisi.',
                '3. Nama Kegiatan, Pencacah, Pengawas HARUS SAMA PERSIS dengan data di Master.',
                '4. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY.',
                '5. flag_progress: "Belum" atau "Selesai".',
                '6. HAPUS baris contoh (baris 2-3) dan petunjuk ini sebelum import!',
            ];
             $instructionRow = 6;
            foreach ($instructions as $instruction) {
                $sheet->setCellValue('A' . $instructionRow, $instruction);
                $sheet->getStyle('A' . $instructionRow)->getFont()->setItalic(true);
                $sheet->mergeCells("A{$instructionRow}:G{$instructionRow}");
                $instructionRow++;
            }

             $sheet->freezePane('A2');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'Template_Import_Distribusi_Bulanan.xlsx';
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($temp_file);

            return response()->download($temp_file, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
             \Log::error('Template Download Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat template: ' . $e->getMessage());
        }
    }

}