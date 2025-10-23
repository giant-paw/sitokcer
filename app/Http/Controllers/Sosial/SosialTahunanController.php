<?php

namespace App\Http\Controllers\Sosial;

use App\Http\Controllers\Controller;
use App\Models\Sosial\SosialTahunan; 
use Illuminate\Http\Request;
use App\Models\Master\MasterPetugas; 
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class SosialTahunanController extends Controller
{
    public function index(Request $request)
    {
        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = SosialTahunan::query()
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->whereNotNull('created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (empty($availableTahun) || !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        }

        $query = SosialTahunan::query()
            ->whereYear('created_at', $selectedTahun);

        // Filter Kegiatan Spesifik (dari Tab)
        $selectedKegiatan = $request->input('kegiatan', ''); // Simpan untuk dikirim ke view
        if ($selectedKegiatan !== '') {
            $query->where('nama_kegiatan', $selectedKegiatan);
        }

        // Filter Pencarian
        $search = $request->input('search', ''); // Simpan untuk dikirim ke view
        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('BS_Responden', 'like', "%{$search}%")
                  ->orWhere('pencacah', 'like', "%{$search}%")
                  ->orWhere('pengawas', 'like', "%{$search}%")
                  ->orWhere('nama_kegiatan', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        // Pastikan primary key 'id_sosial' benar
        $listData = $query->latest('id_sosial')->paginate($perPage)->withQueryString();

        $kegiatanCounts = SosialTahunan::query()
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        return view('timSosial.tahunan.sosialtahunan', compact(
            'listData',
            'kegiatanCounts',
            'masterKegiatanList',
            'availableTahun',
            'selectedTahun',
            'selectedKegiatan', // Kirim ini ke view
            'search'           // Kirim ini ke view
        ));
    }

    /**
     * Simpan data baru.
     */
    public function store(Request $request)
    {
        $baseRules = [
            'nama_kegiatan'       => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden'        => 'nullable|string|max:255', // Sesuai controller asli
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress'       => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])], // Sesuai controller asli
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar.',
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

        SosialTahunan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }
        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    /**
     * PERBAIKAN KUNCI:
     * 1. Ambil $id manual.
     * 2. Format tanggal untuk JavaScript (Y-m-d).
     */
    public function edit($id)
    {
        // Pastikan primary key 'id_sosial' benar
        $sosial_tahunan = SosialTahunan::findOrFail($id);

        $data = $sosial_tahunan->toArray();

        // --- INI ADALAH LOGIKA YANG MEMPERBAIKI MASALAH ---
        $targetPenyelesaian = $sosial_tahunan->target_penyelesaian;
        $tanggalPengumpulan = $sosial_tahunan->tanggal_pengumpulan;

        $data['target_penyelesaian'] = $targetPenyelesaian
            ? Carbon::parse($targetPenyelesaian)->toDateString()
            : null;

        $data['tanggal_pengumpulan'] = $tanggalPengumpulan
            ? Carbon::parse($tanggalPengumpulan)->toDateString()
            : null;
        // --- AKHIR LOGIKA PERBAIKAN ---

        return response()->json($data);
    }

    /**
     * PERBAIKAN KUNCI:
     * 1. Ambil $id manual.
     */
    public function update(Request $request, $id)
    {
        // Pastikan primary key 'id_sosial' benar
        $sosial_tahunan = SosialTahunan::findOrFail($id);

        $baseRules = [
            'nama_kegiatan'       => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden'        => 'nullable|string|max:255',
            'pencacah'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas'            => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
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
                // Pastikan primary key 'id_sosial' benar
                ->with('edit_id', $sosial_tahunan->id_sosial);
        }

        $validatedData = $validator->validated();

        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try { $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year; } catch (\Exception $e) {}
        }

        $sosial_tahunan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        // Redirect ke index setelah update sukses (sesuaikan nama route)
        return redirect()->route('sosial.tahunan.index')->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }

    /**
     * PERBAIKAN KUNCI:
     * 1. Ambil $id manual.
     */
    public function destroy($id)
    {
        // Pastikan primary key 'id_sosial' benar
        $sosial_tahunan = SosialTahunan::findOrFail($id);
        $sosial_tahunan->delete();

        // Redirect ke index setelah delete sukses (sesuaikan nama route)
        return redirect()->route('sosial.tahunan.index')->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Hapus banyak data.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            // Pastikan primary key 'id_sosial' benar
            'ids.*' => 'exists:sosial_tahunan,id_sosial'
        ]);

        // Pastikan primary key 'id_sosial' benar
        SosialTahunan::whereIn('id_sosial', $request->ids)->delete();

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    /**
     * Cari petugas (autocomplete).
     */
    public function searchPetugas(Request $request)
    {
        // Hapus validasi 'field' karena tidak dipakai di Blade
         $request->validate([
            'query' => 'nullable|string|max:100',
        ]);
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
             // ->where('jenis', 'Sosial') // Opsional: filter hanya kegiatan sosial
             ->where('nama_kegiatan', 'LIKE', "%{$query}%")
             ->limit(10)
             ->pluck('nama_kegiatan');

         return response()->json($data);
    }
}
