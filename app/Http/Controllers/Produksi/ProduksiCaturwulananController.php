<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // [FIX] Tambahkan Request
use App\Models\Produksi\ProduksiCaturwulanan;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProduksiCaturwulananExport; // Pastikan file ini ada
use App\Imports\ProduksiCaturwulananImport; // Pastikan file ini ada
use Illuminate\Validation\Rule;

class ProduksiCaturwulananController extends Controller
{
    /**
     * Peta Prefix (Translator)
     * Menerjemahkan 'jenisKegiatan' dari URL ke prefix nama kegiatan di DB.
     */
    private function getPrefixMap($jenisKegiatan)
    {
        $map = [
            // Kunci (key) = $jenisKegiatan dari URL (lowercase)
            'ubinan'       => ['UbinanPadiPalawija'],
            'updating utp' => ['UpdatingUTPPalawija'],
            // [REKOMENDASI] Ganti 'updating utp' menjadi 'updating-utp'
            // 'updating-utp' => ['UpdatingUTPPalawija'] 
        ];
        
        $jenisKegiatanLower = strtolower($jenisKegiatan);
        
        if (!isset($map[$jenisKegiatanLower])) {
            abort(404, "Pemetaan prefix untuk '{$jenisKegiatan}' tidak ditemukan di Controller.");
        }
        
        return $map[$jenisKegiatanLower]; // Kembalikan prefix yang benar
    }


    public function index(Request $request, $jenisKegiatan)
    {
        $currentModul = 'produksi_caturwulanan'; 
        $validPrefixes = $this->getPrefixMap($jenisKegiatan);
        $selectedTahun = $request->input('tahun', date('Y'));
        
        // Query Tahun yang Tersedia
        $availableTahunQuery = ProduksiCaturwulanan::query()
            ->leftJoin('master_kegiatan', 'produksi_caturwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) use ($currentModul) {
                $q->where('master_kegiatan.modul', $currentModul)
                ->orWhereNull('produksi_caturwulanan.master_kegiatan_id'); 
            });
            
        $availableTahunQuery->where(function($q) use ($validPrefixes) {
            if (empty($validPrefixes)) { $q->whereRaw('1 = 0'); return; }
            foreach ($validPrefixes as $prefix) {
                $q->orWhere('master_kegiatan.nama_kegiatan', 'LIKE', $prefix . '%')
                  ->orWhere(function($sub) use ($prefix) {
                      $sub->whereNull('produksi_caturwulanan.master_kegiatan_id')
                          ->where('produksi_caturwulanan.nama_kegiatan', 'LIKE', $prefix . '%');
                  });
            }
        });
            
        $availableTahun = $availableTahunQuery
            ->select(DB::raw('YEAR(produksi_caturwulanan.created_at) as tahun'))
            ->distinct()
            ->whereNotNull('produksi_caturwulanan.created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
            
        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // Kueri Utama untuk Data
        $query = ProduksiCaturwulanan::query()
            ->leftJoin('master_kegiatan', 'produksi_caturwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) use ($currentModul) {
                $q->where('master_kegiatan.modul', $currentModul)
                ->orWhereNull('produksi_caturwulanan.master_kegiatan_id'); 
            })
            ->where(function($q) use ($validPrefixes) {
                if (empty($validPrefixes)) { $q->whereRaw('1 = 0'); return; }
                foreach ($validPrefixes as $prefix) {
                    $q->orWhere('master_kegiatan.nama_kegiatan', 'LIKE', $prefix . '%')
                      ->orWhere(function($sub) use ($prefix) {
                          $sub->whereNull('produksi_caturwulanan.master_kegiatan_id')
                              ->where('produksi_caturwulanan.nama_kegiatan', 'LIKE', $prefix . '%');
                      });
                }
            })
            ->whereYear('produksi_caturwulanan.created_at', $selectedTahun);

        // Filter Tab Kegiatan Spesifik
        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            if (is_numeric($selectedKegiatan)) {
                $query->where('produksi_caturwulanan.master_kegiatan_id', $selectedKegiatan);
            } else {
                $query->whereNull('produksi_caturwulanan.master_kegiatan_id')
                    ->where('produksi_caturwulanan.nama_kegiatan', $selectedKegiatan);
            }
        }

        // Filter Pencarian
        $search = $request->input('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('produksi_caturwulanan.BS_Responden', 'like', "%{$search}%")
                ->orWhere('produksi_caturwulanan.pencacah', 'like', "%{$search}%")
                ->orWhere('produksi_caturwulanan.pengawas', 'like', "%{$search}%")
                ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$search}%")
                ->orWhere('produksi_caturwulanan.nama_kegiatan', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPageInput = $request->input('per_page', 20); 
        $perPage = $perPageInput; 
        if ($perPageInput == 'all') {
            $countQuery = (clone $query); 
            $countQuery->setEagerLoads([]); 
            $total = $countQuery->count('produksi_caturwulanan.id_produksi_caturwulanan'); 
            $perPage = $total > 0 ? $total : 20; 
        }

        // Ambil Data
        $listData = $query
            ->select('produksi_caturwulanan.*')
            ->with('masterKegiatan')
            ->latest('produksi_caturwulanan.id_produksi_caturwulanan')
            ->paginate($perPage) 
            ->withQueryString(); 

        // Kueri Hitung Tab
        $kegiatanCountsQuery = ProduksiCaturwulanan::query()
            ->leftJoin('master_kegiatan', 'produksi_caturwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) use ($currentModul) {
                $q->where('master_kegiatan.modul', $currentModul)
                ->orWhereNull('produksi_caturwulanan.master_kegiatan_id'); 
            })
            ->where(function($q) use ($validPrefixes) {
                if (empty($validPrefixes)) { $q->whereRaw('1 = 0'); return; }
                foreach ($validPrefixes as $prefix) {
                    $q->orWhere('master_kegiatan.nama_kegiatan', 'LIKE', $prefix . '%')
                      ->orWhere(function($sub) use ($prefix) {
                          $sub->whereNull('produksi_caturwulanan.master_kegiatan_id')
                              ->where('produksi_caturwulanan.nama_kegiatan', 'LIKE', $prefix . '%');
                      });
                }
            })
            ->whereYear('produksi_caturwulanan.created_at', $selectedTahun);
            
        $kegiatanCounts = $kegiatanCountsQuery
            ->select(
                DB::raw('COALESCE(master_kegiatan.id_master_kegiatan, produksi_caturwulanan.nama_kegiatan) as filter_value'),
                DB::raw('COALESCE(master_kegiatan.nama_kegiatan, produksi_caturwulanan.nama_kegiatan) as display_name'),
                DB::raw('count(produksi_caturwulanan.id_produksi_caturwulanan) as total')
            )
            ->groupBy('filter_value', 'display_name')
            ->orderBy('display_name')
            ->get();
            
        // Ambil Master Kegiatan (untuk Autocomplete)
        $masterKegiatanList = MasterKegiatan::where('modul', $currentModul)
                                            ->orderBy('nama_kegiatan')->get();

        // Kirim ke View
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

    public function store(Request $request)
    {
        $baseRules = [
            'master_kegiatan_id' => 'required|integer|exists:master_kegiatan,id_master_kegiatan', 
            'nama_kegiatan'      => 'required|string|max:255',
            'BS_Responden'       => 'required|string|max:255',
            'pencacah'           => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'           => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'      => ['required', Rule::in(['Belum Selesai', 'Selesai'])], 
            'tanggal_pengumpulan' => 'nullable|date',
        ];
        
        $customMessages = [
            'master_kegiatan_id.required' => 'Kegiatan wajib dipilih dari daftar.',
            'master_kegiatan_id.exists'   => 'ID Kegiatan tidak terdaftar di master.',
            'pencacah.exists'             => 'Nama pencacah tidak terdaftar.',
            'pengawas.exists'             => 'Nama pengawas tidak terdaftar.',
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
            try { 
                // $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; 
            } catch (\Exception $e) { /* Abaikan */ }
        }

        ProduksiCaturwulanan::create($validatedData); 

        session()->flash('success', 'Data berhasil ditambahkan!');
        session()->flash('auto_hide', true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back();
    }

    public function edit($id) 
    {
        $produksi_caturwulanan = ProduksiCaturwulanan::findOrFail($id); 
        $data = $produksi_caturwulanan->toArray();
        $targetPenyelesaian = $produksi_caturwulanan->target_penyelesaian;
        $tanggalPengumpulan = $produksi_caturwulanan->tanggal_pengumpulan;
        $data['target_penyelesaian'] = $targetPenyelesaian ? Carbon::parse($targetPenyelesaian)->toDateString() : null;
        $data['tanggal_pengumpulan'] = $tanggalPengumpulan ? Carbon::parse($tanggalPengumpulan)->toDateString() : null;
        return response()->json($data);
    }

    public function update(Request $request, $id) 
    {
        $produksi_caturwulanan = ProduksiCaturwulanan::findOrFail($id); 
        $baseRules = [
            'master_kegiatan_id' => 'required|integer|exists:master_kegiatan,id_master_kegiatan', 
            'nama_kegiatan'      => 'required|string|max:255',
            'BS_Responden'       => 'required|string|max:255',
            'pencacah'           => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'           => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'      => ['required', Rule::in(['Belum Selesai', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];
        $customMessages = [
            'master_kegiatan_id.required' => 'Kegiatan wajib dipilih dari daftar.',
            'master_kegiatan_id.exists'   => 'ID Kegiatan tidak terdaftar di master.',
            'pencacah.exists'             => 'Nama pencacah tidak terdaftar.',
            'pengawas.exists'             => 'Nama pengawas tidak terdaftar.',
        ];
        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator, 'editForm') 
                ->withInput()
                ->with('error_modal', 'editDataModal')
                ->with('edit_id', $produksi_caturwulanan->id_produksi_caturwulanan);
        }

        $validatedData = $validator->validated();
        if ($request->filled('target_penyelesaian')) {
            try { 
                // $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; 
            } catch (\Exception $e) { /* Abaikan */ }
        }

        $produksi_caturwulanan->update($validatedData);
        
        session()->flash('success', 'Data berhasil diperbarui!');
        session()->flash('auto_hide', true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        return back();
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

    // [PERBAIKAN] Tambahkan Request $request
    public function destroy(Request $request, $id) 
    {
        $produksi_caturwulanan = ProduksiCaturwulanan::findOrFail($id); 
        $produksi_caturwulanan->delete();

        session()->flash('success', 'Data berhasil dihapus!');
        session()->flash('auto_hide', true);

        // [PERBAIKAN] Gunakan $request
        if ($request->ajax() || $request->wantsJson()) { 
            return response()->json(['success' => 'Data berhasil dihapus!']); 
        }

        return back(); 
    }

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

    public function searchKegiatan(Request $request, $jenisKegiatan = null)
    {
        $request->validate(['query' => 'nullable|string|max:100']);
        $query = $request->input('query', '');
        $kegiatanQuery = MasterKegiatan::query();
        
        // Filter berdasarkan MODUL
        $kegiatanQuery->where('modul', 'produksi_caturwulanan');

        // Filter berdasarkan jenisKegiatan (dari URL) jika diberikan
        if ($jenisKegiatan) {
            try {
                $validPrefixes = $this->getPrefixMap($jenisKegiatan);
                
                $kegiatanQuery->where(function($q) use ($validPrefixes) {
                     if (empty($validPrefixes)) { $q->whereRaw('1 = 0'); return; }
                     foreach ($validPrefixes as $prefix) {
                        $q->orWhere('nama_kegiatan', 'LIKE', $prefix . '%');
                     }
                });
            } catch (\Exception $e) {
                 $kegiatanQuery->whereRaw('1 = 0');
            }
        }

        $data = $kegiatanQuery
            ->where('nama_kegiatan', 'LIKE', "%{$query}%")
            ->limit(10)
            ->select('id_master_kegiatan', 'nama_kegiatan') 
            ->get();
        return response()->json($data);
    }

    public function export(Request $request, $jenisKegiatan)
    {
        // 1. Validasi prefix (otomatis 404 jika tidak ada di map)
        $this->getPrefixMap($jenisKegiatan);

        // 2. Ambil parameter filter
        $dataRange = $request->input('dataRange', 'all');
        $dataFormat = $request->input('dataFormat', 'formatted_values');
        $exportFormat = $request->input('exportFormat', 'excel');
        $kegiatan = $request->input('kegiatan');
        $search = $request->input('search');
        $currentPage = $request->input('page', 1);
        $perPageInput = $request->input('per_page', 20);
        $selectedTahun = $request->input('tahun', date('Y')); 
        $perPage = ($perPageInput == 'all' || $dataRange == 'all') ? -1 : (int)$perPageInput; 
        
        // 3. Panggil Export Class
        $exportClass = new ProduksiCaturwulananExport(
            $dataRange,
            $dataFormat,
            $jenisKegiatan,   // kirim 'ubinan' atau 'updating utp'
            $kegiatan,        
            $search,
            $currentPage,
            $perPage,
            $selectedTahun    
        );

        $fileName = 'ProduksiCaturwulanan_' . str_replace(' ', '_', strtoupper($jenisKegiatan)) . '_' . $selectedTahun . '_' . now()->format('YmdHis');

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
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ], [
            'file.required' => 'File Excel/CSV wajib diunggah.',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV (.csv).',
            'file.max' => 'Ukuran file maksimal 2MB.',
        ]);

        try {
            $file = $request->file('file');
            // Panggil Import Class yang sudah dimodifikasi
            $import = new ProduksiCaturwulananImport(); 
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

            // Header
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

            // Sample data
            $exampleData = [
                [
                    'UbinanPadiPalawija-Sub1', // Contoh 1
                    'BS001',
                    'Nama Petugas Valid 1', // Ganti dengan nama valid di master_petugas Anda
                    'Nama Petugas Valid 2', // Ganti dengan nama valid di master_petugas Anda
                    '2025-04-30',
                    'Belum Selesai',
                    '2025-04-20'
                ],
                [
                    'UpdatingUTPPalawija1', // Contoh 2
                    'BS002',
                    'Nama Petugas Valid 3', // Ganti dengan nama valid di master_petugas Anda
                    'Nama Petugas Valid 4', // Ganti dengan nama valid di master_petugas Anda
                    '2025-08-31',
                    'Selesai',
                    '2025-08-25'
                ]
            ];
            $sheet->fromArray($exampleData, null, 'A2');
            $sheet->getStyle('A2:G3')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFF4CC'); 

            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Petunjuk (disesuaikan dengan validasi baru)
            $sheet->setCellValue('A5', 'PETUNJUK PENGISIAN:');
            $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A5')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFB4C7E7');
            
            $instructions = [
                '1. Kolom WAJIB: nama_kegiatan, bs_responden, pencacah, pengawas, target_penyelesaian.',
                '2. "nama_kegiatan" WAJIB terdaftar di Master Kegiatan (modul produksi_caturwulanan).',
                '3. "pencacah" dan "pengawas" TIDAK divalidasi ke master, tetapi tetap wajib diisi.',
                '4. "flag_progress" TIDAK wajib. Jika diisi "Selesai", akan disimpan. Jika kosong/salah, otomatis "Belum Selesai".',
                '5. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY.',
                '6. HAPUS baris contoh (baris 2-3) dan petunjuk ini sebelum import!',
            ];
            $instructionRow = 6;
            foreach ($instructions as $instruction) {
                $sheet->setCellValue('A' . $instructionRow, $instruction);
                $sheet->getStyle('A' . $instructionRow)->getFont()->setItalic(true);
                $sheet->mergeCells("A{$instructionRow}:G{$instructionRow}");
                $instructionRow++;
            }
            
            $sheet->freezePane('A2'); // Freeze header

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'Template_Import_Produksi_Caturwulanan.xlsx'; 
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
}