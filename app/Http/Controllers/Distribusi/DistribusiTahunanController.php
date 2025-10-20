<?php

namespace App\Http\Controllers\Distribusi;

use App\Http\Controllers\Controller;
use App\Models\Distribusi\DistribusiTahunan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use App\Models\Master\MasterKegiatan;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Validator;

class DistribusiTahunanController extends Controller
{

    public function index(Request $request)
    {
        $selectedKegiatan = $request->input('kegiatan');
        $search = $request->input('search');

        $query = DistribusiTahunan::query();

        $query->when($selectedKegiatan, function ($q, $nama) {
             $q->where('nama_kegiatan', $nama);
        });

        $query->when($search, function ($q, $term) {
            $q->where(function($subq) use ($term){
                 $subq->where('BS_Responden', 'like', "%{$term}%")
                      ->orWhere('pencacah', 'like', "%{$term}%")
                      ->orWhere('pengawas', 'like', "%{$term}%")
                      ->orWhere('nama_kegiatan', 'like', "%{$term}%");
            });
        });

        $kegiatanCounts = DistribusiTahunan::query()
            ->select('nama_kegiatan', DB::raw('count(*) as total'))
            ->groupBy('nama_kegiatan')
            ->orderBy('nama_kegiatan')
            ->get();

        $masterKegiatanList = MasterKegiatan::orderBy('nama_kegiatan')->get();

        $perPage = $request->input('per_page', 20);
        if ($perPage == 'all') {
            $total = (clone $query)->count();
            $perPage = $total > 0 ? $total : 20;
        }

        $listData = $query->latest('id_distribusi')->paginate($perPage)->withQueryString();

        return view('timDistribusi.distribusitahunan', compact('listData', 'kegiatanCounts', 'masterKegiatanList', 'search'));
    }

    public function store(Request $request)
    {
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => 'required|string',
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar di master kegiatan. Silakan pilih dari rekomendasi.',
            'pencacah.exists' => 'Nama pencacah tidak terdaftar di master petugas. Silakan pilih dari rekomendasi.',
            'pengawas.exists' => 'Nama pengawas tidak terdaftar di master petugas. Silakan pilih dari rekomendasi.',
        ];

        // Validasi dengan rules dasar dan pesan kustom
        $validator = Validator::make($request->all(), $baseRules, $customMessages);
        
        $validatedData = $validator->validated();
        
        $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;

        DistribusiTahunan::create($validatedData);

        return back()->with('success', 'Data berhasil ditambahkan!');
    }

    public function edit(DistribusiTahunan $tahunan)
    {
        return response()->json($tahunan);
    }

    public function update(Request $request, DistribusiTahunan $tahunan)
    {
        $baseRules = [
            'nama_kegiatan' => [
                'required', 'string', 'max:255',
                'exists:master_kegiatan,nama_kegiatan'
            ],
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
            'flag_progress' => 'required|string',
            'tanggal_pengumpulan' => 'nullable|date',
        ];

        $customMessages = [
            'nama_kegiatan.exists' => 'Nama kegiatan tidak terdaftar di master kegiatan. Silakan pilih dari rekomendasi.',
            'pencacah.exists' => 'Nama pencacah tidak terdaftar di master petugas. Silakan pilih dari rekomendasi.',
            'pengawas.exists' => 'Nama pengawas tidak terdaftar di master petugas. Silakan pilih dari rekomendasi.',
        ];

        $validator = Validator::make($request->all(), $baseRules, $customMessages);

        if ($validator->fails()) {
            return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error_modal', 'editDataModal')
                    ->with('edit_id', $tahunan->id_distribusi);
        }

        $validatedData = $validator->validated();

        if($request->has('target_penyelesaian')) {
            $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
        }

        $tahunan->update($validatedData);
        return back()->with('success', 'Data berhasil diperbarui!');
    }

    public function destroy(DistribusiTahunan $tahunan)
    {
        $tahunan->delete();
        return back()->with('success', 'Data berhasil dihapus!');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:distribusi_tahunan,id_distribusi'
        ]);

        DistribusiTahunan::whereIn('id_distribusi', $request->ids)->delete();
        return back()->with('success', 'Data yang dipilih berhasil dihapus!');
    }

    public function searchPetugas(Request $request)
    {
        $request->validate([
            'field' => 'required|in:pencacah,pengawas',
            'query' => 'nullable|string|max:100',
        ]);

        $query = $request->input('query', '');

        $data = MasterPetugas::query()
            ->where('nama_petugas', 'LIKE', "%{$query}%")
            ->limit(10) 
            ->pluck('nama_petugas');

        return response()->json($data);
    }
}