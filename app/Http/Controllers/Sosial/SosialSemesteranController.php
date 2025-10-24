<?php

namespace App\Http\Controllers\Sosial;

use App\Http\Controllers\Controller;
use App\Models\Sosial\SosialSemesteran; 
use Illuminate\Http\Request;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SosialSemesteranExport;

class SosialSemesteranController extends Controller
{
    public function index(Request $request, $jenisKegiatan) // Tambah parameter jenisKegiatan
    {
        // 1. Validasi jenis kegiatan
        $validJenis = ['sakernas', 'susenas'];
        $jenisKegiatanLower = strtolower($jenisKegiatan);
        if (!in_array($jenisKegiatanLower, $validJenis)) {
            abort(404);
        }
        // Tentukan prefix berdasarkan jenisKegiatan untuk query LIKE
        $prefixKegiatan = ($jenisKegiatanLower == 'sakernas') ? 'Sakernas' : 'Susenas';

        // 2. Logika Filter Tahun
        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = SosialSemesteran::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Filter by jenis (TETAP PAKAI LIKE)
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->whereNotNull('created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        // 3. Kueri Utama
        $query = SosialSemesteran::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Filter by jenis (TETAP PAKAI LIKE)
            ->whereYear('created_at', $selectedTahun);

        // Filter Kegiatan Spesifik (dari Tab, misal: Sakernas Semester 1)
        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            $query->where('nama_kegiatan', $selectedKegiatan);
        }

        // Filter Pencarian
        $search = $request->input('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('BS_Responden', 'like', "%{$search}%")
                  ->orWhere('pencacah', 'like', "%{$search}%")
                  ->orWhere('pengawas', 'like', "%{$search}%")
                  ->orWhere('nama_kegiatan', 'like', "%{$search}%")
                  ->orWhere('flag_progress', 'like', "%{$search}%");
            });
        }

        // 4. Logika Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        // 5. Ambil Data (Gunakan primary key 'id_sosial_semesteran')
        $listData = $query->latest('id_sosial_semesteran')->paginate($perPage)->withQueryString();

        // 6. Logika Hitung Tab (group by nama kegiatan spesifik)
        $kegiatanCounts = SosialSemesteran::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Filter jenis (TETAP PAKAI LIKE)
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        // Ambil master kegiatan hanya untuk jenis yang relevan
        $masterKegiatanList = MasterKegiatan::where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%')
                                            ->orderBy('nama_kegiatan')->get();

        // 7. Kirim ke View BARU
        return view('timSosial.semesteran.sosialSemesteran', compact( // Path view baru
            'listData',
            'kegiatanCounts',
            'jenisKegiatan',      // Kirim 'sakernas' atau 'susenas'
            'masterKegiatanList',
            'availableTahun',
            'selectedTahun',
            'selectedKegiatan',
            'search'
        ));
    }

    // --- store, edit, update, destroy, bulkDelete, searchPetugas, searchKegiatan ---
    // (Kode dari jawaban sebelumnya sudah benar dan mengikuti pola $id manual)
    // ... (salin dari jawaban sebelumnya jika perlu) ...
        /**
     * Simpan data baru (AJAX ready).
     */
    public function store(Request $request)
    {
        // Validasi disamakan & diperbaiki (regex + exists)
        $baseRules = [
            'nama_kegiatan'       => ['required', 'string', 'max:255', 'exists:master_kegiatan,nama_kegiatan', 'regex:/^(Sakernas|Susenas)/i'], // Izinkan Sakernas atau Susenas
            'BS_Responden'        => 'nullable|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'nullable|date', // Dibuat nullable agar konsisten
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar di master.',
            'nama_kegiatan.regex'  => 'Nama Kegiatan harus diawali Sakernas atau Susenas.',
            'pencacah.exists'      => 'Nama pencacah tidak terdaftar.',
            'pengawas.exists'      => 'Nama pengawas tidak terdaftar.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data yang diberikan tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput()->with('error_modal', 'tambahDataModal');
        }

        $validatedData = $validator->validated();

        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        SosialSemesteran::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data Semesteran berhasil ditambahkan!']);
        }
        return back()->with(['success' => 'Data Semesteran berhasil ditambahkan!', 'auto_hide' => true]);
    }

    /**
     * Ambil data untuk modal edit (AJAX ready).
     * Menggunakan $id manual dan format tanggal.
     */
    public function edit($id)
    {
        // Pastikan primary key 'id_sosial_semesteran' benar
        $sosial_semesteran = SosialSemesteran::findOrFail($id);

        $data = $sosial_semesteran->toArray();

        // Format tanggal untuk input type="date"
        $targetPenyelesaian = $sosial_semesteran->target_penyelesaian;
        $tanggalPengumpulan = $sosial_semesteran->tanggal_pengumpulan;

        $data['target_penyelesaian'] = $targetPenyelesaian
            ? Carbon::parse($targetPenyelesaian)->toDateString()
            : null;

        $data['tanggal_pengumpulan'] = $tanggalPengumpulan
            ? Carbon::parse($tanggalPengumpulan)->toDateString()
            : null;

        return response()->json($data);
    }

    /**
     * Update data yang ada (AJAX ready).
     * Menggunakan $id manual.
     */
    public function update(Request $request, $id)
    {
        // Pastikan primary key 'id_sosial_semesteran' benar
        $sosial_semesteran = SosialSemesteran::findOrFail($id);

        $baseRules = [ // Sama seperti store
            'nama_kegiatan'       => ['required', 'string', 'max:255', 'exists:master_kegiatan,nama_kegiatan', 'regex:/^(Sakernas|Susenas)/i'],
            'BS_Responden'        => 'nullable|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'nullable|date',
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [ /* ... sama seperti store ... */ ];
        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput()
                ->with('error_modal', 'editDataModal')
                // Pastikan primary key 'id_sosial_semesteran' benar
                ->with('edit_id', $sosial_semesteran->id_sosial_semesteran);
        }

        $validatedData = $validator->validated();

        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        $sosial_semesteran->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data Semesteran berhasil diperbarui!']);
        }
        // Redirect ke index dengan filter yang relevan
        $jenisKegiatan = str_starts_with(strtolower($validatedData['nama_kegiatan']), 'sakernas') ? 'sakernas' : 'susenas';
        $selectedTahun = $request->input('tahun', date('Y')); // Ambil tahun dari request atau default
        return redirect()->route('sosial.semesteran.index', ['jenisKegiatan' => $jenisKegiatan, 'tahun' => $selectedTahun])
                         ->with(['success' => 'Data Semesteran berhasil diperbarui!', 'auto_hide' => true]);
    }

    /**
     * Hapus satu data (AJAX ready).
     * Menggunakan $id manual.
     */
    public function destroy($id)
    {
        // Pastikan primary key 'id_sosial_semesteran' benar
        $sosial_semesteran = SosialSemesteran::findOrFail($id);
        $namaKegiatan = $sosial_semesteran->nama_kegiatan; // Simpan untuk redirect
        $tahunDariData = $sosial_semesteran->created_at ? $sosial_semesteran->created_at->year : date('Y'); // Ambil tahun dari data

        $sosial_semesteran->delete();

        if (request()->ajax() || request()->wantsJson()) {
             return response()->json(['success' => 'Data Semesteran berhasil dihapus!']);
        }

        // Redirect ke index dengan filter yang relevan
        $jenisKegiatan = str_starts_with(strtolower($namaKegiatan), 'sakernas') ? 'sakernas' : 'susenas';
        return redirect()->route('sosial.semesteran.index', ['jenisKegiatan' => $jenisKegiatan, 'tahun' => $tahunDariData])
                         ->with(['success' => 'Data Semesteran berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Hapus banyak data.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            // Pastikan primary key 'id_sosial_semesteran' benar
            'ids.*' => 'exists:sosial_semesteran,id_sosial_semesteran'
        ]);

        // Pastikan primary key 'id_sosial_semesteran' benar
        SosialSemesteran::whereIn('id_sosial_semesteran', $request->ids)->delete();

        return back()->with(['success' => 'Data Semesteran yang dipilih berhasil dihapus!', 'auto_hide' => true]);
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
      * Cari kegiatan Semesteran (Sakernas/Susenas - autocomplete).
      */
    public function searchKegiatan(Request $request)
    {
         $request->validate([
            'query' => 'nullable|string|max:100',
            'jenis' => 'nullable|string|in:sakernas,susenas' // Tambah parameter jenis
         ]);
         $query = $request->input('query', '');
         $jenis = $request->input('jenis'); // Ambil jenis dari request

         $kegiatanQuery = MasterKegiatan::query();

         // Filter berdasarkan jenis jika ada
         if ($jenis === 'sakernas') {
            $kegiatanQuery->where('nama_kegiatan', 'LIKE', 'Sakernas%');
         } elseif ($jenis === 'susenas') {
            $kegiatanQuery->where('nama_kegiatan', 'LIKE', 'Susenas%');
         } else {
             // Jika jenis tidak spesifik (misal dari halaman lain), cari keduanya
            $kegiatanQuery->where(function ($q) {
                $q->where('nama_kegiatan', 'LIKE', 'Sakernas%')
                  ->orWhere('nama_kegiatan', 'LIKE', 'Susenas%');
            });
         }

         $data = $kegiatanQuery
             ->where('nama_kegiatan', 'LIKE', "%{$query}%")
             ->limit(10)
             ->pluck('nama_kegiatan');

         return response()->json($data);
    }
    public function export(Request $request, $jenisKegiatan)
    {
        // Validasi jenis kegiatan
        $validJenis = ['sakernas', 'susenas'];
        $jenisKegiatanLower = strtolower($jenisKegiatan);
        if (!in_array($jenisKegiatanLower, $validJenis)) {
            abort(404);
        }
        // Validasi input
        $request->validate([
            'dataRange' => 'required|in:all,current_page',
            'dataFormat' => 'required|in:formatted_values,raw_values',
            'exportFormat' => 'required|in:excel,csv,word',
        ]);
        $dataRange = $request->input('dataRange', 'all');
        $dataFormat = $request->input('dataFormat');
        $exportFormat = $request->input('exportFormat');
        $kegiatan = $request->input('kegiatan');
        $search = $request->input('search');
        $tahun = $request->input('tahun', date('Y'));
        $currentPage = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        // Buat instance export class
        $exportClass = new SosialSemesteranExport(
            $dataRange,
            $dataFormat,
            $jenisKegiatan,
            $kegiatan,
            $search,
            $tahun,
            $currentPage,
            $perPage
        );
        // Generate nama file
        $jenisKegiatanTitle = ucfirst($jenisKegiatan);
        $fileName = 'Sosial_Semesteran_' . $jenisKegiatanTitle;
        if (!empty($kegiatan)) {
            $fileName .= '_' . str_replace(' ', '_', $kegiatan);
        }
        $fileName .= '_' . date('Ymd_His');
        // Export berdasarkan format
        if ($exportFormat == 'excel') {
            return Excel::download($exportClass, $fileName . '.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, $fileName . '.csv');
        } elseif ($exportFormat == 'word') {
            return $exportClass->exportToWord();
        }
        return back()->with('error', 'Format ekspor tidak didukung.');
    }
}
