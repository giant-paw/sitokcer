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
    private $currentModul = 'distribusi_bulanan';

    public function index(Request $request, $jenisKegiatan)
    {
        // 1. Validasi jenis kegiatan [PERUBAHAN LOGIKA]
        $prefixKegiatan = strtoupper($jenisKegiatan);
        $jenisKegiatanLower = strtolower($jenisKegiatan);

        // Cek ke master: Apakah prefix ini ada DAN termasuk modul ini?
        $isValidJenisForModul = MasterKegiatan::where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                                             ->where('modul', $this->currentModul)
                                             ->exists();

        if (!$isValidJenisForModul) {
            abort(404, "Jenis kegiatan '{$jenisKegiatan}' tidak valid untuk modul {$this->currentModul}.");
        }
        // --- Akhir Validasi ---

        // 2. Logika Filter Tahun 
        $selectedTahun = $request->input('tahun', date('Y'));
        
         $availableTahun = DistribusiBulanan::query()
             ->leftJoin('master_kegiatan', 'distribusi_bulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
             ->where('master_kegiatan.modul', $this->currentModul) 
             ->where(function($q) use ($prefixKegiatan) { 
                 $q->where('master_kegiatan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                   ->orWhere(function($sub) use ($prefixKegiatan) {
                       $sub->whereNull('distribusi_bulanan.master_kegiatan_id')
                           ->where('distribusi_bulanan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%');
                   });
             })
             ->select(DB::raw('YEAR(distribusi_bulanan.created_at) as tahun'))
             ->distinct()
             ->whereNotNull('distribusi_bulanan.created_at')
             ->orderBy('tahun', 'desc')
             ->pluck('tahun')
             ->toArray();
             
         if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
             array_unshift($availableTahun, date('Y'));
         }


        // 3. Kueri Utama [PERUBAHAN LOGIKA: LEFT JOIN & MODUL]
        $query = DistribusiBulanan::query()
            ->leftJoin('master_kegiatan', 'distribusi_bulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            // Filter hanya data yang relevan dengan modul ini (bersih) ATAU data kotor (NULL)
            ->where(function($q) {
                 $q->where('master_kegiatan.modul', $this->currentModul)
                   ->orWhereNull('distribusi_bulanan.master_kegiatan_id'); 
            })
            // Filter berdasarkan prefix jenisKegiatan dari URL
            ->where(function($q) use ($prefixKegiatan) { 
                $q->where('master_kegiatan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                  ->orWhere(function($sub) use ($prefixKegiatan) {  
                      $sub->whereNull('distribusi_bulanan.master_kegiatan_id')
                          ->where('distribusi_bulanan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%');
                  });
            })
            ->whereYear('distribusi_bulanan.created_at', $selectedTahun);

        // Filter Kegiatan Spesifik (Tab) 
        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            if (is_numeric($selectedKegiatan)) {
                $query->where('distribusi_bulanan.master_kegiatan_id', $selectedKegiatan);
            } else {
                $query->whereNull('distribusi_bulanan.master_kegiatan_id')
                      ->where('distribusi_bulanan.nama_kegiatan', $selectedKegiatan);
            }
        }

        // Filter Pencarian 
        $search = $request->input('search', '');
        if ($search !== '') {
             $query->where(function ($q) use ($search) {
                $q->where('distribusi_bulanan.BS_Responden', 'like', "%{$search}%")
                  ->orWhere('distribusi_bulanan.pencacah', 'like', "%{$search}%")
                  ->orWhere('distribusi_bulanan.pengawas', 'like', "%{$search}%")
                  ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$search}%") 
                  ->orWhere('distribusi_bulanan.nama_kegiatan', 'like', "%{$search}%");
            });
        }

        // 4. Logika Pagination
        $perPageInput = $request->input('per_page', 20);
        $perPage = $perPageInput;
        if ($perPageInput == 'all') {
            $countQuery = (clone $query); 
            $countQuery->setEagerLoads([]); 
            $total = $countQuery->count('distribusi_bulanan.id_distribusi_bulanan');
            $perPage = $total > 0 ? $total : 20;
        }

        // 5. Ambil Data
        $listData = $query
            ->select('distribusi_bulanan.*') // Selalu SELECT dari tabel utama
            ->with('masterKegiatan') // Eager load relasi
            ->latest('distribusi_bulanan.id_distribusi_bulanan') 
            ->paginate($perPage) 
            ->withQueryString(); 

        // 6. Logika Hitung Tab (Dashboard) [PERUBAHAN LOGIKA: COALESCE]
        $kegiatanCounts = DistribusiBulanan::query()
            ->leftJoin('master_kegiatan', 'distribusi_bulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
             ->where(function($q) {
                 $q->where('master_kegiatan.modul', $this->currentModul)
                   ->orWhereNull('distribusi_bulanan.master_kegiatan_id'); 
             })
            ->where(function($q) use ($prefixKegiatan) { 
                $q->where('master_kegiatan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                  ->orWhere(function($sub) use ($prefixKegiatan) { 
                      $sub->whereNull('distribusi_bulanan.master_kegiatan_id')
                          ->where('distribusi_bulanan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%');
                  });
            })
            ->whereYear('distribusi_bulanan.created_at', $selectedTahun)
            ->select(
                DB::raw('COALESCE(master_kegiatan.id_master_kegiatan, distribusi_bulanan.nama_kegiatan) as filter_value'),
                DB::raw('COALESCE(master_kegiatan.nama_kegiatan, distribusi_bulanan.nama_kegiatan) as display_name'),
                DB::raw('count(distribusi_bulanan.id_distribusi_bulanan) as total')
            )
            ->groupBy('filter_value', 'display_name')
            ->orderBy('display_name')
            ->get();
            
        // Ambil daftar master murni untuk modul ini (jika diperlukan)
        $masterKegiatanList = MasterKegiatan::where('modul', $this->currentModul)
                                           ->orderBy('nama_kegiatan')->get();

        // 7. Kirim ke View
        return view('timDistribusi.distribusiBulanan', compact(
            'listData', 
            'kegiatanCounts', 
            'jenisKegiatan',
            'masterKegiatanList', 
            'availableTahun',     
            'selectedTahun',
            'selectedKegiatan', // dikirim untuk <select>
            'search'            // dikirim untuk <input>
        ));
    }


    public function store(Request $request)
    {
        $baseRules = [
            'master_kegiatan_id' => 'required|integer|exists:master_kegiatan,id_master_kegiatan',
            'nama_kegiatan'      => 'required|string|max:255', // Tetap ada
            'BS_Responden'       => 'required|string|max:255',
            'pencacah'           => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'           => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'      => ['required', Rule::in(['Belum Selesai', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
           'master_kegiatan_id.required' => 'Kegiatan wajib dipilih dari daftar.',
           'master_kegiatan_id.exists' => 'ID Kegiatan tidak terdaftar di master.',
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

        session()->flash('success', 'Data berhasil ditambahkan!');
        session()->flash('auto_hide', true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back();

    }

    public function edit($id) 
    {
        $distribusi_bulanan = DistribusiBulanan::findOrFail($id); 
        $data = $distribusi_bulanan->toArray();
        // Pastikan $data sekarang berisi 'master_kegiatan_id'
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
            'query' => 'nullable|string|max:100',
        ]);
        $query = $request->input('query', '');
        $data = MasterPetugas::query()
            ->where('nama_petugas', 'LIKE', "%{$query}%")
            ->limit(10)
            ->pluck('nama_petugas');
        return response()->json($data);
    }

    /**
     * [PERBAIKAN] Autocomplete Kegiatan (Return ID & Nama, filter by Modul)
     */
     public function searchKegiatan(Request $request, $jenisKegiatan = null)
     {
        $request->validate(['query' => 'nullable|string|max:100']);
        $query = $request->input('query', '');
        $kegiatanQuery = MasterKegiatan::query();

        // Filter WAJIB berdasarkan MODUL
        $kegiatanQuery->where('modul', $this->currentModul);

        // Filter berdasarkan prefix (jenisKegiatan dari URL)
        if ($jenisKegiatan) {
            $prefixKegiatan = strtoupper($jenisKegiatan);
            $kegiatanQuery->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%');
        }

        // Filter berdasarkan ketikan user
        $data = $kegiatanQuery
            ->where('nama_kegiatan', 'LIKE', "%{$query}%")
            ->limit(10)
            // [PENTING] Kembalikan ID dan Nama
            ->select('id_master_kegiatan', 'nama_kegiatan') 
            ->get(); 
        return response()->json($data);
     }


    public function export(Request $request, $jenisKegiatan)
    {
        // [PERBAIKAN] Validasi dinamis
        $prefixKegiatan = strtoupper($jenisKegiatan);
        $isValidJenisForModul = MasterKegiatan::where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                                             ->where('modul', $this->currentModul)
                                             ->exists();
        if (!$isValidJenisForModul) {
            abort(404);
        }

        $dataRange = $request->input('dataRange', 'all');
        $dataFormat = $request->input('dataFormat', 'formatted_values');
        $exportFormat = $request->input('exportFormat', 'excel');
        $kegiatan = $request->input('kegiatan'); 
        $search = $request->input('search');
        $tahun = $request->input('tahun', date('Y')); 
        $currentPage = $request->input('page', 1);
        $perPageInput = $request->input('per_page', 20);

        $perPage = ($perPageInput == 'all' || $dataRange == 'all') ? -1 : (int)$perPageInput;

        // Pastikan DistribusiBulananExport di-update logikanya
        $exportClass = new DistribusiBulananExport(
            $dataRange,
            $dataFormat,
            $jenisKegiatan,     // Ini adalah prefix (misal: 'vhts')
            $kegiatan,          // Ini adalah filter_value (bisa ID atau Teks)
            $search,
            $tahun,             // Ini adalah tahun
            $currentPage,
            $perPage,
            $this->currentModul // [TAMBAHAN] Kirim modul saat ini
        );

        $fileName = 'DistribusiBulanan_' . strtoupper($jenisKegiatan) . '_' . $tahun . '_' . now()->format('YmdHis');

        if ($exportFormat == 'excel') {
            return Excel::download($exportClass, $fileName . '.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV, [
                 'Content-Type' => 'text/csv',
             ]);
        }

        return back()->with('error', 'Format ekspor tidak didukung.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048' 
        ],[
            'file.required' => 'File Excel/CSV wajib diunggah.',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV (.csv).',
            'file.max' => 'Ukuran file maksimal 2MB.',
        ]);

        try {
            $file = $request->file('file');
            // [PENTING] Kirim modul saat ini ke Importer
            $import = new DistribusiBulananImport($this->currentModul);
            Excel::import($import, $file);

            $errors = $import->getErrors(); 
            $successCount = $import->getSuccessCount(); 

            if (!empty($errors)) {
                 $formattedErrors = collect($errors)->map(function ($err) {
                     return [
                         'row' => $err['row'] ?? '?',
                         'error' => $err['error'] ?? 'Unknown Error',
                         'values' => $err['values'] ?? 'N/A'
                     ];
                 })->toArray();

                 return back()
                     ->with('import_errors', $formattedErrors)
                     ->with('success_count', $successCount) 
                     ->with('warning', "Import selesai dengan {$successCount} data berhasil dan " . count($errors) . " data gagal.");
            }

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
                     'error' => implode(', ', $failure->errors()),
                     'values' => $failure->values()[$failure->attribute()] ?? 'N/A'
                 ];
             }
             return back()
                 ->with('import_errors', $formattedErrors)
                 ->with('error', 'Import gagal karena ada data yang tidak valid.');

        } catch (\Exception $e) {
            \Log::error('Import Error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString()); 
            return back()->with('error', 'Terjadi kesalahan sistem saat import: ' . $e->getMessage());
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
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);
            $sheet->getStyle('A1:G1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD9EAD3');

            // [PERBAIKAN] Ambil contoh kegiatan yang VALID untuk modul ini
             $exampleKegiatan1 = MasterKegiatan::where('modul', $this->currentModul)->skip(0)->first();
             $exampleKegiatan2 = MasterKegiatan::where('modul', $this->currentModul)->skip(1)->first();
             $namaKeg1 = $exampleKegiatan1 ? $exampleKegiatan1->nama_kegiatan : 'NAMA_KEGIATAN_VALID_1';
             $namaKeg2 = $exampleKegiatan2 ? $exampleKegiatan2->nama_kegiatan : 'NAMA_KEGIATAN_VALID_2';

             $exampleData = [
                [
                    $namaKeg1,
                    'BS001',
                    'Nama Pencacah Valid', // Ganti dengan nama valid
                    'Nama Pengawas Valid', // Ganti dengan nama valid
                    '2025-01-31',
                    'Belum Selesai',
                    '2025-01-20'
                ],
                [
                    $namaKeg2,
                    'BS002',
                    'Nama Pencacah Lain', // Ganti dengan nama valid
                    'Nama Pengawas Lain', // Ganti dengan nama valid
                    '2025-02-28',
                    'Selesai',
                    '' // Boleh kosong
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
                '2. Kolom selain "tanggal_pengumpulan" WAJIB diisi.',
                '3. "nama_kegiatan" HARUS SAMA PERSIS dengan data di Master Kegiatan untuk modul ini (Contoh: ' . $namaKeg1 . ').',
                '4. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY.',
                '5. flag_progress: "Belum Selesai" atau "Selesai".',
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
             \Log::error('Template Download Error: '. $e->getMessage());
            return back()->with('error', 'Gagal membuat template: ' . $e->getMessage());
        }
    }

} // <-- Ini adalah kurung kurawal penutup Class