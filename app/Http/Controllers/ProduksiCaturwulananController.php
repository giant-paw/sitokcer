<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProduksiCaturwulanan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProduksiCaturwulananController extends Controller
{
    public function index(Request $request, $jenisKegiatan)
    {
        
        $lowercaseJenis = strtolower($jenisKegiatan);

        if (!in_array($lowercaseJenis, ['ubinan padi palawija', 'ubinan utp palawija'])) {
            abort(404);
        }

        $searchString = str_replace(' ', '', $lowercaseJenis); 
        
        $query = ProduksiCaturwulanan::query()
            ->whereRaw('LOWER(REPLACE(nama_kegiatan, " ", "")) LIKE ?', [$searchString . '%']);


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

        $kegiatanCounts = ProduksiCaturwulanan::query()
            ->whereRaw('LOWER(REPLACE(nama_kegiatan, " ", "")) LIKE ?', [$searchString . '%']) 
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        return view('timProduksi.produksiCaturwulanan', compact('listData', 'kegiatanCounts', 'jenisKegiatan'));
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
            'tanggal_pengumpulan' => 'required|date',
        ]);

        $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;

        ProduksiCaturwulanan::create($validatedData);

        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    public function edit(ProduksiCaturwulanan $produksi_caturwulanan)
    {
        return response()->json($produksi_caturwulanan);
    }

    public function update(Request $request, ProduksiCaturwulanan $produksi_caturwulanan)
    {
        $validatedData = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'BS_Responden' => 'required|string|max:255', 
            'pencacah' => 'required|string|max:255',
            'pengawas' => 'required|string|max:255',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => 'required|string',
            'tanggal_pengumpulan' => 'required|date',
        ]);

        $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;

        $produksi_caturwulanan->update($validatedData);
        
        return back()->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }


    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:produksi_caturwulanan,id_produksi_caturwulanan' 
        ]);

        ProduksiCaturwulanan::whereIn('id_produksi_caturwulanan', $request->ids)->delete();

        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true]);
    }


    public function destroy(ProduksiCaturwulanan $produksi_caturwulanan)
    {
        $produksi_caturwulanan->delete();
        
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

        $data = ProduksiCaturwulanan::query()
            ->select($field)
            ->where($field, 'LIKE', "%{$query}%")
            ->distinct() 
            ->limit(5)  
            ->pluck($field);

        return response()->json($data);
    }
}
