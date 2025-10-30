<?php

namespace App\Http\Controllers\Distribusi;

use App\Http\Controllers\Controller;
use App\Models\Distribusi\DistribusiTriwulanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DistribusiTriwulananExport;
use Illuminate\Validation\Rule;
use App\Imports\DistribusiTriwulananImport; 


class DistribusiTriwulananController extends Controller
{
    public function index(Request $request, $jenisKegiatan)
    {
        // 1. Validasi jenis kegiatan
        $validJenis = ['spunp', 'shkk'];
        $jenisKegiatanLower = strtolower($jenisKegiatan);
        if (!in_array($jenisKegiatanLower, $validJenis)) {
            abort(404);
        }
        $prefixKegiatan = strtoupper($jenisKegiatan); // SPUNP atau SHKK

        // 2. Logika Filter Tahun (Konsisten)
        $selectedTahun = $request->input('tahun', date('Y'));
        
        // Query ini bisa tetap sama, karena hanya mengambil daftar tahun
        $availableTahun = DistribusiTriwulanan::query()
             // Kita filter berdasarkan prefix nama_kegiatan lokal,
             // ini akan mencakup data bersih dan kotor
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()->whereNotNull('created_at')->orderBy('tahun', 'desc')
            ->pluck('tahun')->toArray();
            
        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // 3. Kueri Utama [PERBAIKAN UTAMA DI SINI]
        $query = DistribusiTriwulanan::query()
            // Gunakan LEFT JOIN agar data yang 'master_kegiatan_id' nya NULL tetap muncul
            ->leftJoin('master_kegiatan', 'distribusi_triwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            
            // Filter jenis kegiatan: Cek di master JIKA ADA, atau cek di tabel lokal JIKA NULL
            ->where(function($q) use ($prefixKegiatan) {
                // 1. Cek di tabel master (untuk data bersih)
                $q->where('master_kegiatan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                  // 2. ATAU cek di tabel lokal jika ID-nya NULL (untuk data kotor)
                  ->orWhere(function($sub) use ($prefixKegiatan) {
                      $sub->whereNull('distribusi_triwulanan.master_kegiatan_id')
                          ->where('distribusi_triwulanan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%');
                  });
            })
            ->whereYear('distribusi_triwulanan.created_at', $selectedTahun); // Filter tahun

        // Filter Kegiatan Spesifik (Tab) [PERBAIKAN]
        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            // $selectedKegiatan bisa berupa ID (data bersih) atau Teks (data kotor)
            if (is_numeric($selectedKegiatan)) {
                // Jika ID (data bersih), filter berdasarkan ID
                $query->where('distribusi_triwulanan.master_kegiatan_id', $selectedKegiatan);
            } else {
                // Jika Teks (data kotor), filter berdasarkan nama & pastikan ID-nya NULL
                $query->whereNull('distribusi_triwulanan.master_kegiatan_id')
                      ->where('distribusi_triwulanan.nama_kegiatan', $selectedKegiatan);
            }
        }

        // Filter Pencarian [PERBAIKAN]
        $search = $request->input('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                // Tentukan tabel dengan jelas untuk menghindari "ambiguous column"
                $q->where('distribusi_triwulanan.BS_Responden', 'like', "%{$search}%")
                  ->orWhere('distribusi_triwulanan.pencacah', 'like', "%{$search}%")
                  ->orWhere('distribusi_triwulanan.pengawas', 'like', "%{$search}%")
                  // Cari di kedua kolom nama (master untuk data bersih, lokal untuk data kotor)
                  ->orWhere('master_kegiatan.nama_kegiatan', 'like', "%{$search}%")
                  ->orWhere('distribusi_triwulanan.nama_kegiatan', 'like', "%{$search}%");
            });
        }

        // 4. Logika Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        // 5. Ambil Data [PERBAIKAN]
        $listData = $query
            // PENTING: Pilih kolom dari tabel utama untuk menghindari data 'created_at' yg tumpang tindih
            ->select('distribusi_triwulanan.*') 
            // Eager load relasi (akan null jika ID-nya null, tapi ini bagus)
            ->with('masterKegiatan') 
            // Tentukan tabel untuk 'latest'
            ->latest('distribusi_triwulanan.id_distribusi_triwulanan') 
            ->paginate($perPage)
            ->withQueryString();

        // 6. Logika Hitung Tab (Dashboard) [PERBAIKAN BESAR]
        $kegiatanCounts = DistribusiTriwulanan::query()
            ->leftJoin('master_kegiatan', 'distribusi_triwulanan.master_kegiatan_id', '=', 'master_kegiatan.id_master_kegiatan')
            // Gunakan filter WHERE yang sama persis dengan $query utama
            ->where(function($q) use ($prefixKegiatan) {
                $q->where('master_kegiatan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                  ->orWhere(function($sub) use ($prefixKegiatan) {
                      $sub->whereNull('distribusi_triwulanan.master_kegiatan_id')
                          ->where('distribusi_triwulanan.nama_kegiatan', 'LIKE', $prefixKegiatan . '%');
                  });
            })
            ->whereYear('distribusi_triwulanan.created_at', $selectedTahun)
            ->select(
                // "Ambil ID master JIKA ADA, kalau tidak, ambil Teks ngawurnya"
                // Ini akan jadi 'value' di dropdown
                DB::raw('COALESCE(master_kegiatan.id_master_kegiatan, distribusi_triwulanan.nama_kegiatan) as filter_value'),
                
                // "Ambil NAMA master JIKA ADA, kalau tidak, ambil Teks ngawurnya"
                // Ini akan jadi 'nama' di dropdown
                DB::raw('COALESCE(master_kegiatan.nama_kegiatan, distribusi_triwulanan.nama_kegiatan) as display_name'),
                
                DB::raw('count(distribusi_triwulanan.id_distribusi_triwulanan) as total')
            )
            ->groupBy('filter_value', 'display_name')
            ->orderBy('display_name')
            ->get();

        // Ambil master kegiatan hanya untuk jenis yang relevan (ini masih oke)
        $masterKegiatanList = MasterKegiatan::where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                                            ->orderBy('nama_kegiatan')->get();

        // 7. Kirim ke View
        return view('timDistribusi.distribusiTriwulanan', compact(
            'listData',
            'kegiatanCounts', // <-- $kegiatanCounts sekarang berisi data bersih & kotor
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
        // --- [PERUBAHAN UTAMA DI SINI] ---
        $baseRules = [
            // 1. Validasi utama sekarang berdasarkan ID, bukan Teks
            'master_kegiatan_id' => 'required|integer|exists:master_kegiatan,id_master_kegiatan', 
            
            // 2. 'nama_kegiatan' tetap wajib, tapi tidak perlu 'exists' lagi
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
            'pencacah.exists'             => 'Nama pencacah tidak terdaftar di master.',
            'pengawas.exists'             => 'Nama pengawas tidak terdaftar di master.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput()->with('error_modal', 'tambahDataModal');
        }

        // $validatedData sekarang akan berisi 'master_kegiatan_id'
        $validatedData = $validator->validated();

        // Tambahkan 'tahun_kegiatan' (jika masih dipakai)
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try { 
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; 
            } catch (\Exception $e) {
                // Abaikan jika parsing gagal
            }
        }

        // Buat data baru. Ini akan berhasil jika 'master_kegiatan_id' ada di $fillable Model
        DistribusiTriwulanan::create($validatedData);

        // Kirim pesan sukses
        session()->flash('success', 'Data berhasil ditambahkan!');
        session()->flash('auto_hide', true);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back();
    }

    public function edit($id)
    {
        $distribusi_triwulanan = DistribusiTriwulanan::findOrFail($id);
        $data = $distribusi_triwulanan->toArray();

        // Format tanggal ke Y-m-d
        $targetPenyelesaian = $distribusi_triwulanan->target_penyelesaian;
        $tanggalPengumpulan = $distribusi_triwulanan->tanggal_pengumpulan;
        $data['target_penyelesaian'] = $targetPenyelesaian ? Carbon::parse($targetPenyelesaian)->toDateString() : null;
        $data['tanggal_pengumpulan'] = $tanggalPengumpulan ? Carbon::parse($tanggalPengumpulan)->toDateString() : null;

        return response()->json($data);
    }

    /**
     * Update data ($id manual).
     */
    public function update(Request $request, $id)
    {
        $distribusi_triwulanan = DistribusiTriwulanan::findOrFail($id);
        $baseRules = [ 
            'master_kegiatan_id' => 'required|integer|exists:master_kegiatan,id_master_kegiatan',
            'nama_kegiatan'      => 'required|string|max:255',
            'BS_Responden'        => 'required|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'       => ['required', Rule::in(['Belum Selesai', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];
         $customMessages = [
            'master_kegiatan_id.exists' => 'ID Kegiatan tidak terdaftar.',
            'master_kegiatan_id.required' => 'Silakan pilih kegiatan dari daftar.',
            'pencacah.exists'      => 'Nama pencacah tidak terdaftar.',
            'pengawas.exists'      => 'Nama pengawas tidak terdaftar.',
         ];
        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Data tidak valid.', 'errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput()
                ->with('error_modal', 'editDataModal')
                ->with('edit_id', $distribusi_triwulanan->id_distribusi_triwulanan);
        }

        $validatedData = $validator->validated();
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        $distribusi_triwulanan->update($validatedData);

        // ===== PERBAIKAN: SET FLASH MESSAGE SEBELUM RETURN =====
        session()->flash('success', 'Data berhasil diperbarui!');
        session()->flash('auto_hide', true);
        // =======================================================

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        return back(); // Flash sudah di-set di atas
    }

    /**
     * Hapus data ($id manual).
     */
    public function destroy($id)
    {
        $distribusi_triwulanan = DistribusiTriwulanan::findOrFail($id);
        $distribusi_triwulanan->delete();

        if (request()->ajax() || request()->wantsJson()) {
             return response()->json(['success' => 'Data berhasil dihapus!']);
        }
        return back()->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Hapus banyak data.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:distribusi_triwulanan,id_distribusi_triwulanan'
        ]);
        DistribusiTriwulanan::whereIn('id_distribusi_triwulanan', $request->ids)->delete();
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
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
     public function searchKegiatan(Request $request, $jenisKegiatan = null) // $jenisKegiatan akan diisi oleh Route
     {
         $request->validate(['query' => 'nullable|string|max:100']);
         $query = $request->input('query', '');
         $kegiatanQuery = MasterKegiatan::query();

         // Filter berdasarkan jenisKegiatan jika diberikan di URL
         if ($jenisKegiatan) {
             $jenisKegiatanLower = strtolower($jenisKegiatan);
             if (in_array($jenisKegiatanLower, ['spunp', 'shkk'])) {
                 $kegiatanQuery->where('nama_kegiatan', 'LIKE', strtoupper($jenisKegiatan) . '%');
             }
         }

         $data = $kegiatanQuery
             ->where('nama_kegiatan', 'LIKE', "%{$query}%")
             ->limit(10)
             ->select('id_master_kegiatan', 'nama_kegiatan') 
            ->get();
         return response()->json($data);
     }

    // PERBAIKAN: Method export
    public function export(Request $request, $jenisKegiatan)
    {
        // Validasi jenis kegiatan
        if (!in_array(strtolower($jenisKegiatan), ['spunp', 'shkk'])) {
            abort(404);
        }

        // Ambil semua parameter filter dari request
        $dataRange = $request->input('dataRange', 'all');
        $dataFormat = $request->input('dataFormat', 'formatted_values');
        $exportFormat = $request->input('exportFormat', 'excel');
        $kegiatan = $request->input('kegiatan');
        $search = $request->input('search');
        $currentPage = $request->input('page', 1);
        $perPageInput = $request->input('per_page', 20);
        $selectedTahun = $request->input('tahun', date('Y')); // Tambahkan tahun

        // Tentukan perPage untuk query export
        $perPage = ($perPageInput == 'all' || $dataRange == 'all') ? -1 : (int)$perPageInput; // -1 untuk all
        
        // Buat instance export class
        // Pastikan constructor DistribusiTriwulananExport menerima parameter ini
        $exportClass = new DistribusiTriwulananExport(
            $dataRange,
            $dataFormat,
            $jenisKegiatan,  // jenis SPUNP/SHKK
            $kegiatan,       // kegiatan spesifik (jika ada)
            $search,
            $currentPage,
            $perPage,
            $selectedTahun   // tahun
        );

        $fileName = 'DistribusiTriwulanan_' . strtoupper($jenisKegiatan) . '_' . $selectedTahun . '_' . now()->format('YmdHis');

        if ($exportFormat == 'excel') {
            return Excel::download($exportClass, $fileName . '.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, $fileName . '.csv', \Maatwebsite\Excel\Excel::CSV, [
                 'Content-Type' => 'text/csv',
            ]);
        } 
        // Hapus 'word' jika DistribusiTriwulananExport tidak punya method exportToWord()
        // elseif ($exportFormat == 'word') {
        //     return $exportClass->exportToWord(); 
        // }

        return back()->with('error', 'Format ekspor tidak didukung.');
    }
    
    // PERBAIKAN: Method import
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048', // Max 2MB
        ], [
            'file.required' => 'File Excel wajib diunggah.',
            'file.mimes' => 'File harus berformat Excel (.xlsx atau .xls).',
            'file.max' => 'Ukuran file maksimal 2MB.',
        ]);
        try {
            $file = $request->file('file');
            $import = new DistribusiTriwulananImport(); // Pastikan file import ada
            Excel::import($import, $file);
            
            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();

            if (!empty($errors)) {
                return back()
                    ->with('import_errors', $errors)
                    ->with('success_count', $successCount)
                    ->with('warning', "Import selesai dengan {$successCount} data berhasil dan " . count($errors) . " data gagal. Lihat detail error di bawah.");
            }
            
            return back()->with([
                'success' => "Berhasil mengimpor {$successCount} data!",
                'auto_hide' => true
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    // PERBAIKAN: Method downloadTemplate
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header (lowercase_underscore)
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
                ->getStartColor()->setARGB('FFD9EAD3'); // Hijau muda

            // Sample data (Baris 2)
            $exampleData = [
                [
                    'SPUNP-TW1 2025',
                    'BS001',
                    'Ani Rahmawati',
                    'Budi Hariyadi',
                    '2025-03-31',
                    'Belum', // Biarkan 'Belum', file import akan menanganinya
                    '2025-03-20'
                ],
                [
                    'SHKK-TW1 2025',
                    'BS002',
                    'Siti Nurhaliza',
                    'Andi Wijaya',
                    '2025-03-31',
                    'Selesai',
                    '2025-03-25'
                ]
            ];
            $sheet->fromArray($exampleData, null, 'A2');
             $sheet->getStyle('A2:G3')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFF4CC'); // Kuning muda

            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Petunjuk (Mulai dari baris 5)
            $sheet->setCellValue('A5', 'PETUNJUK PENGISIAN:');
            $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
             $sheet->getStyle('A5')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFB4C7E7'); // Biru muda
            
            $instructions = [
                '1. Semua kolom WAJIB diisi kecuali Tanggal Pengumpulan (boleh kosong)',
                '2. Header baris 1 HARUS tetap ada dengan format lowercase dan underscore',
                '3. Nama Kegiatan, Pencacah, Pengawas harus berisi huruf (tidak boleh hanya angka)',
                '4. Format tanggal: YYYY-MM-DD atau DD/MM/YYYY (contoh: 2025-12-31 atau 31/12/2025)',
                '5. Flag Progress hanya boleh diisi: "Belum Selesai" atau "Selesai" (case-sensitive)',
                '6. BS Responden boleh berisi angka atau kombinasi huruf-angka',
                '7. HAPUS baris contoh (baris 2-3) dan petunjuk ini sebelum import!',
                '8. Simpan file dalam format .xlsx atau .xls'
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
            $fileName = 'Template_Import_Distribusi_Triwulanan.xlsx';
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($temp_file);

            return response()->download($temp_file, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat template: ' . $e->getMessage());
        }
    }
}