<?php

namespace App\Http\Controllers\Sosial;

use App\Http\Controllers\Controller;
use App\Models\Sosial\SosialTriwulanan; // Pastikan model ini ada
use Illuminate\Http\Request;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class SosialTriwulanController extends Controller
{
    public function index(Request $request, $jenisKegiatan = 'seruti') // Default ke seruti jika tidak ada
    {
        // 1. Validasi jenis kegiatan (opsional, sesuaikan jika ada jenis lain)
        $validJenis = ['seruti']; // Hanya 'seruti' untuk saat ini
        if (!in_array(strtolower($jenisKegiatan), $validJenis)) {
            abort(404);
        }
        $prefixKegiatan = 'Seruti'; // Awalan nama kegiatan untuk query LIKE

        // 2. Logika Filter Tahun (konsisten dengan controller lain)
        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Filter berdasarkan jenis kegiatan
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
        $query = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Filter utama berdasarkan jenis
            ->whereYear('created_at', $selectedTahun);

        // Filter Kegiatan Spesifik (dari Tab, misal: Seruti-TW1, Seruti-TW2)
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
                  ->orWhere('nama_kegiatan', 'like', "%{$search}%") // Bisa cari nama kegiatan spesifik
                  ->orWhere('flag_progress', 'like', "%{$search}%");
            });
        }

        // 4. Logika Pagination
        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        // 5. Ambil Data (Gunakan primary key 'id_sosial_triwulanan')
        $listData = $query->latest('id_sosial_triwulanan')->paginate($perPage)->withQueryString();

        // 6. Logika Hitung Tab (misal: group by Seruti-TW1, Seruti-TW2, dst.)
        $kegiatanCounts = SosialTriwulanan::query()
            ->where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Filter jenis
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan') // Urutkan TW1, TW2, ...
            ->get();

        $masterKegiatanList = MasterKegiatan::where('nama_kegiatan', 'LIKE', $prefixKegiatan . '%') // Hanya tampilkan master Seruti
                                            ->orderBy('nama_kegiatan')->get();

        // 7. Kirim ke View BARU
        return view('timSosial.triwulanan.sosialTriwulanan', compact( // Path view baru
            'listData',
            'kegiatanCounts',
            'jenisKegiatan',      // Kirim jenis ('seruti')
            'masterKegiatanList', // Untuk autocomplete tambah/edit
            'availableTahun',
            'selectedTahun',
            'selectedKegiatan',   // Untuk menandai tab aktif
            'search'              // Untuk mengisi kolom search
        ));
    }

    /**
     * Simpan data baru (AJAX ready).
     */
    public function store(Request $request)
    {
        $baseRules = [
            'nama_kegiatan'       => ['required', 'string', 'max:255', 'exists:master_kegiatan,nama_kegiatan', 'regex:/^Seruti-(TW1|TW2|TW3|TW4)/'], // Tetap pakai regex + exists
            'BS_Responden'        => 'nullable|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'nullable|date', // Diubah jadi nullable seperti NWA & Produksi
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan Seruti tidak terdaftar di master.',
            'nama_kegiatan.regex'  => 'Format Nama Kegiatan harus Seruti-TWx (x=1-4).',
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

        // Tambahkan tahun_kegiatan jika ada kolomnya
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        SosialTriwulanan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data Seruti berhasil ditambahkan!']);
        }
        return back()->with(['success' => 'Data Seruti berhasil ditambahkan!', 'auto_hide' => true]);
    }

    /**
     * Ambil data untuk modal edit (AJAX ready).
     * Menggunakan $id manual.
     */
    public function edit($id)
    {
        // Pastikan primary key 'id_sosial_triwulanan' benar
        $sosial_triwulanan = SosialTriwulanan::findOrFail($id);

        $data = $sosial_triwulanan->toArray();

        // Format tanggal untuk input type="date"
        $targetPenyelesaian = $sosial_triwulanan->target_penyelesaian;
        $tanggalPengumpulan = $sosial_triwulanan->tanggal_pengumpulan;

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
        // Pastikan primary key 'id_sosial_triwulanan' benar
        $sosial_triwulanan = SosialTriwulanan::findOrFail($id);

        $baseRules = [ // Sama seperti store
            'nama_kegiatan'       => ['required', 'string', 'max:255', 'exists:master_kegiatan,nama_kegiatan', 'regex:/^Seruti-(TW1|TW2|TW3|TW4)/'],
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
                // Pastikan primary key 'id_sosial_triwulanan' benar
                ->with('edit_id', $sosial_triwulanan->id_sosial_triwulanan);
        }

        $validatedData = $validator->validated();

        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        $sosial_triwulanan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data Seruti berhasil diperbarui!']);
        }
        // Redirect ke index dengan filter yang relevan (misal: TW dari nama kegiatan)
        $tw = $this->extractTW($validatedData['nama_kegiatan']);
        // Ganti nama route ke index yang baru
        return redirect()->route('sosial.triwulanan.index', ['jenisKegiatan' => 'seruti', /* 'tw' => $tw, */ 'tahun' => $selectedTahun ?? date('Y') ]) 
                         ->with(['success' => 'Data Seruti berhasil diperbarui!', 'auto_hide' => true]);
    }

    /**
     * Hapus satu data (AJAX ready).
     * Menggunakan $id manual.
     */
    public function destroy($id)
    {
        // Pastikan primary key 'id_sosial_triwulanan' benar
        $sosial_triwulanan = SosialTriwulanan::findOrFail($id);
        $namaKegiatan = $sosial_triwulanan->nama_kegiatan; // Simpan nama sebelum dihapus
        $sosial_triwulanan->delete();

        if (request()->ajax() || request()->wantsJson()) {
             return response()->json(['success' => 'Data Seruti berhasil dihapus!']);
        }

        // Redirect ke index dengan filter yang relevan
        $tw = $this->extractTW($namaKegiatan);
        // Ganti nama route ke index yang baru
        return redirect()->route('sosial.triwulanan.index', ['jenisKegiatan' => 'seruti', /* 'tw' => $tw */ 'tahun' => session('selected_tahun', date('Y')) ])
                         ->with(['success' => 'Data Seruti berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Hapus banyak data.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            // Pastikan primary key 'id_sosial_triwulanan' benar
            'ids.*' => 'exists:sosial_triwulanan,id_sosial_triwulanan'
        ]);

        // Pastikan primary key 'id_sosial_triwulanan' benar
        SosialTriwulanan::whereIn('id_sosial_triwulanan', $request->ids)->delete();

        return back()->with(['success' => 'Data Seruti yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Cari petugas (autocomplete). (Copy dari controller lain)
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
      * Cari kegiatan Seruti (autocomplete). (Copy dari controller lain)
      */
    public function searchKegiatan(Request $request)
    {
         $request->validate(['query' => 'nullable|string|max:100']);
         $query = $request->input('query', '');

         $data = MasterKegiatan::query()
             ->where('nama_kegiatan', 'LIKE', "Seruti%") // Filter hanya Seruti
             ->where('nama_kegiatan', 'LIKE', "%{$query}%")
             ->limit(10)
             ->pluck('nama_kegiatan');

         return response()->json($data);
    }

    private function extractTW(string $nama): string
    {
        return preg_match('/Seruti\-(TW[1-4])/', $nama, $m) ? $m[1] : 'TW1';
    }
}
