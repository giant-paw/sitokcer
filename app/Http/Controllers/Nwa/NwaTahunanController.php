<?php

namespace App\Http\Controllers\Nwa;

use App\Http\Controllers\Controller;
use App\Models\Nwa\NwaTahunan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Support\Facades\Validator;

class NwaTahunanController extends Controller
{
    public function index(Request $request)
    {
        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = NwaTahunan::query()
            ->select(DB::raw('YEAR(created_at) as tahun'))
            ->distinct()
            ->whereNotNull('created_at')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        if (!empty($availableTahun) && !in_array(date('Y'), $availableTahun)) {
            array_unshift($availableTahun, date('Y'));
        } elseif (empty($availableTahun)) {
            $availableTahun = [date('Y')];
        }

        $query = NwaTahunan::query()
            ->whereYear('created_at', $selectedTahun);

        $selectedKegiatan = $request->input('kegiatan', '');
        if ($selectedKegiatan !== '') {
            $query->where('nama_kegiatan', $selectedKegiatan);
        }

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

        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        $listData = $query->latest('id_nwa')->paginate($perPage)->withQueryString();

        $kegiatanCounts = NwaTahunan::query()
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        return view('timNWA.tahunan.NWAtahunan', compact(
            'listData',
            'kegiatanCounts',
            'selectedKegiatan',
            'search',
            'masterKegiatanList',
            'availableTahun',
            'selectedTahun'
        ));
    }

    public function store(Request $request)
    {
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:100|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'nullable|string|max:150',
            'pencacah' => 'required|string|max:100|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:100|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'nullable|date',
            'flag_progress' => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar.',
            'pencacah.exists' => 'Nama pencacah tidak terdaftar.',
            'pengawas.exists' => 'Nama pengawas tidak terdaftar.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput()->with('error_modal', 'tambahDataModal');
        }

        $validatedData = $validator->validated();

        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) {
            }
        }

        NwaTahunan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data NWA Tahunan berhasil ditambahkan!']);
        }
        return back()->with(['success' => 'Data NWA Tahunan ditambahkan.', 'auto_hide' => true]);
    }

    /**
     * PERUBAHAN: Menggunakan $id dan memformat tanggal
     */
    public function edit($id)
    {
        $tahunan = NwaTahunan::findOrFail($id);

        $data = $tahunan->toArray();

        // INI ADALAH LOGIKA PENTING YANG HILANG
        $targetPenyelesaian = $tahunan->target_penyelesaian;
        $tanggalPengumpulan = $tahunan->tanggal_pengumpulan;

        $data['target_penyelesaian'] = $targetPenyelesaian
            ? Carbon::parse($targetPenyelesaian)->toDateString()
            : null;

        $data['tanggal_pengumpulan'] = $tanggalPengumpulan
            ? Carbon::parse($tanggalPengumpulan)->toDateString()
            : null;

        return response()->json($data);
    }

    /**
     * PERUBAHAN: Menggunakan $id
     */
    public function update(Request $request, $id)
    {
        $tahunan = NwaTahunan::findOrFail($id);

        $baseRules = [
            'nama_kegiatan' => 'required|string|max:100|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'nullable|string|max:150',
            'pencacah' => 'required|string|max:100|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:100|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'nullable|date',
            'flag_progress' => ['required', Rule::in(['Belum Mulai', 'Proses', 'Selesai'])],
            'tanggal_pengumpulan' => 'nullable|date',
        ];
        $customMessages = [ /* ... sama seperti store ... */];
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
                ->with('edit_id', $tahunan->id_nwa); // Kirim ID untuk JS fallback
        }

        $validatedData = $validator->validated();

        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
            try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
            } catch (\Exception $e) {
            }
        }

        $tahunan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Perubahan berhasil disimpan!']);
        }
        return back()->with(['success' => 'Perubahan disimpan.', 'auto_hide' => true]);
    }

    /**
     * PERUBAHAN: Menggunakan $id
     */
    public function destroy($id)
    {
        $tahunan = NwaTahunan::findOrFail($id);
        $tahunan->delete();

        return back()->with(['success' => 'Data dihapus.', 'auto_hide' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:nwa_tahunan,id_nwa'
        ]);

        NwaTahunan::whereIn('id_nwa', $request->ids)->delete();
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    public function searchPetugas(Request $request)
    {
        // Perbaikan typo: [ + menjadi [
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

    /**
     * [BARU] Menghapus beberapa data tahunan sekaligus (bulk delete).
     */
}
