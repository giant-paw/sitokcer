<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\Produksi\ProduksiBulanan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\MasterPetugas\MasterPetugas;

use Illuminate\Http\Request;

class ProduksiBulananController extends Controller
{
    // Tampil data sesuai jenis kegiatan
    public function index(Request $request, $jenisKegiatan)
    {
        $validJenis = ['sktr', 'tpi', 'sphbst', 'sphtbf', 'sphth', 'airbersih']; 

        $lowercaseJenis = strtolower($jenisKegiatan); 

        if (!in_array($lowercaseJenis, $validJenis)) { 
            abort(404); 
        }

        $selectedTahun = $request->input('tahun', date('Y'));

        $availableTahun = ProduksiTriwulanan::query() 
            ->where('nama_kegiatan', 'LIKE', $jenisKegiatan . '%') 
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

        $query = ProduksiTriwulanan::query() 
            ->where('nama_kegiatan', 'LIKE', $jenisKegiatan . '%')
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

        $listData = $query->latest('id_produksi_triwulanan')->paginate($perPage)->withQueryString(); 

        $kegiatanCounts = ProduksiTriwulanan::query() 
            ->where('nama_kegiatan', 'LIKE', $jenisKegiatan . '%') 
            ->whereYear('created_at', $selectedTahun)
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        
        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        return view('timProduksi.produksiTriwulanan', compact(
            'listData', 
            'kegiatanCounts', 
            'jenisKegiatan',
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

        ProduksiBulanan::create($validatedData);

        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    public function edit(ProduksiBulanan $produksi_bulanan)
    {
        return response()->json($produksi_bulanan);
    }

    public function update(Request $request, ProduksiBulanan $produksi_bulanan)
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

        if($request->has('target_penyelesaian')) {
            $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
        }
        
        $produksi_bulanan->update($validatedData);

        return back()->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }


    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:produksi_bulanan,id_produksi_bulanan' 
        ]);

        ProduksiBulanan::whereIn('id_produksi_bulanan', $request->ids)->delete();

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }

    public function destroy(ProduksiBulanan $produksi_bulanan)
    {
        $produksi_bulanan->delete();

        return back()->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
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