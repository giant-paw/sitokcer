<?php

namespace App\Http\Controllers\Distribusi;

use App\Http\Controllers\Controller;
use App\Models\Distribusi\DistribusiTahunan; // <-- [GANTI]
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DistribusiTahunanExport; // <-- [GANTI]
use App\Imports\DistribusiTahunanImport; // <-- [GANTI]
use Illuminate\Validation\Rule;

class DistribusiTahunanController extends Controller
{
    // Definisikan modul untuk controller ini
    private $currentModul = 'distribusi_tahunan'; // <-- [GANTI]

    /**
     * Tampilkan halaman index, TANPA {jenisKegiatan}
     */
    public function index(Request $request)
    {
        // 1. Logika Filter Tahun 
        $selectedTahun = $request->input('tahun', date('Y'));
        
         $availableTahun = DistribusiTahunan::query() // <-- [GANTI]
             ->leftJoin('master_kegiatan', 'distribusi_tahunan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
             ->where(function($q) {
                 $q->where('master_kegiatan.modul', $this->currentModul)
                   ->orWhereNull('distribusi_tahunan.master_kegiatan_id'); 
             })
             ->select(DB::raw('YEAR(distribusi_tahunan.created_at) as tahun'))
             ->distinct()
             ->whereNotNull('distribusi_tahunan.created_at')
             ->orderBy('tahun', 'desc')
             ->pluck('tahun')
             ->toArray();
             
         if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
             array_unshift($availableTahun, date('Y'));
         }

        // 2. Kueri Utama [PERUBAHAN LOGIKA: LEFT JOIN & MODUL]
        $query = DistribusiTahunan::query() // <-- [GANTI]
            ->leftJoin('master_kegiatan', 'distribusi_tahunan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            // Filter hanya data yang relevan dengan modul ini (bersih) ATAU data kotor (NULL)
            ->where(function($q) {
                 $q->where('master_kegiatan.modul', $this->currentModul)
                   ->orWhereNull('distribusi_tahunan.master_kegiatan_id'); 
            })
            ->whereYear('distribusi_tahunan.created_at', $selectedTahun);

        // 3. Filter Kegiatan Spesifik (Tab) 
        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            if (is_numeric($selectedKegiatan)) {
                $query->where('distribusi_tahunan.master_kegiatan_id', $selectedKegiatan);
            } else {
                $query->whereNull('distribusi_tahunan.master_kegiatan_id')
                      ->where('distribusi_tahunan.nama_kegiatan', $selectedKegiatan);
            }
        }

        // 4. Filter Pencarian 
        $search = $request->input('search', '');
        if ($search !== '') {
             $query->where(function ($q) use ($search) {
                $q->where('distribusi_tahunan.BS_Responden', 'like', "%{$search}%")
                  ->orWhere('distribusi_tahunan.pencacah', 'like', "%{$search}%")
                  ->orWhere('distribusi_tahunan.pengawas', 'like', "%{$search}%")
                  ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$search}%") 
                  ->orWhere('distribusi_tahunan.nama_kegiatan', 'like', "%{$search}%");
            });
        }

        // 5. Logika Pagination
        $perPageInput = $request->input('per_page', 20);
        $perPage = $perPageInput;
        if ($perPageInput == 'all') {
            $countQuery = (clone $query); 
            $countQuery->setEagerLoads([]); 
            $total = $countQuery->count('distribusi_tahunan.id_distribusi');
            $perPage = $total > 0 ? $total : 20;
        }

        // 6. Ambil Data
        $listData = $query
            ->select('distribusi_tahunan.*') 
            ->with('masterKegiatan') 
            ->latest('distribusi_tahunan.id_distribusi') 
            ->paginate($perPage) 
            ->withQueryString(); 

        // 7. Logika Hitung Tab (Dashboard) [COALESCE]
        $kegiatanCounts = DistribusiTahunan::query() // <-- [GANTI]
            ->leftJoin('master_kegiatan', 'distribusi_tahunan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
             ->where(function($q) {
                 $q->where('master_kegiatan.modul', $this->currentModul)
                   ->orWhereNull('distribusi_tahunan.master_kegiatan_id'); 
             })
            ->whereYear('distribusi_tahunan.created_at', $selectedTahun)
            ->select(
                DB::raw('COALESCE(master_kegiatan.id_master_kegiatan, distribusi_tahunan.nama_kegiatan) as filter_value'),
                DB::raw('COALESCE(master_kegiatan.nama_kegiatan, distribusi_tahunan.nama_kegiatan) as display_name'),
                DB::raw('count(distribusi_tahunan.id_distribusi) as total')
            )
            ->groupBy('filter_value', 'display_name')
            ->orderBy('display_name')
            ->get();
            
        // 8. Ambil daftar master murni untuk modul ini (untuk autocomplete)
        $masterKegiatanList = MasterKegiatan::where('modul', $this->currentModul)
                                           ->orderBy('nama_kegiatan')->get();

        // 9. Kirim ke View
        return view('timDistribusi.distribusiTahunan', compact( // <-- [GANTI]
            'listData', 
            'kegiatanCounts', 
            // 'jenisKegiatan' tidak ada lagi
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

        DistribusiTahunan::create($validatedData);

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
        $distribusi_tahunan = DistribusiTahunan::findOrFail($id); 
        $data = $distribusi_tahunan->toArray();
        // Pastikan $data sekarang berisi 'master_kegiatan_id'
        $targetPenyelesaian = $distribusi_tahunan->target_penyelesaian;
        $tanggalPengumpulan = $distribusi_tahunan->tanggal_pengumpulan;
        $data['target_penyelesaian'] = $targetPenyelesaian ? Carbon::parse($targetPenyelesaian)->toDateString() : null;
        $data['tanggal_pengumpulan'] = $tanggalPengumpulan ? Carbon::parse($tanggalPengumpulan)->toDateString() : null;
        return response()->json($data);
    }

    /**
     * Update data.
     */
    public function update(Request $request, $id) 
    {
        $distribusi_tahunan = DistribusiTahunan::findOrFail($id);
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
                ->with('edit_id', $distribusi_tahunan->id_distribusi);
        }

        $validatedData = $validator->validated();
        if ($request->filled('target_penyelesaian')) {
            try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        $distribusi_tahunan->update($validatedData);

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
            'ids.*' => 'exists:distribusi_tahunan,id_distribusi'
        ]);

        DistribusiTahunan::whereIn('id_distribusi', $request->ids)->delete();

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Hapus satu data.
     */
    public function destroy($id) 
    {
        $distribusi_tahunan = DistribusiTahunan::findOrFail($id); 
        $distribusi_tahunan->delete();

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

        $exportClass = new DistribusiTahunanExport(
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

        $fileName = 'DistribusiTahunan_' . $tahun . '_' . now()->format('YmdHis');

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
            $import = new DistribusiTahunanImport($this->currentModul);
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

            // Ambil contoh kegiatan yang VALID untuk modul ini
            $exampleKegiatan1 = MasterKegiatan::where('modul', $this->currentModul)->inRandomOrder()->first();
            $namaKeg1 = $exampleKegiatan1 ? $exampleKegiatan1->nama_kegiatan : 'NAMA_KEGIATAN_VALID_1';
             
            $exampleData = [
                [
                    $namaKeg1,
                    'BS001',
                    'Nama Pencacah Valid', // Ganti dengan nama valid
                    'Nama Pengawas Valid', // Ganti dengan nama valid
                    '2025-01-31',
                    'Belum Selesai',
                    '' // Boleh kosong
                ]
            ];
            $sheet->fromArray($exampleData, null, 'A2');
            $sheet->getStyle('A2:G2')->getFill() // Hanya baris 2
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFF4CC');

            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            $sheet->setCellValue('A4', 'PETUNJUK PENGISIAN:'); // Pindah ke baris 4
            $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A4')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFB4C7E7');

            $instructions = [
                '1. Header (Baris 1) WAJIB ada.',
                '2. Kolom selain "tanggal_pengumpulan" WAJIB diisi.',
                '3. "nama_kegiatan" HARUS SAMA PERSIS dengan data di Master Kegiatan untuk modul ini (Contoh: ' . $namaKeg1 . ').',
                '4. "pencacah" dan "pengawas" HARUS SAMA PERSIS dengan data di Master Petugas.',
                '5. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY.',
                '6. flag_progress: "Belum Selesai" atau "Selesai".',
                '7. HAPUS baris contoh (baris 2) dan petunjuk ini sebelum import!',
            ];
            $instructionRow = 5; // Mulai dari baris 5
            foreach ($instructions as $instruction) {
                $sheet->setCellValue('A' . $instructionRow, $instruction);
                $sheet->getStyle('A' . $instructionRow)->getFont()->setItalic(true);
                $sheet->mergeCells("A{$instructionRow}:G{$instructionRow}");
                $instructionRow++;
            }
            
            $sheet->freezePane('A2');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'Template_Import_Distribusi_Tahunan.xlsx';
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