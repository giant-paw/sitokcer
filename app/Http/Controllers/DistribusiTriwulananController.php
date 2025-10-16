<?php

namespace App\Http\Controllers;

use App\Models\DistribusiTriwulanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DistribusiTriwulananController extends Controller
{
    // Tampil data SPUNP atau SHKK
    public function index(Request $request)
    {
        $query = DistribusiTahunan::query();

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

        $listData = $query->latest()->paginate(20)->withQueryString();

        $kegiatanCounts = DistribusiTahunan::query()
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        return view('timDistribusi.distribusitahunan', compact('listData', 'kegiatanCounts'));
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

        DistribusiTahunan::create($validatedData);

        return back()->with(['success' => 'Data berhasil ditambahkan!', 'auto_hide' => true]);
    }

    public function edit($id)
    {
        $distribusi = DistribusiTahunan::findOrFail($id);
        
        return response()->json($distribusi);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'BS_Responden' => 'required|string|max:255', 
            'pencacah' => 'required|string|max:255',
            'pengawas' => 'required|string|max:255',
            'target_penyelesaian' => 'required|string|max:255',
            'flag_progress' => 'required|string',
            'tanggal_pengumpulan' => 'nullable|string|max:255',
        ]);

        $distribusi = DistribusiTahunan::findOrFail($id);

        $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;

        $distribusi->update($validatedData);
        return redirect()->route('tim-distribusi.tahunan.index')->with(['success' => 'Data berhasil diperbarui!', 'auto_hide' => true]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:distribusi_tahunan,id_distribusi' 
        ]);

        DistribusiTahunan::whereIn('id_distribusi', $request->ids)->delete();
        return back()->with(['success' => 'Data yang dipilih berhasil dihapus!', 'auto_hide' => true, 'hide_after' => 2]);
    }

    public function destroy($id)
    {
        $distribusi = DistribusiTahunan::findOrFail($id);
        $distribusi->delete();

        return redirect()->route('tim-distribusi.tahunan.index')->with(['success' => 'Data berhasil dihapus!', 'auto_hide' => true]);
    }

    public function searchPetugas(Request $request)
    {
        $request->validate([
            'field' => 'required|in:pencacah,pengawas',
            'query' => 'nullable|string|max:100',
        ]);

        $field = $request->input('field');
        $query = $request->input('query', '');

        $data = DistribusiTahunan::query()
            ->select($field)
            ->where($field, 'LIKE', "%{$query}%")
            ->distinct() 
            ->limit(5)  
            ->pluck($field);

        return response()->json($data);
    }
}