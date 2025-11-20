<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produksi\ProduksiTahunan;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProduksiTahunanExport;
use App\Imports\ProduksiTahunanImport;
use Illuminate\Validation\Rule;

class ProduksiTahunanController extends Controller
{
    // === FUNGSI getPrefixMap() DIHAPUS KARENA TIDAK DIPERLUKAN LAGI ===

    public function index(Request $request)
    {
        // Definisikan modul untuk controller ini
        $currentModul = 'produksi_tahunan'; 

        // [PERBAIKAN] Definisikan $jenisKegiatan sebagai string dummy
        // karena view Anda masih membutuhkannya, tetapi rute tidak lagi menyediakannya.
        $jenisKegiatan = 'tahunan'; // Anda bisa ganti ini dengan string apapun

        // 2. Logika Filter Tahun 
        $selectedTahun = $request->input('tahun', date('Y'));
        
        $availableTahunQuery = ProduksiTahunan::query()
            ->leftJoin('master_kegiatan', 'produksi_tahunan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) use ($currentModul) {
                $q->where('master_kegiatan.modul', $currentModul)
                ->orWhereNull('produksi_tahunan.master_kegiatan_id'); 
            });
            
        // KARENA 'jenisKegiatan' TIDAK ADA, kita tidak memfilter berdasarkan prefix
        // $availableTahunQuery->where(function($q) use ($validPrefixes) { ... });
            
        $availableTahun = $availableTahunQuery
            ->select(DB::raw('YEAR(produksi_tahunan.created_at) as tahun'))
            ->distinct()
            ->whereNotNull('produksi_tahunan.created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
            
        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // 3. Kueri Utama
        $query = ProduksiTahunan::query()
            ->leftJoin('master_kegiatan', 'produksi_tahunan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) use ($currentModul) {
                $q->where('master_kegiatan.modul', $currentModul)
                ->orWhereNull('produksi_tahunan.master_kegiatan_id'); 
            })
            // KARENA 'jenisKegiatan' TIDAK ADA, kita tidak memfilter berdasarkan prefix
            // ->where(function($q) use ($validPrefixes) { ... })
            ->whereYear('produksi_tahunan.created_at', $selectedTahun);

        // Filter Kegiatan Spesifik (Tab) 
        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            if (is_numeric($selectedKegiatan)) {
                $query->where('produksi_tahunan.master_kegiatan_id', $selectedKegiatan);
            } else {
                $query->whereNull('produksi_tahunan.master_kegiatan_id')
                    ->where('produksi_tahunan.nama_kegiatan', $selectedKegiatan);
            }
        }

        // Filter Pencarian 
        $search = $request->input('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('produksi_tahunan.BS_Responden', 'like', "%{$search}%")
                ->orWhere('produksi_tahunan.pencacah', 'like', "%{$search}%")
                ->orWhere('produksi_tahunan.pengawas', 'like', "%{$search}%")
                ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$search}%") 
                ->orWhere('produksi_tahunan.nama_kegiatan', 'like', "%{$search}%");
            });
        }

        // 4. Logika Pagination
        $perPageInput = $request->input('per_page', 20); 
        $perPage = $perPageInput; 
        if ($perPageInput == 'all') {
            $countQuery = (clone $query); 
            $countQuery->setEagerLoads([]); 
            $total = $countQuery->count('produksi_tahunan.id_produksi');
            $perPage = $total > 0 ? $total : 20; 
        }

        // 5. Ambil Data
        $listData = $query
            ->select('produksi_tahunan.*') 
            ->with('masterKegiatan') 
            ->latest('produksi_tahunan.id_produksi')
            ->paginate($perPage) 
            ->withQueryString(); 

        // 6. Logika Hitung Tab (Dashboard)
        $kegiatanCountsQuery = ProduksiTahunan::query()
            ->leftJoin('master_kegiatan', 'produksi_tahunan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) use ($currentModul) {
                $q->where('master_kegiatan.modul', $currentModul)
                ->orWhereNull('produksi_tahunan.master_kegiatan_id'); 
            })
            // KARENA 'jenisKegiatan' TIDAK ADA, kita tidak memfilter berdasarkan prefix
            // ->where(function($q) use ($validPrefixes) { ... })
            ->whereYear('produksi_tahunan.created_at', $selectedTahun);
            
        $kegiatanCounts = $kegiatanCountsQuery
            ->select(
                DB::raw('COALESCE(master_kegiatan.id_master_kegiatan, produksi_tahunan.nama_kegiatan) as filter_value'),
                DB::raw('COALESCE(master_kegiatan.nama_kegiatan, produksi_tahunan.nama_kegiatan) as display_name'),
                DB::raw('count(produksi_tahunan.id_produksi) as total') 
            )
            ->groupBy('filter_value', 'display_name')
            ->orderBy('display_name')
            ->get();
            
        // Ambil master kegiatan hanya untuk MODUL ini
        $masterKegiatanList = MasterKegiatan::where('modul', $currentModul)
                                            ->orderBy('nama_kegiatan')->get();

        // 7. Kirim ke View
        return view('timProduksi.ProduksiTahunan', compact(
            'listData', 
            'kegiatanCounts', 
            'jenisKegiatan', // <-- Variabel ini sekarang sudah didefinisikan di baris 131
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
        ProduksiTahunan::create($validatedData);

        session()->flash('success', 'Data berhasil ditambahkan!');
        session()->flash('auto_hide', true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back();
    }

    public function edit($id) 
    {
        $produksi_tahunan = ProduksiTahunan::findOrFail($id); 
        $data = $produksi_tahunan->toArray();
        // Handle format tanggal dari SQL Anda yang bervariasi
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
        $produksi_tahunan = ProduksiTahunan::findOrFail($id); 
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
                ->with('edit_id', $produksi_tahunan->id_produksi);
        }

        $validatedData = $validator->validated();
        $produksi_tahunan->update($validatedData);
        
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
            'ids.*' => 'exists:produksi_tahunan,id_produksi' 
        ]);
        ProduksiTahunan::whereIn('id_produksi', $request->ids)->delete();
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    public function destroy(Request $request, $id) 
    {
        $produksi_tahunan = ProduksiTahunan::findOrFail($id); 
        $produksi_tahunan->delete();

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

    public function searchKegiatan(Request $request) // <-- Hapus $jenisKegiatan
    {
        $request->validate(['query' => 'nullable|string|max:100']);
        $query = $request->input('query', '');
        $kegiatanQuery = MasterKegiatan::query();
        
        // Filter berdasarkan MODUL
        $kegiatanQuery->where('modul', 'produksi_tahunan'); // <-- Ganti Modul

        $data = $kegiatanQuery
            ->where('nama_kegiatan', 'LIKE', "%{$query}%")
            ->limit(10)
            ->select('id_master_kegiatan', 'nama_kegiatan') 
            ->get();
        return response()->json($data);
    }

    public function export(Request $request) // <-- Hapus $jenisKegiatan
    {
        $dataRange = $request->input('dataRange', 'all');
        $dataFormat = $request->input('dataFormat', 'formatted_values');
        $exportFormat = $request->input('exportFormat', 'excel');
        $kegiatan = $request->input('kegiatan');
        $search = $request->input('search');
        $currentPage = $request->input('page', 1);
        $perPageInput = $request->input('per_page', 20);
        $selectedTahun = $request->input('tahun', date('Y')); 
        $perPage = ($perPageInput == 'all' || $dataRange == 'all') ? -1 : (int)$perPageInput; 
        
        $exportClass = new ProduksiTahunanExport( // <-- Ganti Export
            $dataRange,
            $dataFormat,
            null,   // $jenisKegiatan tidak ada lagi
            $kegiatan,        
            $search,
            $currentPage,
            $perPage,
            $selectedTahun    
        );

        $fileName = 'ProduksiTahunan_' . $selectedTahun . '_' . now()->format('YmdHis'); // Ganti Nama File

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
            $import = new ProduksiTahunanImport(); // <-- GANTI IMPORT
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
                    'UpdatingDPA', // Contoh 1
                    'BS001',
                    'Nama Petugas Valid 1', 
                    'Nama Petugas Valid 2', 
                    '2025-12-31',
                    'Belum Selesai',
                    ''
                ],
                [
                    'ListingIMKTahunan', // Contoh 2
                    'BS002',
                    'Nama Petugas Valid 3', 
                    'Nama Petugas Valid 4', 
                    '2025-12-31',
                    'Selesai',
                    '2025-11-25'
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
                '2. "nama_kegiatan" WAJIB terdaftar di Master Kegiatan (modul produksi_tahunan).',
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
            $fileName = 'Template_Import_Produksi_Tahunan.xlsx'; // <-- GANTI NAMA FILE
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