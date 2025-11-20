<?php

namespace App\Http\Controllers\Sosial;

use App\Http\Controllers\Controller;
use App\Models\Sosial\SosialTriwulanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SosialTriwulananExport;
use App\Imports\SosialTriwulananImport;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class SosialTriwulanController extends Controller 
{
    private function getKegiatanPrefix($jenisKegiatan)
    {
        if (empty($jenisKegiatan)) {
            return null;
        }
        // Mengubah 'seruti' menjadi 'Seruti'
        return Str::studly($jenisKegiatan);
    }

    /**
     * Tampilkan halaman index, DENGAN {jenisKegiatan}
     */
    public function index(Request $request, $jenisKegiatan = null)
    {
        // Jika tidak ada jenisKegiatan (misal: akses /sosial/triwulanan/ ), redirect
        if (empty($jenisKegiatan)) {
            // Redirect ke jenis default, misal 'seruti'
            return redirect()->route('sosial.triwulanan.index', ['jenisKegiatan' => 'seruti']);
        }
        
        $kegiatanPrefix = $this->getKegiatanPrefix($jenisKegiatan);
        
        // 1. Logika Filter Tahun 
        $selectedTahun = $request->input('tahun', date('Y'));
        
         $availableTahun = SosialTriwulanan::query() // <-- [GANTI]
            ->where('nama_kegiatan', 'LIKE', $kegiatanPrefix . '%') // Filter by Prefix
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->whereNotNull('created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
            
        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // 2. Kueri Utama
        $query = SosialTriwulanan::query() // <-- [GANTI]
            ->where('nama_kegiatan', 'LIKE', $kegiatanPrefix . '%') // Filter by Prefix
            ->whereYear('created_at', $selectedTahun);

        // 3. Filter Kegiatan Spesifik (Tab) 
        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            if (is_numeric($selectedKegiatan)) {
                $query->where('master_kegiatan_id', $selectedKegiatan);
            } else {
                $query->whereNull('master_kegiatan_id')
                      ->where('nama_kegiatan', $selectedKegiatan);
            }
        }

        // 4. Filter Pencarian 
        $search = $request->input('search', '');
        if ($search !== '') {
             $query->where(function ($q) use ($search) {
                 $q->where('BS_Responden', 'like', "%{$search}%")
                   ->orWhere('pencacah', 'like', "%{$search}%")
                   ->orWhere('pengawas', 'like', "%{$search}%")
                   ->orWhere('nama_kegiatan', 'like', "%{$search}%");
             });
        }

        // 5. Logika Pagination
        $perPageInput = $request->input('per_page', 20);
        $perPage = $perPageInput;
        if ($perPageInput == 'all') {
            $countQuery = (clone $query); 
            $countQuery->setEagerLoads([]); 
            $total = $countQuery->count('id_sosial_triwulanan'); // <-- [GANTI] PK
            $perPage = $total > 0 ? $total : 20;
        }

        // 6. Ambil Data
        $listData = $query
            ->select('sosial_triwulanan.*') 
            ->with('masterKegiatan') 
            ->latest('id_sosial_triwulanan') // <-- [GANTI] PK
            ->paginate($perPage) 
            ->withQueryString(); 

        // 7. Logika Hitung Tab (Dashboard)
        $kegiatanCounts = SosialTriwulanan::query() // <-- [GANTI]
            // Tentukan tabel secara spesifik
            ->where('sosial_triwulanan.nama_kegiatan', 'LIKE', $kegiatanPrefix . '%') // <-- FIX 1
            ->whereYear('sosial_triwulanan.created_at', $selectedTahun) // <-- FIX 2
            ->select(
                // Tentukan tabel untuk semua kolom yang ambigu
                DB::raw('COALESCE(sosial_triwulanan.master_kegiatan_id, sosial_triwulanan.nama_kegiatan) as filter_value'), // <-- FIX 3
                DB::raw('COALESCE(master_kegiatan.nama_kegiatan, sosial_triwulanan.nama_kegiatan) as display_name'),
                DB::raw('count(sosial_triwulanan.id_sosial_triwulanan) as total') // <-- FIX 4 (Ganti PK & spesifik)
            )
            ->leftJoin('master_kegiatan', 'sosial_triwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            ->groupBy('filter_value', 'display_name')
            ->orderBy('display_name')
            ->get();
            
        // 8. Ambil daftar master murni untuk modul ini (untuk autocomplete)
        $masterKegiatanList = MasterKegiatan::where('nama_kegiatan', 'LIKE', $kegiatanPrefix . '%') // Filter by Prefix
                                            ->orderBy('nama_kegiatan')->get();

        // 9. Kirim ke View
        return view('timSosial.triwulanan.sosialTriwulanan', compact( // <-- [GANTI] View
            'listData', 
            'kegiatanCounts', 
            'jenisKegiatan', // Kirim jenisKegiatan ke view!
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
            'flag_progress'      => ['required', Rule::in(['Belum Selesai', 'Selesai', 'Proses'])], 
            'tanggal_pengumpulan' => 'nullable|date',
            'jenisKegiatan'      => 'required|string', 
        ];
        
        $customMessages = [
            'master_kegiatan_id.required' => 'Kegiatan wajib dipilih dari daftar.',
            'pencacah.exists'             => 'Nama pencacah tidak terdaftar di master petugas.',
            'pengawas.exists'             => 'Nama pengawas tidak terdaftar di master petugas.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            // JIKA INI ADALAH AJAX (dari modal JS Anda)
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // Fallback jika non-JS (meskipun skrip Anda menangani ini)
            return back()->withErrors($validator)->withInput()->with('error_modal', 'tambahDataModal');
        }

        // Ambil data yang divalidasi, kecuali 'jenisKegiatan'
        $validatedData = $validator->safe()->except('jenisKegiatan');

        SosialTriwulanan::create($validatedData);

        // JIKA INI ADALAH AJAX
        if ($request->wantsJson()) {
            // Kirim balasan sukses sebagai JSON
            return response()->json([
                'success' => true, 
                'message' => 'Data berhasil ditambahkan!'
            ]);
        }
        
        // Fallback jika non-JS
        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    public function edit($id) 
    {
        $sosial_triwulanan = SosialTriwulanan::findOrFail($id); // <-- [GANTI]
        $data = $sosial_triwulanan->toArray();

        // Format tanggal untuk input type="date"
        $data['target_penyelesaian'] = $sosial_triwulanan->target_penyelesaian ? $sosial_triwulanan->target_penyelesaian->format('Y-m-d') : null;
        $data['tanggal_pengumpulan'] = $sosial_triwulanan->tanggal_pengumpulan ? $sosial_triwulanan->tanggal_pengumpulan->format('Y-m-d') : null;

        return response()->json($data);
    }

    public function update(Request $request, $id) 
    {
        $sosial_triwulanan = SosialTriwulanan::findOrFail($id); 
        $baseRules = [
            'master_kegiatan_id' => 'required|integer|exists:master_kegiatan,id_master_kegiatan', 
            'nama_kegiatan'      => 'required|string|max:255',
            'BS_Responden'       => 'required|string|max:255',
            'pencacah'           => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'           => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'      => ['required', Rule::in(['Belum Selesai', 'Selesai', 'Proses'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];
        $customMessages = [
            'master_kegiatan_id.required' => 'Kegiatan wajib dipilih dari daftar.',
            'pencacah.exists'             => 'Nama pencacah tidak terdaftar.',
            'pengawas.exists'             => 'Nama pengawas tidak terdaftar.',
        ];
        
        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            // JIKA INI ADALAH AJAX
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // Fallback jika non-JS
            return back()->withErrors($validator, 'editForm') 
                      ->withInput()
                      ->with('error_modal', 'editDataModal')
                      ->with('edit_id', $sosial_triwulanan->id_sosial_triwulanan);
        }

        $sosial_triwulanan->update($validator->validated());

        // JIKA INI ADALAH AJAX
        if ($request->wantsJson()) {
            // Kirim balasan sukses sebagai JSON
            return response()->json([
                'success' => true, 
                'message' => 'Data berhasil diperbarui!'
            ]);
        }
        
        // Fallback jika non-JS
        return back()->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }
    
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:sosial_triwulanan,id_sosial_triwulanan' // <-- [GANTI]
        ]);

        SosialTriwulanan::whereIn('id_sosial_triwulanan', $request->ids)->delete(); // <-- [GANTI]

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    public function destroy($id) 
    {
        $sosial_triwulanan = SosialTriwulanan::findOrFail($id); // <-- [GANTI]
        $sosial_triwulanan->delete();

        return back()->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    public function searchPetugas(Request $request)
    {
        // Fungsi ini tidak terpengaruh oleh jenisKegiatan
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
        $request->validate([
            'query' => 'nullable|string|max:100',
            'jenisKegiatan' => 'required|string' // Wajib ada dari JS
        ]);
        
        $query = $request->input('query', '');
        $kegiatanPrefix = $this->getKegiatanPrefix($request->input('jenisKegiatan'));
        
        $data = MasterKegiatan::query()
            ->where('nama_kegiatan', 'LIKE', $kegiatanPrefix . '%') // Filter by Prefix
            ->where('nama_kegiatan', 'LIKE', "%{$query}%") // Filter by user query
            ->limit(10)
            ->select('id_master_kegiatan', 'nama_kegiatan') 
            ->get(); 
        return response()->json($data);
     }

    public function export(Request $request, $jenisKegiatan) // <-- Ambil dari URL
    {
        $kegiatanPrefix = $this->getKegiatanPrefix($jenisKegiatan);
        
        $dataRange = $request->input('dataRange', 'all');
        $dataFormat = $request->input('dataFormat', 'formatted_values');
        $exportFormat = $request->input('exportFormat', 'excel');
        $kegiatan = $request->input('kegiatan'); 
        $search = $request->input('search');
        $tahun = $request->input('tahun', date('Y')); 
        $currentPage = $request->input('page', 1);
        $perPageInput = $request->input('per_page', 20);

        $perPage = ($perPageInput == 'all' || $dataRange == 'all') ? -1 : (int)$perPageInput;

        $exportClass = new SosialTriwulananExport( // <-- [GANTI]
            $dataRange, $dataFormat, $kegiatan, $search, $tahun, $currentPage, $perPage,
            $kegiatanPrefix // <-- Kirim Prefix
        );

        $fileName = 'SosialTriwulanan_' . $kegiatanPrefix . '_' . $tahun . '_' . now()->format('YmdHis'); // <-- [GANTI]

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
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            'jenisKegiatan' => 'required|string' // Wajib ada dari Form
        ],[
            'file.required' => 'File Excel/CSV wajib diunggah.',
        ]);

        try {
            $file = $request->file('file');
            $kegiatanPrefix = $this->getKegiatanPrefix($request->input('jenisKegiatan'));

            $import = new SosialTriwulananImport($kegiatanPrefix); // <-- [GANTI] Kirim Prefix
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

    public function downloadTemplate(Request $request)
    {
        $request->validate(['jenisKegiatan' => 'required|string']);
        $kegiatanPrefix = $this->getKegiatanPrefix($request->input('jenisKegiatan'));
        
         try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = ['nama_kegiatan', 'bs_responden', 'pencacah', 'pengawas', 'target_penyelesaian', 'flag_progress', 'tanggal_pengumpulan'];
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle('A1:G1')->getFont()->setBold(true);
            $sheet->getStyle('A1:G1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAD3');

            // Ambil contoh kegiatan VALID berdasarkan prefix
            $exampleKegiatan1 = MasterKegiatan::where('nama_kegiatan', 'LIKE', $kegiatanPrefix . '%')->inRandomOrder()->first();
            $namaKeg1 = $exampleKegiatan1 ? $exampleKegiatan1->nama_kegiatan : $kegiatanPrefix . '-Contoh';
            
            $petugas1 = MasterPetugas::inRandomOrder()->first();
            $namaPetugas1 = $petugas1 ? $petugas1->nama_petugas : 'Nama Pencacah Valid';
            $petugas2 = MasterPetugas::inRandomOrder()->skip(1)->first();
            $namaPetugas2 = $petugas2 ? $petugas2->nama_petugas : 'Nama Pengawas Valid';

            $exampleData = [[ $namaKeg1, 'BS001', $namaPetugas1, $namaPetugas2, '2025-01-31', 'Belum Selesai', '' ]];
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
            $fileName = 'Template_Import_Sosial_Triwulanan_' . $kegiatanPrefix . '.xlsx'; // <-- [GANTI]
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($temp_file);

            return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
         } catch (\Exception $e) {
            \Log::error('Template Download Error: '. $e->getMessage());
            return back()->with('error', 'Gagal membuat template: ' . $e->getMessage());
         }
    }
}