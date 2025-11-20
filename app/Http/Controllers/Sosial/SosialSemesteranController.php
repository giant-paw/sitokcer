<?php

namespace App\Http\Controllers\Sosial;

use App\Http\Controllers\Controller;
use App\Models\Sosial\SosialSemesteran; // <-- [GANTI]
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SosialSemesteranExport; // <-- [GANTI]
use App\Imports\SosialSemesteranImport; // <-- [GANTI]
use Illuminate\Validation\Rule;

class SosialSemesteranController extends Controller // <-- [GANTI]
{
    // Definisikan modul untuk controller ini
    private $currentModul = 'sosial_semesteran'; // <-- [GANTI]

    public function index(Request $request)
    {
        // 1. Logika Filter Tahun 
        $selectedTahun = $request->input('tahun', date('Y'));
        
         $availableTahun = SosialSemesteran::query() // <-- [GANTI]
            ->leftJoin('master_kegiatan', 'sosial_semesteran.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) {
                $q->where('master_kegiatan.modul', $this->currentModul)
                  ->orWhereNull('sosial_semesteran.master_kegiatan_id'); 
            })
            ->select(DB::raw('YEAR(sosial_semesteran.created_at) as tahun'))
            ->distinct()
            ->whereNotNull('sosial_semesteran.created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
            
        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // 2. Kueri Utama
        $query = SosialSemesteran::query() // <-- [GANTI]
            ->leftJoin('master_kegiatan', 'sosial_semesteran.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->where(function($q) {
                $q->where('master_kegiatan.modul', $this->currentModul)
                  ->orWhereNull('sosial_semesteran.master_kegiatan_id'); 
            })
            ->whereYear('sosial_semesteran.created_at', $selectedTahun);

        // 3. Filter Kegiatan Spesifik (Tab) 
        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            if (is_numeric($selectedKegiatan)) {
                $query->where('sosial_semesteran.master_kegiatan_id', $selectedKegiatan);
            } else {
                $query->whereNull('sosial_semesteran.master_kegiatan_id')
                      ->where('sosial_semesteran.nama_kegiatan', $selectedKegiatan);
            }
        }

        // 4. Filter Pencarian 
        $search = $request->input('search', '');
        if ($search !== '') {
             $query->where(function ($q) use ($search) {
                 $q->where('sosial_semesteran.BS_Responden', 'like', "%{$search}%")
                   ->orWhere('sosial_semesteran.pencacah', 'like', "%{$search}%")
                   ->orWhere('sosial_semesteran.pengawas', 'like', "%{$search}%")
                   ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$search}%") 
                   ->orWhere('sosial_semesteran.nama_kegiatan', 'like', "%{$search}%");
             });
        }

        // 5. Logika Pagination
        $perPageInput = $request->input('per_page', 20);
        $perPage = $perPageInput;
        if ($perPageInput == 'all') {
            $countQuery = (clone $query); 
            $countQuery->setEagerLoads([]); 
            $total = $countQuery->count('sosial_semesteran.id_sosial_semesteran'); // <-- [GANTI] PK
            $perPage = $total > 0 ? $total : 20;
        }

        // 6. Ambil Data
        $listData = $query
            ->select('sosial_semesteran.*') 
            ->with('masterKegiatan') 
            ->latest('sosial_semesteran.id_sosial_semesteran') // <-- [GANTI] PK
            ->paginate($perPage) 
            ->withQueryString(); 

        // 7. Logika Hitung Tab (Dashboard)
        $kegiatanCounts = SosialSemesteran::query() // <-- [GANTI]
            ->leftJoin('master_kegiatan', 'sosial_semesteran.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
             ->where(function($q) {
                 $q->where('master_kegiatan.modul', $this->currentModul)
                   ->orWhereNull('sosial_semesteran.master_kegiatan_id'); 
             })
            ->whereYear('sosial_semesteran.created_at', $selectedTahun)
            ->select(
                DB::raw('COALESCE(sosial_semesteran.master_kegiatan_id, sosial_semesteran.nama_kegiatan) as filter_value'),
                DB::raw('COALESCE(master_kegiatan.nama_kegiatan, sosial_semesteran.nama_kegiatan) as display_name'),
                DB::raw('count(sosial_semesteran.id_sosial_semesteran) as total') // <-- [GANTI] PK
            )
            ->groupBy('filter_value', 'display_name')
            ->orderBy('display_name')
            ->get();
            
        // 8. Ambil daftar master murni untuk modul ini (untuk autocomplete)
        $masterKegiatanList = MasterKegiatan::where('modul', $this->currentModul)
                                            ->orderBy('nama_kegiatan')->get();

        // 9. Kirim ke View
        return view('timSosial.semesteran.sosialSemesteran', compact( // <-- [GANTI] View
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
            'flag_progress'       => ['required', Rule::in(['Belum Selesai', 'Selesai', 'Proses'])], 
            'tanggal_pengumpulan' => 'nullable|date',
        ];
        
        $customMessages = [
            'master_kegiatan_id.required' => 'Kegiatan wajib dipilih dari daftar.',
            'pencacah.exists'             => 'Nama pencacah tidak terdaftar di master petugas.',
            'pengawas.exists'             => 'Nama pengawas tidak terdaftar di master petugas.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error_modal', 'tambahDataModal');
        }

        SosialSemesteran::create($validator->validated()); // <-- [GANTI]

        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    public function edit($id) 
    {
        $item = SosialSemesteran::findOrFail($id); // <-- [GANTI]
        $data = $item->toArray();
        
        // Format tanggal agar sesuai dengan <input type="date">
        $data['target_penyelesaian'] = $item->target_penyelesaian ? $item->target_penyelesaian->format('Y-m-d') : null;
        $data['tanggal_pengumpulan'] = $item->tanggal_pengumpulan ? $item->tanggal_pengumpulan->format('Y-m-d') : null;
        
        return response()->json($data);
    }

    public function update(Request $request, $id) 
    {
        $item = SosialSemesteran::findOrFail($id); // <-- [GANTI]
        $baseRules = [
            'master_kegiatan_id' => 'required|integer|exists:master_kegiatan,id_master_kegiatan', 
            'nama_kegiatan'       => 'required|string|max:255',
            'BS_Responden'        => 'required|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'       => ['required', Rule::in(['Belum Selesai', 'Selesai', 'Proses'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];
        $customMessages = [
            'master_kegiatan_id.required' => 'Kegiatan wajib dipilih dari daftar.',
            'pencacah.exists'             => 'Nama pencacah tidak terdaftar.',
            'pengawas.exists'             => 'Nama pengawas tidak terdaftar.',
        ];
        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            return back()->withErrors($validator, 'editForm')
                         ->withInput()
                         ->with('error_modal', 'editDataModal')
                         ->with('edit_id', $item->id_sosial_semesteran); // <-- [GANTI] PK
        }

        $item->update($validator->validated());

        return back()->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }
    
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:sosial_semesteran,id_sosial_semesteran' // <-- [GANTI]
        ]);

        SosialSemesteran::whereIn('id_sosial_semesteran', $request->ids)->delete(); // <-- [GANTI]

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    public function destroy($id) 
    {
        $item = SosialSemesteran::findOrFail($id); // <-- [GANTI]
        $item->delete();

        return back()->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
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

     public function searchKegiatan(Request $request)
     {
        $request->validate(['query' => 'nullable|string|max:100']);
        $query = $request->input('query', '');
        
        $data = MasterKegiatan::query()
            ->where('modul', $this->currentModul) // Filter WAJIB berdasarkan MODUL
            ->where('nama_kegiatan', 'LIKE', "%{$query}%")
            ->limit(10)
            ->select('id_master_kegiatan', 'nama_kegiatan') 
            ->get(); 
        return response()->json($data);
     }

    public function export(Request $request)
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

        $exportClass = new SosialSemesteranExport( // <-- [GANTI]
            $dataRange, $dataFormat, $kegiatan, $search, $tahun, $currentPage, $perPage,
            $this->currentModul 
        );

        $fileName = 'SosialSemesteran_' . $tahun . '_' . now()->format('YmdHis'); // <-- [GANTI]

        if ($exportFormat == 'excel') {
            return Excel::download($exportClass, $fileName . '.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return back()->with('error', 'Format ekspor tidak didukung.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048' 
        ],[
            'file.required' => 'File Excel/CSV wajib diunggah.',
        ]);

        try {
            $file = $request->file('file');
            $import = new SosialSemesteranImport($this->currentModul); // <-- [GANTI]
            Excel::import($import, $file);

            $errors = $import->getErrors(); 
            $successCount = $import->getSuccessCount(); 

            if (!empty($errors)) {
                 $formattedErrors = collect($errors)->map(fn($err) => [
                     'row' => $err['row'] ?? '?', 'error' => $err['error'] ?? 'Unknown Error', 'values' => $err['values'] ?? 'N/A'
                 ])->toArray();
                 return back()->with('import_errors', $formattedErrors)
                              ->with('success_count', $successCount) 
                              ->with('warning', "Import selesai dengan {$successCount} data berhasil dan " . count($errors) . " data gagal.");
            }
            return back()->with(['success' => "Berhasil mengimpor {$successCount} data!", 'auto_hide' => true]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
             $failures = $e->failures();
             $formattedErrors = [];
             foreach ($failures as $failure) {
                 $formattedErrors[] = ['row' => $failure->row(), 'error' => implode(', ', $failure->errors()), 'values' => $failure->values()[$failure->attribute()] ?? 'N/A'];
             }
             return back()->with('import_errors', $formattedErrors)->with('error', 'Import gagal.');
        } catch (\Exception $e) {
            \Log::error('Import Error: ' . $e->getMessage()); 
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
         try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = ['nama_kegiatan', 'bs_responden', 'pencacah', 'pengawas', 'target_penyelesaian', 'flag_progress', 'tanggal_pengumpulan'];
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);
            $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAD3');

            $exampleKegiatan1 = MasterKegiatan::where('modul', $this->currentModul)->inRandomOrder()->first();
            $namaKeg1 = $exampleKegiatan1 ? $exampleKegiatan1->nama_kegiatan : 'Sakernas Februari';
            
            $petugas1 = MasterPetugas::inRandomOrder()->first();
            $namaPetugas1 = $petugas1 ? $petugas1->nama_petugas : 'Nama Pencacah Valid';
            $petugas2 = MasterPetugas::inRandomOrder()->skip(1)->first();
            $namaPetugas2 = $petugas2 ? $petugas2->nama_petugas : 'Nama Pengawas Valid';

            $exampleData = [[ $namaKeg1, 'BS001', $namaPetugas1, $namaPetugas2, '2025-02-28', 'Belum Selesai', '' ]];
            $sheet->fromArray($exampleData, null, 'A2');
            $sheet->getStyle('A2:G2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFF4CC');

            foreach (range('A', 'G') as $col) $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->setCellValue('A4', 'PETUNJUK PENGISIAN:');
            $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFB4C7E7');

            $instructions = [
                '1. "nama_kegiatan" HARUS SAMA PERSIS dengan data di Master Kegiatan (Contoh: ' . $namaKeg1 . ').',
                '2. "pencacah" dan "pengawas" HARUS SAMA PERSIS dengan data di Master Petugas.',
                '3. flag_progress: "Belum Selesai", "Selesai", atau "Proses".',
                '4. HAPUS baris contoh (baris 2) dan petunjuk ini sebelum import!',
            ];
            $instructionRow = 5;
            foreach ($instructions as $instruction) {
                $sheet->setCellValue('A' . $instructionRow, $instruction);
                $sheet->getStyle('A' . $instructionRow)->getFont()->setItalic(true);
                $sheet->mergeCells("A{$instructionRow}:G{$instructionRow}");
                $instructionRow++;
            }
            
            $sheet->freezePane('A2');
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $fileName = 'Template_Import_Sosial_Semesteran.xlsx'; // <-- [GANTI]
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($temp_file);

            return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
         } catch (\Exception $e) {
            \Log::error('Template Download Error: '. $e->getMessage());
            return back()->with('error', 'Gagal membuat template: ' . $e->getMessage());
         }
    }
}