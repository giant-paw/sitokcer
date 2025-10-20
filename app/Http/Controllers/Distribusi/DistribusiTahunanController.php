<?php

namespace App\Http\Controllers\Distribusi;

use App\Http\Controllers\Controller;
use App\Models\Distribusi\DistribusiTahunan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
<<<<<<< HEAD
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DistribusiTahunanExport;
=======
use App\Models\Master\MasterKegiatan;
use Illuminate\Validation\Rule; 
use Illuminate\Support\Facades\Validator;
>>>>>>> jay

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

<<<<<<< HEAD
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
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
=======
        $query->when($search, function ($q, $term) {
            $q->where(function($subq) use ($term){
                 $subq->where('BS_Responden', 'like', "%{$term}%")
                      ->orWhere('pencacah', 'like', "%{$term}%")
                      ->orWhere('pengawas', 'like', "%{$term}%")
                      ->orWhere('nama_kegiatan', 'like', "%{$term}%");
            });
        });
>>>>>>> jay

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
<<<<<<< HEAD
        $validatedData = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255',
            'pengawas' => 'required|string|max:255',
=======
        $baseRules = [
            'nama_kegiatan' => 'required|string|max:255|exists:master_kegiatan,nama_kegiatan',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
>>>>>>> jay
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
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Data yang diberikan tidak valid.',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error_modal', 'tambahDataModal');
        }

        $validatedData = $validator->validated();
        $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
        DistribusiTahunan::create($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil ditambahkan!']);
        }

        return back()->with('success', 'Data berhasil ditambahkan!');
    }

    public function edit(DistribusiTahunan $tahunan)
    {
<<<<<<< HEAD
        $distribusi = DistribusiTahunan::findOrFail($id);

        return response()->json($distribusi);
=======
        return response()->json($tahunan);
>>>>>>> jay
    }

    public function update(Request $request, DistribusiTahunan $tahunan)
    {
<<<<<<< HEAD
        $validatedData = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255',
            'pengawas' => 'required|string|max:255',
            'target_penyelesaian' => 'required|string|max:255',
=======
        $baseRules = [
            'nama_kegiatan' => [
                'required', 'string', 'max:255',
                'exists:master_kegiatan,nama_kegiatan'
            ],
            'BS_Responden' => 'required|string|max:255',
            'pencacah' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'pengawas' => 'required|string|max:255|exists:master_petugas,nama_petugas',
            'target_penyelesaian' => 'required|date',
>>>>>>> jay
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
                    ->with('edit_id', $tahunan->id_distribusi); 
        }

        $validatedData = $validator->validated();

        if($request->has('target_penyelesaian')) {
            $validatedData['tahun_kegiatan'] = Carbon::parse($request->target_penyelesaian)->year;
        }

        $tahunan->update($validatedData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => 'Data berhasil diperbarui!']);
        }
        
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
<<<<<<< HEAD
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

        $query = $request->input('query', '');

        $data = MasterPetugas::query()
            ->where('nama_petugas', 'LIKE', "%{$query}%")
            ->limit(10)
            ->pluck('nama_petugas');

        return response()->json($data);
=======
        return back()->with('success', 'Data yang dipilih berhasil dihapus!');
>>>>>>> jay
    }
    public function export(Request $request)
    {
        $dataRange = $request->input('dataRange');
        $dataFormat = $request->input('dataFormat');
        $exportFormat = $request->input('exportFormat');

        $exportClass = new DistribusiTahunanExport($dataRange, $dataFormat);

        if ($exportFormat == 'excel') {
            return Excel::download($exportClass, 'DistribusiTahunan.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, 'DistribusiTahunan.csv');
        }

        return back()->with('error', 'Format ekspor tidak didukung.');
    }
}
