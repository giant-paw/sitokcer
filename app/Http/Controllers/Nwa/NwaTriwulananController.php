<?php

namespace App\Http\Controllers\Nwa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Nwa\NwaTriwulanan; // Model yang Benar
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NwaTriwulananExport;   
use App\Imports\NwaTriwulananImport;   
use Illuminate\Validation\Rule;

class NwaTriwulananController extends Controller
{
    /**
     * Peta Prefix (Translator)
     */
    private function getPrefixMap($jenisKegiatan)
    {
        $map = [
            'sklnp'       => ['SKLNPRT-', 'SKLNP-'],
            'snaper'      => ['SNAPER-'],
            'sktnp'       => ['SKTNP TAHAP '], 
        ];
        
        $jenisKegiatanLower = strtolower($jenisKegiatan);
        
        if (!isset($map[$jenisKegiatanLower])) {
            abort(404, "Pemetaan prefix untuk '{$jenisKegiatan}' tidak ditemukan di Controller.");
        }
        
        return $map[$jenisKegiatanLower];
    }


    public function index(Request $request, $jenisKegiatan)
    {
        // [BENAR] Menggunakan modul 'nwa_triwulanan'
        $currentModul = 'nwa_triwulanan'; 

        $validPrefixes = $this->getPrefixMap($jenisKegiatan);
        $selectedTahun = $request->input('tahun', date('Y'));
        
        // [BENAR] Menggunakan Model NwaTriwulanan
        $availableTahunQuery = NwaTriwulanan::query() 
            ->leftJoin('master_kegiatan', 'nwa_triwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) use ($currentModul) {
                $q->where('master_kegiatan.modul', $currentModul)
                ->orWhereNull('nwa_triwulanan.master_kegiatan_id'); 
            });
            
        $availableTahunQuery->where(function($q) use ($validPrefixes) {
            if (empty($validPrefixes)) { $q->whereRaw('1 = 0'); return; } 
            foreach ($validPrefixes as $prefix) {
                $q->orWhere('master_kegiatan.nama_kegiatan', 'LIKE', $prefix . '%')
                  ->orWhere(function($sub) use ($prefix) {
                      $sub->whereNull('nwa_triwulanan.master_kegiatan_id')
                          ->where('nwa_triwulanan.nama_kegiatan', 'LIKE', $prefix . '%');
                  });
            }
        });
            
        $availableTahun = $availableTahunQuery
            ->select(DB::raw('YEAR(nwa_triwulanan.created_at) as tahun'))
            ->distinct()
            ->whereNotNull('nwa_triwulanan.created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
            
        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // [BENAR] Menggunakan Model NwaTriwulanan
        $query = NwaTriwulanan::query() 
            ->leftJoin('master_kegiatan', 'nwa_triwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) use ($currentModul) {
                $q->where('master_kegiatan.modul', $currentModul)
                ->orWhereNull('nwa_triwulanan.master_kegiatan_id'); 
            })
            ->where(function($q) use ($validPrefixes) {
                if (empty($validPrefixes)) { $q->whereRaw('1 = 0'); return; }
                foreach ($validPrefixes as $prefix) {
                    $q->orWhere('master_kegiatan.nama_kegiatan', 'LIKE', $prefix . '%')
                      ->orWhere(function($sub) use ($prefix) {
                          $sub->whereNull('nwa_triwulanan.master_kegiatan_id')
                              ->where('nwa_triwulanan.nama_kegiatan', 'LIKE', $prefix . '%');
                      });
                }
            })
            ->whereYear('nwa_triwulanan.created_at', $selectedTahun);

        // Filter Kegiatan Spesifik (Tab) 
        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            if (is_numeric($selectedKegiatan)) {
                $query->where('nwa_triwulanan.master_kegiatan_id', $selectedKegiatan);
            } else {
                $query->whereNull('nwa_triwulanan.master_kegiatan_id')
                    ->where('nwa_triwulanan.nama_kegiatan', $selectedKegiatan);
            }
        }

        // Filter Pencarian 
        $search = $request->input('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nwa_triwulanan.BS_Responden', 'like', "%{$search}%")
                ->orWhere('nwa_triwulanan.pencacah', 'like', "%{$search}%")
                ->orWhere('nwa_triwulanan.pengawas', 'like', "%{$search}%")
                ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$search}%") 
                ->orWhere('nwa_triwulanan.nama_kegiatan', 'like', "%{$search}%");
            });
        }

        // 4. Logika Pagination
        $perPageInput = $request->input('per_page', 20); 
        $perPage = $perPageInput; 
        if ($perPageInput == 'all') {
            $countQuery = (clone $query); 
            $countQuery->setEagerLoads([]); 
            $total = $countQuery->count('nwa_triwulanan.id_nwa_triwulanan'); 
            $perPage = $total > 0 ? $total : 20; 
        }

        // 5. Ambil Data
        $listData = $query
            ->select('nwa_triwulanan.*') 
            ->with('masterKegiatan') 
            ->latest('nwa_triwulanan.id_nwa_triwulanan') 
            ->paginate($perPage) 
            ->withQueryString(); 

        // 6. Logika Hitung Tab (Dashboard)
        $kegiatanCountsQuery = NwaTriwulanan::query() 
            ->leftJoin('master_kegiatan', 'nwa_triwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) use ($currentModul) {
                $q->where('master_kegiatan.modul', $currentModul)
                ->orWhereNull('nwa_triwulanan.master_kegiatan_id'); 
            })
            ->where(function($q) use ($validPrefixes) {
                if (empty($validPrefixes)) { $q->whereRaw('1 = 0'); return; }
                foreach ($validPrefixes as $prefix) {
                    $q->orWhere('master_kegiatan.nama_kegiatan', 'LIKE', $prefix . '%')
                      ->orWhere(function($sub) use ($prefix) {
                          $sub->whereNull('nwa_triwulanan.master_kegiatan_id')
                              ->where('nwa_triwulanan.nama_kegiatan', 'LIKE', $prefix . '%');
                      });
                }
            })
            ->whereYear('nwa_triwulanan.created_at', $selectedTahun);
            
        $kegiatanCounts = $kegiatanCountsQuery
            ->select(
                DB::raw('COALESCE(master_kegiatan.id_master_kegiatan, nwa_triwulanan.nama_kegiatan) as filter_value'),
                DB::raw('COALESCE(master_kegiatan.nama_kegiatan, nwa_triwulanan.nama_kegiatan) as display_name'),
                DB::raw('count(nwa_triwulanan.id_nwa_triwulanan) as total') 
            )
            ->groupBy('filter_value', 'display_name')
            ->orderBy('display_name')
            ->get();
            
        // Ambil master kegiatan hanya untuk MODUL ini
        $masterKegiatanList = MasterKegiatan::where('modul', $currentModul)
                                            ->orderBy('nama_kegiatan')->get();

        // 7. Kirim ke View
        return view('timNWA.triwulanan.NWATriwulanan', compact( 
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
        NwaTriwulanan::create($validatedData); 

        session()->flash('success', 'Data berhasil ditambahkan!');
        session()->flash('auto_hide', true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back();
    }

    public function edit($id) 
    {
        $nwa_triwulanan = NwaTriwulanan::findOrFail($id); 
        $data = $nwa_triwulanan->toArray();
        try {
            $data['target_penyelesaian'] = Carbon::parse($data['target_penyelesaian'])->toDateString();
        } catch (\Exception $e) {
            $data['target_penyelesaian'] = null;
        }
        try {
            $data['tanggal_pengumpulan'] = Carbon::parse($data['tanggal_pengumpulan'])->toDateString();
        } catch (\Exception $e) {
            $data['tanggal_pengumpulan'] = null;
        }
        return response()->json($data);
    }

    public function update(Request $request, $id) 
    {
        $nwa_triwulanan = NwaTriwulanan::findOrFail($id); 
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
                ->with('edit_id', $nwa_triwulanan->id_nwa_triwulanan); 
        }

        $validatedData = $validator->validated();
        $nwa_triwulanan->update($validatedData);
        
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
            'ids.*' => 'exists:nwa_triwulanan,id_nwa_triwulanan' 
        ]);
        NwaTriwulanan::whereIn('id_nwa_triwulanan', $request->ids)->delete(); 
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    public function destroy(Request $request, $id) 
    {
        $nwa_triwulanan = NwaTriwulanan::findOrFail($id); 
        $nwa_triwulanan->delete();

        session()->flash('success', 'Data berhasil dihapus!');
        session()->flash('auto_hide', true);

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
        
        $kegiatanQuery->where('modul', 'nwa_triwulanan'); 

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
        $this->getPrefixMap($jenisKegiatan);

        $dataRange = $request->input('dataRange', 'all');
        $dataFormat = $request->input('dataFormat', 'formatted_values');
        $exportFormat = $request->input('exportFormat', 'excel');
        $kegiatan = $request->input('kegiatan');
        $search = $request->input('search');
        $currentPage = $request->input('page', 1);
        $perPageInput = $request->input('per_page', 20);
        $selectedTahun = $request->input('tahun', date('Y')); 
        $perPage = ($perPageInput == 'all' || $dataRange == 'all') ? -1 : (int)$perPageInput; 
        
        $exportClass = new NwaTriwulananExport( 
            $dataRange,
            $dataFormat,
            $jenisKegiatan,   
            $kegiatan,        
            $search,
            $currentPage,
            $perPage,
            $selectedTahun    
        );

        $fileName = 'NwaTriwulanan_' . str_replace(' ', '_', strtoupper($jenisKegiatan)) . '_' . $selectedTahun . '_' . now()->format('YmdHis'); 

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
            $import = new NwaTriwulananImport(); 
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

            $exampleData = [
                [
                    'SKLNP-TW1',
                    'BS001',
                    'Nama Petugas Valid 1', 
                    'Nama Petugas Valid 2', 
                    '2025-03-31',
                    'Belum Selesai',
                    ''
                ],
                [
                    'SNAPER-TW1',
                    'BS002',
                    'Nama Petugas Valid 3', 
                    'Nama Petugas Valid 4', 
                    '2025-03-31',
                    'Selesai',
                    '2025-03-25'
                ],
                [
                    'SKTNP TAHAP 1',
                    'BS003',
                    'Nama Petugas Valid 5', 
                    'Nama Petugas Valid 6', 
                    '2025-03-31',
                    'Selesai',
                    '2025-03-25'
                ]
            ];
            $sheet->fromArray($exampleData, null, 'A2');
            $sheet->getStyle('A2:G4')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFF4CC'); 

            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet->setCellValue('A6', 'PETUNJUK PENGISIAN:');
            $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A6')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFB4C7E7');
            
            $instructions = [
                '1. Kolom WAJIB: nama_kegiatan, bs_responden, pencacah, pengawas, target_penyelesaian.',
                '2. "nama_kegiatan" WAJIB terdaftar di Master Kegiatan (modul nwa_triwulanan). Cth: SNAPER-TW1, SKTNP TAHAP 1',
                '3. "pencacah" dan "pengawas" TIDAK divalidasi ke master, tetapi tetap wajib diisi.',
                '4. "flag_progress" TIDAK wajib. Jika diisi "Selesai", akan disimpan. Jika kosong/salah, otomatis "Belum Selesai".',
                '5. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY.',
                '6. HAPUS baris contoh (baris 2-4) dan petunjuk ini sebelum import!',
            ];
            $instructionRow = 7;
            foreach ($instructions as $instruction) {
                $sheet->setCellValue('A' . $instructionRow, $instruction);
                $sheet->getStyle('A' . $instructionRow)->getFont()->setItalic(true);
                $sheet->mergeCells("A{$instructionRow}:G{$instructionRow}");
                $instructionRow++;
            }
            
            $sheet->freezePane('A2'); // Freeze header

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'Template_Import_NWA_Triwulanan.xlsx'; 
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