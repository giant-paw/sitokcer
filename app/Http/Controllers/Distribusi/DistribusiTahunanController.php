<?php

namespace App\Http\Controllers\Distribusi;

use App\Http\Controllers\Controller;
use App\Models\Distribusi\DistribusiTahunan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Master\MasterPetugas;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DistribusiTahunanExport;

class DistribusiTahunanController extends Controller
{
    public function index(Request $request)
    {
        $query = DistribusiTahunan::query();

        if ($request->filled('kegiatan')) {
            $query->where('nama_kegiatan', $request->kegiatan);
        }

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

        $query = $request->input('query', '');

        $data = MasterPetugas::query()
            ->where('nama_petugas', 'LIKE', "%{$query}%")
            ->limit(10)
            ->pluck('nama_petugas');

        return response()->json($data);
    }
    // PERBAIKAN 4: Method export yang benar
    public function export(Request $request)
    {
        $dataRange = $request->input('dataRange', 'all'); // default 'all'
        $dataFormat = $request->input('dataFormat');
        $exportFormat = $request->input('exportFormat');
        $kegiatan = $request->input('kegiatan');
        $search = $request->input('search');
        $currentPage = $request->input('page', 1); // Ambil halaman aktif
        $perPage = $request->input('per_page', 20);

        // Kirim semua parameter yang diperlukan
        $exportClass = new DistribusiTahunanExport(
            $dataRange,
            $dataFormat,
            $kegiatan,
            $search,
            $currentPage,
            $perPage
        );

        if ($exportFormat == 'excel') {
            return Excel::download($exportClass, 'DistribusiTahunan.xlsx');
        } elseif ($exportFormat == 'csv') {
            return Excel::download($exportClass, 'DistribusiTahunan.csv');
        } elseif ($exportFormat == 'word') {
            return $exportClass->exportToWord();
        }

        return back()->with('error', 'Format ekspor tidak didukung.');
    }
}
