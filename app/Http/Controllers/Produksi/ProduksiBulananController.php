<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Models\ProduksiBulanan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use Illuminate\Http\Request;

class ProduksiBulananController extends Controller
{
    // Tampil data sesuai jenis kegiatan
    public function index(Request $request, $jenisKegiatan)
    {
        if (!in_array(strtolower($jenisKegiatan), ['ksapadi', 'ksajagung', 'lptb', 'sphsbs', 'sppalawija', 'perkebunan', 'ibs'])) {
            abort(404);
        }

        $query = ProduksiBulanan::query()->where('nama_kegiatan', 'Like', strtoupper($jenisKegiatan). '%');

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

        $listData = $query->latest()->paginate($perPage)->withQueryString();

        $kegiatanCounts = ProduksiBulanan::query()
            ->where('nama_kegiatan', 'LIKE', strtoupper($jenisKegiatan). '%')
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        return view('timProduksi.produksiBulanan', compact('listData', 'kegiatanCounts', 'jenisKegiatan'));
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

        $data = ProduksiBulanan::query()
            ->select($field)
            ->where($field, 'LIKE', "%{$query}%")
            ->distinct() 
            ->limit(5)  
            ->pluck($field);

        return response()->json($data);
    }
}