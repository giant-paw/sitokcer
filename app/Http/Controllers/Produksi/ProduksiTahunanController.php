<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\Produksi\ProduksiTahunan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\MasterPetugas\MasterPetugas;
use App\Models\Master\MasterKegiatan; 
use Illuminate\Support\Facades\Validator;

class ProduksiTahunanController extends Controller
{
    public function index(Request $request)
    {
        $selectedTahun = $request->input('tahun', date('Y'));
        $availableTahun = ProduksiTahunan::query()
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

        $query = ProduksiTahunan::query()
                ->whereYear('created_at', $selectedTahun);

        if ($request->filled('kegiatan')) {
            $query->where('nama_kegiatan', $request->kegiatan);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('BS_Responden', 'like', "%{$searchTerm}%")
                  ->orWhere('pencacah', 'like', "%{$searchTerm}%")
                  ->orWhere('pengawas', 'like', "%{$searchTerm}%")
                  ->orWhere('nama_kegiatan', 'like', "%{$searchTerm}%");
            });
        }

        $perPage = $request->input('per_page', 20); 

        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        $listData = $query->latest('id_produksi')->paginate($perPage)->withQueryString();

        $kegiatanCounts = ProduksiTahunan::query()
            ->whereYear('created_at', $selectedTahun) 
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        return view('timProduksi.produksiTahunan', compact(
            'listData', 
            'kegiatanCounts',
            'masterKegiatanList', 
            'availableTahun',     
            'selectedTahun'       
        ));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'BS_Responden' => 'required|string|max:255', 
            'pencacah' => 'required|string|max:255',
            'pengawas' => 'required|string|max:255',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => 'required|string',
            'tanggal_pengumpulan' => 'nullable|date',
        ]);

        $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;

        ProduksiTahunan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }

        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    public function edit($id)
    {
        $produksi = ProduksiTahunan::findOrFail($id);
        
        return response()->json($produksi);
    }

    public function update(Request $request, ProduksiTahunan $produksi) // <-- Ubah $id menjadi ProduksiTahunan $produksi
    {
         $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255', 
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date', // <-- Sesuaikan tipe data jika perlu
            'flag_progress' => 'required|string',
            'tanggal_pengumpulan' => 'nullable|date', // <-- Sesuaikan tipe data jika perlu
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar di master kegiatan.',
            'pencacah.exists' => 'Nama pencacah tidak terdaftar di master petugas.',
            'pengawas.exists' => 'Nama pengawas tidak terdaftar di master petugas.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data yang diberikan tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }

            return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error_modal', 'editDataModal')
                    ->with('edit_id', $produksi->id_produksi);
        }

        $validatedData = $validator->validated();
        
        if ($request->has('target_penyelesaian') && !empty($request->target_penyelesaian)) {
             try {
                $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
             } catch (\Exception $e) {
             }
        } else {
        }

        $produksi->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        
        return redirect()->route('tim-produksi.tahunan.index')->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:produksi_tahunan,id_produksi' 
        ]);

        ProduksiTahunan::whereIn('id_produksi', $request->ids)->delete();
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true, 'hide_after' => 2]);
    }

    public function destroy(ProduksiTahunan $produksi)
    {
        $produksi->delete();

        return redirect()->route('tim-produksi.tahunan.index')->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    public function searchPetugas(Request $request)
    {
        $request->validate([
            'field' => 'required|in:pencacah,pengawas',
            'query' => 'nullable|string|max:100',
        ]);

        $field = $request->input('field');
        $query = $request->input('query', '');

        $data = MasterPetugas::query()
            ->where('nama_petugas', 'LIKE', "%{$query}%")
            ->limit(10) 
            ->pluck('nama_petugas');

        return response()->json($data);
    }
}