<?php

namespace App\Http\Controllers\Sosial; // <-- [GANTI] Namespace

use App\Http\Controllers\Controller;
use App\Models\Sosial\SosialTahunan; // <-- [GANTI] Model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SosialTahunanExport; // <-- [GANTI] Export
use App\Imports\SosialTahunanImport; // <-- [GANTI] Import
use Illuminate\Validation\Rule;

class SosialTahunanController extends Controller // <-- [GANTI] Class
{
    // Definisikan modul untuk controller ini
    private $currentModul = 'sosial_tahunan'; // <-- [GANTI] Modul

    /**
     * Tampilkan halaman index, TANPA {jenisKegiatan}
     */
    public function index(Request $request)
    {
        // 1. Logika Filter Tahun 
        $selectedTahun = $request->input('tahun', date('Y'));
        
         $availableTahun = SosialTahunan::query() // <-- [GANTI]
            ->leftJoin('master_kegiatan', 'sosial_tahunan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) {
                $q->where('master_kegiatan.modul', $this->currentModul)
                  ->orWhereNull('sosial_tahunan.master_kegiatan_id'); 
            })
            ->select(DB::raw('YEAR(sosial_tahunan.created_at) as tahun'))
            ->distinct()
            ->whereNotNull('sosial_tahunan.created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
            
        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // 2. Kueri Utama [PERUBAHAN LOGIKA: LEFT JOIN & MODUL]
        $query = SosialTahunan::query() // <-- [GANTI]
            ->leftJoin('master_kegiatan', 'sosial_tahunan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            // Filter hanya data yang relevan dengan modul ini (bersih) ATAU data kotor (NULL)
            ->where(function($q) {
                $q->where('master_kegiatan.modul', $this->currentModul)
                  ->orWhereNull('sosial_tahunan.master_kegiatan_id'); 
            })
            ->whereYear('sosial_tahunan.created_at', $selectedTahun);

        // 3. Filter Kegiatan Spesifik (Tab) 
        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            if (is_numeric($selectedKegiatan)) {
                $query->where('sosial_tahunan.master_kegiatan_id', $selectedKegiatan);
            } else {
                $query->whereNull('sosial_tahunan.master_kegiatan_id')
                      ->where('sosial_tahunan.nama_kegiatan', $selectedKegiatan);
            }
        }

        // 4. Filter Pencarian 
        $search = $request->input('search', '');
        if ($search !== '') {
             $query->where(function ($q) use ($search) {
                 $q->where('sosial_tahunan.BS_Responden', 'like', "%{$search}%")
                   ->orWhere('sosial_tahunan.pencacah', 'like', "%{$search}%")
                   ->orWhere('sosial_tahunan.pengawas', 'like', "%{$search}%")
                   ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$search}%") 
                   ->orWhere('sosial_tahunan.nama_kegiatan', 'like', "%{$search}%");
             });
        }

        // 5. Logika Pagination
        $perPageInput = $request->input('per_page', 20);
        $perPage = $perPageInput;
        if ($perPageInput == 'all') {
            $countQuery = (clone $query); 
            $countQuery->setEagerLoads([]); 
            $total = $countQuery->count('sosial_tahunan.id_sosial'); // <-- [GANTI] PK
            $perPage = $total > 0 ? $total : 20;
        }

        // 6. Ambil Data
        $listData = $query
            ->select('sosial_tahunan.*') 
            ->with('masterKegiatan') 
            ->latest('sosial_tahunan.id_sosial') // <-- [GANTI] PK
            ->paginate($perPage) 
            ->withQueryString(); 

        // 7. Logika Hitung Tab (Dashboard) [COALESCE]
        $kegiatanCounts = SosialTahunan::query() // <-- [GANTI]
            ->leftJoin('master_kegiatan', 'sosial_tahunan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
             ->where(function($q) {
                 $q->where('master_kegiatan.modul', $this->currentModul)
                   ->orWhereNull('sosial_tahunan.master_kegiatan_id'); 
             })
            ->whereYear('sosial_tahunan.created_at', $selectedTahun)
            ->select(
                DB::raw('COALESCE(master_kegiatan.id_master_kegiatan, sosial_tahunan.nama_kegiatan) as filter_value'),
                DB::raw('COALESCE(master_kegiatan.nama_kegiatan, sosial_tahunan.nama_kegiatan) as display_name'),
                DB::raw('count(sosial_tahunan.id_sosial) as total') // <-- [GANTI] PK
            )
            ->groupBy('filter_value', 'display_name')
            ->orderBy('display_name')
            ->get();
            
        // 8. Ambil daftar master murni untuk modul ini (untuk autocomplete)
        $masterKegiatanList = MasterKegiatan::where('modul', $this->currentModul)
                                            ->orderBy('nama_kegiatan')->get();

        // 9. Kirim ke View
        return view('timSosial.tahunan.sosialTahunan', compact( // <-- [GANTI] View
            'listData', 
            'kegiatanCounts', 
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
            'nama_kegiatan'       => 'required|string|max:255',
            'BS_Responden'        => 'required|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'       => ['required', Rule::in(['Belum Selesai', 'Selesai'])], 
            'tanggal_pengumpulan' => 'nullable|date',
        ];
        
        $customMessages = [
            'master_kegiatan_id.required' => 'Kegiatan wajib dipilih dari daftar.',
            'master_kegiatan_id.exists'   => 'ID Kegiatan tidak terdaftar di master.',
            'pencacah.exists'             => 'Nama pencacah tidak terdaftar di master petugas.',
            'pengawas.exists'             => 'Nama pengawas tidak terdaftar di master petugas.',
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
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; 
            } catch (\Exception $e) {
                // Abaikan jika parsing gagal
            }
        }

        SosialTahunan::create($validatedData); // <-- [GANTI]

        session()->flash('success', 'Data berhasil ditambahkan!');
        session()->flash('auto_hide', true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back();
    }

    /**
     * Ambil data untuk modal edit.
     */
    public function edit($id) 
    {
        $sosial_tahunan = SosialTahunan::findOrFail($id); // <-- [GANTI]
        $data = $sosial_tahunan->toArray();
        // Pastikan $data sekarang berisi 'master_kegiatan_id'
        $targetPenyelesaian = $sosial_tahunan->target_penyelesaian;
        $tanggalPengumpulan = $sosial_tahunan->tanggal_pengumpulan;
        $data['target_penyelesaian'] = $targetPenyelesaian ? Carbon::parse($targetPenyelesaian)->toDateString() : null;
        $data['tanggal_pengumpulan'] = $tanggalPengumpulan ? Carbon::parse($tanggalPengumpulan)->toDateString() : null;
        return response()->json($data);
    }

    /**
     * Update data.
     */
    public function update(Request $request, $id) 
    {
        $sosial_tahunan = SosialTahunan::findOrFail($id); // <-- [GANTI]
        $baseRules = [
            'master_kegiatan_id' => 'required|integer|exists:master_kegiatan,id_master_kegiatan', 
            'nama_kegiatan'       => 'required|string|max:255',
            'BS_Responden'        => 'required|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'       => ['required', Rule::in(['Belum Selesai', 'Selesai'])],
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
            return back()->withErrors($validator)->withInput()
                ->with('error_modal', 'editDataModal')
                ->with('edit_id', $sosial_tahunan->id_sosial); // <-- [GANTI] PK
        }

        $validatedData = $validator->validated();
        if ($request->filled('target_penyelesaian')) {
            try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        $sosial_tahunan->update($validatedData);

        session()->flash('success', 'Data berhasil diperbarui!');
        session()->flash('auto_hide', true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        return back();
    }
    
    /**
     * Hapus banyak data.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:sosial_tahunan,id_sosial' // <-- [GANTI]
        ]);

        SosialTahunan::whereIn('id_sosial', $request->ids)->delete(); // <-- [GANTI]

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Hapus satu data.
     */
    public function destroy($id) 
    {
        $sosial_tahunan = SosialTahunan::findOrFail($id); // <-- [GANTI]
        $sosial_tahunan->delete();

        session()->flash('success', 'Data berhasil dihapus!');
        session()->flash('auto_hide', true);

        if (request()->ajax() || request()->wantsJson()) {
             return response()->json(['success' => 'Data berhasil dihapus!']); 
        }

        return back(); 
    }

    /**
     * Cari petugas (autocomplete).
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
     * Cari kegiatan (autocomplete).
     */
     public function searchKegiatan(Request $request) // Hapus parameter $jenisKegiatan
     {
        $request->validate(['query' => 'nullable|string|max:100']);
        $query = $request->input('query', '');
        $kegiatanQuery = MasterKegiatan::query();

        // Filter WAJIB berdasarkan MODUL
        $kegiatanQuery->where('modul', $this->currentModul);
        // Hapus filter prefix

        // Filter berdasarkan ketikan user
        $data = $kegiatanQuery
            ->where('nama_kegiatan', 'LIKE', "%{$query}%")
            ->limit(10)
            ->select('id_master_kegiatan', 'nama_kegiatan') 
            ->get(); 
        return response()->json($data);
     }

    /**
     * Ekspor data.
     */
    public function export(Request $request) // Hapus parameter $jenisKegiatan
    {
        $dataRange = $request->input('dataRange', 'all');
        $dataFormat = $request->input('dataFormat', 'formatted_values');
        $exportFormat = $request->input('exportFormat', 'excel');
        $kegiatan = $request->input('kegiatan'); 
        $search = $request->input('search');
        $tahun = $request->input('tahun', date('Y')); 
        $currentPage = $request->input('page', 1);
        $perPageInput = $request->input('per_page', 20);

        $perPage = ($perPageInput == 'all' || $dataRange == 'all') ? -1 : (int)$perPageInput;

        $exportClass = new SosialTahunanExport( // <-- [GANTI]
            $dataRange,
            $dataFormat,
            // $jenisKegiatan dihapus
            $kegiatan,      
            $search,
            $tahun,         
            $currentPage,
            $perPage,
            $this->currentModul 
        );

        $fileName = 'SosialTahunan_' . $tahun . '_' . now()->format('YmdHis'); // <-- [GANTI]

        if ($exportFormat == 'excel') {
            return Excel::download($exportClass, $fileName . '.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV, [
                'Content-Type' => 'text/csv',
             ]);
        }

        return back()->with('error', 'Format ekspor tidak didukung.');
    }

    /**
     * Impor data.
     */
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
            // Kirim modul saat ini ke Importer
            $import = new SosialTahunanImport($this->currentModul); // <-- [GANTI]
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

    /**
     * Download template impor.
     */
    public function downloadTemplate()
    {
         try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // 1. Setup Header
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

            // Style Header
            $headerStyle = $sheet->getStyle('A1:G1');
            $headerStyle->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF'); // Font Putih
            $headerStyle->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4472C4'); // Background Biru Rapi

            // 2. Data Contoh (Ambil real dari master agar user tidak bingung)
            $exampleKegiatan = MasterKegiatan::where('modul', $this->currentModul)->first();
            $namaKeg = $exampleKegiatan ? $exampleKegiatan->nama_kegiatan : 'CONTOH_KEGIATAN';
            
            $exampleData = [
                [
                    $namaKeg,
                    'BS001',
                    'Nama Pencacah (Sesuai Master)',
                    'Nama Pengawas (Sesuai Master)',
                    date('Y-m-d', strtotime('+1 month')), // Contoh tanggal bulan depan
                    'Belum Selesai',
                    '' // Dikosongkan untuk contoh
                ]
            ];
            $sheet->fromArray($exampleData, null, 'A2');

            // Style Baris Contoh
            $sheet->getStyle('A2:G2')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFF4CC'); // Kuning muda

            // Auto Width Columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // 3. Instruksi Pengisian (Rapi & Clean)
            $sheet->setCellValue('A4', 'PETUNJUK PENGISIAN (HAPUS SEBELUM UPLOAD):');
            $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11);
            
            // List Instruksi Baru (Tanpa kalimat "Kolom selain... WAJIB diisi")
            $instructions = [
                '1. Jangan mengubah atau menghapus Baris Header (Baris 1).',
                '2. "nama_kegiatan" harus sama persis dengan yang ada di Aplikasi.',
                '3. "pencacah" dan "pengawas" harus terdaftar di Master Petugas.',
                '4. Gunakan format tanggal YYYY-MM-DD (Contoh: 2025-01-31).',
                '5. Opsi Flag Progress: "Belum Selesai" atau "Selesai".',
                '6. Hapus baris contoh (warna kuning) sebelum file di-upload.'
            ];

            $rowNum = 5;
            foreach ($instructions as $inst) {
                $sheet->setCellValue('A' . $rowNum, $inst);
                $sheet->mergeCells("A{$rowNum}:G{$rowNum}"); // Merge agar teks panjang muat
                $rowNum++;
            }
            
            // Freeze Pane agar header tetap terlihat saat scroll
            $sheet->freezePane('A2');

            // Generate File
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'Template_Import_Sosial_Tahunan.xlsx';
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($temp_file);

            return response()->download($temp_file, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

         } catch (\Exception $e) {
            \Log::error('Template Error: '. $e->getMessage());
            return back()->with('error', 'Gagal membuat template.');
         }
    }
}